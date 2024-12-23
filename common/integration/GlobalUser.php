<?php


namespace common\integration;

use App\Http\Controllers\Traits\ApiResponseTrait;
use App\Http\Controllers\Traits\ExportExcelTrait;
use App\Http\Controllers\Traits\NotificationTrait;
use App\Http\Controllers\Traits\OTPTrait;
use App\Models\ChangePasswordHistory;
use App\Models\Country;
use App\Models\AdminMakerChecker;
use App\Models\UserLoginAlertSetting;
use App\Models\UserStatistic;
use common\integration\Brand\Configuration\Backend\BackendAdmin;
use App\Models\CurrenciesSettings;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\NotificationAutomation;
use App\Models\PaymentReceiveOption;
use App\Models\PaymentRecOption;
use App\Models\Profile;
use App\Models\Usergroup;
use App\Models\UserSetting;
use App\Models\UserUsergroup;
use App\Models\Wallet;
use App\User;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Backend\BackendMix;
use common\integration\Brand\Configuration\Backend\BackendWallet;
use common\integration\Brand\Configuration\Frontend\FrontendMix;
use common\integration\Brand\Configuration\Frontend\FrontendWallet;
use common\integration\BrandConfiguration;
use common\integration\Cashback\CashbackService;
use common\integration\Models\CashbackChannel;
use common\integration\Models\CashbackEntity;
use common\integration\Models\MerchantExtras;
use common\integration\Models\OutGoingEmail;
use common\integration\Models\StaticContent;
use common\integration\Models\UserAgreement;
use common\integration\Models\UserDevice;
use common\integration\Otp\OtpLimitRate;
use common\integration\Utility\Arr;
use common\integration\Utility\Helper;
use Illuminate\Support\Facades\Auth;
use common\integration\Utility\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use DB;
use App\Http\Controllers\Traits\SendEmailTrait;
use App\Http\Controllers\Traits\CommonLogTrait;
use App\Models\MerchantReportHistory;
use App\Models\RevokeTokens;
use App\Utils\Date;
use Carbon\Carbon;
use App\Models\Statistics;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use common\integration\Utility\Cookies;
use Illuminate\Support\Str;
use PHPUnit\Util\Exception;

class GlobalUser
{
   use SendEmailTrait, CommonLogTrait, ExportExcelTrait, OTPTrait, ApiResponseTrait,NotificationTrait;

   public $exception_msg = '';
   
   const RESET_SECRET_QUESTION_DURATION = 3600; //value is in seconds . it's for not sending email multiple times within one hour and not excessing a old link. 
   const INFORMATION_CHNAGE_LIMIT = 3;
	const INFORMATION_CHANGE_FIELDS = [
		'address',
		'country',
		'city',
		'email',
		'question_one',
		'answer_one',
	];

    const USER_AVATAR_EXT = ['png', 'jpg', 'jpeg', 'webp', 'tif', 'tiff'];
    const USER_AVATAR_SIZE = 2; //in MB

    const MAX_WRONG_SECRET_LIMIT = 5;

    const USER_SELF_DEACTIVATE = 1;
    const USER_SELF_ACTIVATE = 0;
    
    const ACCOUNT_ACTIVATION_NOT_SEND = 0;
    const ACCOUNT_ACTIVATION_SEND = 1;
    const USER_MAX_DEACTIVATE_DAYS = 30;
    
    const USER_STATUS_CHANGE = "user_status_change";
    const USER_ACTIVATION_REQUEST = "user_activation_request_to_admin";

    const MAX_TIME_OF_COOKIE = 24 * 60; // Houres * minutes

    public const FORGET_PASSWORD_BLOCKING_TIME_IN_SEC = 600;
    public const FORGET_PASSWORD_MAX_ALLOWED_ATTEMPT = 3;

    const ACTION_CHANGEMAIL = "CHANGEMAIL";
    const ACTION_EMAIL_VERIFICATION = "EMAIL_VERIFICATION";

    const OTP_CHANNEL_ALL = 0;
    const OTP_CHANNEL_SMS = 1;
    const OTP_CHANNEL_EMAIL = 2;

   public function userFirstNameLastName($full_name)
   {
      $firstname = $lastname = '';
      $full_name = trim($full_name);
      if (!empty($full_name)) {
         $length = str_word_count($full_name);
         if ($length > 1) {
            $names = explode(' ', $full_name);
            $lastname = $names[count($names) - 1];
            unset($names[count($names) - 1]);
            $firstname = join(' ', $names);
         } else {
            $firstname = $full_name;
            $lastname = $full_name;
         }
      }


      return [$firstname, $lastname];
   }


   public function migrateUserFirstNameLastNameFromName($id = null)
   {

      $profile = new Profile();

      if (!empty($id)) {

         $userObj = $profile->getUserById($id);

         if (!empty($userObj) && isset($userObj->name)) {

            list($first_name, $last_name) = $this->userFirstNameLastName($userObj->name);

            $userObj->first_name = $first_name;
            $userObj->last_name = $last_name;
            $userObj->save();
         }

      } else {

         $allUser = $profile->getAllUser();

         if (!empty($allUser) && count($allUser) > 0) {

            try {
               DB::beginTransaction();

               foreach ($allUser->chunk(100) as $chunk) {

                  foreach ($chunk as $userObj) {

                     list($first_name, $last_name) = $this->userFirstNameLastName($userObj->name);

                     $userObj->first_name = $first_name;
                     $userObj->last_name = $last_name;
                     $userObj->save();

                  }

               }

               DB::commit();
               $logData["action"] = "User first name and last name update process success";

            } catch (\Throwable $e) {
               DB::rollBack();
               $logData["action"] = "User first name and last name update process failed";
               $logData["rollback_status"] = true;
               $logData["message"] = $e->getMessage();
            }

            Log::info($logData);
         }
      }

      return true;

   }

   public static function checkSecurityImage($userObj)
   {
      return BrandConfiguration::showSecurityImage() && !empty($userObj->security_image_id);
   }
   
   public static function activitySessionKey()
   {
      return 'last_activity';
   }

   public function sentChangePasswordEmail($userObj = null, $log_info = 'ADMIN CHANGE PASSWORD'){

      if(empty($data) && !BrandConfiguration::allowChangePasswordMail() && !empty($userObj)){
         return false;
      }



      $subject = 'change_password';
      $to = $userObj->email;
      $from = config('app.SYSTEM_NO_REPLY_ADDRESS');
      $template = 'user_forget_password.success';
      $language = $this->getLang($userObj);
      $priority = 1;

      $mail_data = [
         'log_info' => $log_info,
         'lan' =>  $language,
         'from' =>  $from,
         'to' =>  $to ,
         'template' => $template,
         'subject' => $subject,
         'priority' => $priority,
      ];
      
      $data = [
         'changed_date' => Date::format(9,Carbon::now()),
         'ip' => $this->getClientIp(),
      ];

       //out_going_email
      $this->setGNPriority(OutGoingEmail::PRIORITY_EXPRESS);
      $this->sendEmail($data,$subject, $from, $to,'', $template, $language,'', $priority);

      $log_data = [
         'LOGNAME' => 'CHANGE_PASSWORD_EMAIL',
         'log_info' => $log_info,
         'to' => $to,
         'mail_data' => $mail_data,
      ];

      $this->createLog($log_data);

   }

   public function getLang($user){
      $default = 'tr';

      if(!empty($user) && !empty($user->language)){
         $default = $user->language;
      }

      return $default;
   }

   public static function getStatusWalletByMobileNo($mobile_no){
      $userObj = (new Profile)->getUserByPhone($mobile_no, Profile::CUSTOMER);
      if(!empty($userObj)){
         return $userObj->wallets ? true : false;
      }
      return false;
   }

   public static function getUserTypeList($allow_user_list = []){

      $list  = Profile::USER_TYPES;

      if(empty($allow_user_list)){
         return $list;
      }
      
      $user_type = [];
      foreach($list as $key => $value){
         if(in_array($key, $allow_user_list)){
            $user_type[$key] = $value;
         }
      }

      return $user_type;

   }
    public static function getMerchantReportAuditorRtList(){
        return MerchantReportHistory::AUDITOR_RT_LIST;
    }

   public static function getBlockedUserViewPageContent($data, $localization_sentence, $language){

      $sentence = @$data['name'] ?? @$data['first_name']. ' '. @$data['last_name'];
      $sentence .= ', ('.GlobalFunction::getContentForLocalization(@$data['panel_name'], $language).') ';
      $sentence .= GlobalFunction::getContentForLocalization($localization_sentence, $language);
      return $sentence;

      // return @$data['name'] ?? @$data['first_name']. ' '. @$data['last_name']. ', ('.GlobalFunction::getContentForLocalization(@$data['panel_name'], $language).') '.  GlobalFunction::getContentForLocalization($localization_sentence, $language);
   }

   public static function blockUserSentMail($userObj, $panel_name = null){

      $manage_log = new ManageLogging;

      try{
         $to = (new Statistics)->findById(1,'block_user_email')->block_user_email;

         if(empty($to)){
   
            $manage_log->createLog([
               BrandConfiguration::BLOCK_USER_LOG_KEY => 'There is no email set for block user',
            ]);
   
            return false;
         }
   
         $to_mails = explode(",", str_replace(" ", "", $to));
         $from = config('app.SYSTEM_NO_REPLY_ADDRESS');
   
   
         $data = [
            'name' => $userObj->name,
            'email' => $userObj->email,
            'first_name' => $userObj->first_name,
            'last_name' => $userObj->last_name,
            'user_language' => $userObj->language,
            'panel_name' => $panel_name,
         ];
   
         $language = $data['user_language'] ?? app()->getLocale();
         $subject = 'block_user';
         $attachment = ''; 
         $template = 'user_blocked.user_blocked';
         $priority = 1;

          //out_going_email
         $useTraitClass = (new class { use SendEmailTrait; });
         $useTraitClass->setGNPriority(OutGoingEmail::PRIORITY_HIGH);
         $useTraitClass->sendEmail($data, $subject, $from, $to_mails, $attachment, $template, $language, '', $priority);

         $manage_log->createLog([
            BrandConfiguration::BLOCK_USER_LOG_KEY => 'Block user email sent successfully',
            'SENT_TO' => $to_mails,
         ]);

      }catch(\Throwable $e){
         $manage_log->createLog([
            BrandConfiguration::BLOCK_USER_LOG_KEY => 'Block user email sent successfully',
            'MESSAGE' => $e->getMessage(),
            'FILE' => $e->getFile(),
            'LINE' => $e->getLine(),
            'TRACE' => $e->getTrace(),
         ]);
      }
   }
   
   public static function merchantStatusUpdateSentMail($userObj,$merchant_id,$merchant_name,$merchant_status, $authorized_person_name = ''){

      $manage_log = new ManageLogging;

      try{
         $to = (new Statistics)->findById(1,'merchant_status_update_email')->merchant_status_update_email;

         if(empty($to)){
   
            $manage_log->createLog([
               'MERCHANT_STATUS_UPDATE_EMAIL' => 'There is no email set for merchant status update!',
            ]);
   
            return false;
         }
   
         $to_mails = explode(",", str_replace(" ", "", $to));
         $from = config('app.SYSTEM_NO_REPLY_ADDRESS');
   
         $data = [
            'merchant_name' => $merchant_name,
            'name' => $authorized_person_name,
            'merchant_id' => $merchant_id,
            'merchant_status' => ($merchant_status == 0) ? 'Inactive' : 'Active',
            'user_language' => $userObj->language,
         ];
   
         $language = $data['user_language'] ?? app()->getLocale();
         $subject = 'merchant_status_update';
         $attachment = ''; 
         $template = 'merchant_status_update.merchant_status_update';
         $priority = 1;

          //out_going_email
         $useTraitClass = (new class { use SendEmailTrait; });
         $useTraitClass->setGNPriority(OutGoingEmail::PRIORITY_HIGH);
         $useTraitClass->sendEmail($data, $subject, $from, $to_mails, $attachment, $template, $language, '', $priority);

         $manage_log->createLog([
            'MERCHANT_STATUS_UPDATE_EMAIL' => 'Merchant status update email sent successfully',
            'SENT_TO' => $to_mails,
         ]);

      }catch(\Throwable $e){
         $manage_log->createLog([
            'MERCHANT_STATUS_UPDATE_EMAIL' => 'Merchant status update email sent successfully',
            'MESSAGE' => $e->getMessage(),
            'FILE' => $e->getFile(),
            'LINE' => $e->getLine(),
            'TRACE' => $e->getTrace(),
         ]);
      }
   }


    public static function guIsPasswordContainsInfo($password, $user = null)
    {
        $status = false;
        if (!empty($user) && in_array(config('brand.name_code'), Profile::PASS_MATCH_WITH_INFO)) {
            mb_internal_encoding("UTF-8");
	        $match_array = [
		        \common\integration\Utility\Str::mbConvertCase(\common\integration\Utility\Str::replace(" ", "", $user->name), MB_CASE_LOWER, "UTF-8"),
		        \common\integration\Utility\Str::mbConvertCase(\common\integration\Utility\Str::replace(" ", "", $user->first_name), MB_CASE_LOWER, "UTF-8"),
		        \common\integration\Utility\Str::mbConvertCase(\common\integration\Utility\Str::replace(" ", "", $user->last_name), MB_CASE_LOWER, "UTF-8"),
		        \common\integration\Utility\Str::mbConvertCase(\common\integration\Utility\Str::replace(" ", "", $user->email), MB_CASE_LOWER, "UTF-8"),
		        \common\integration\Utility\Str::mbConvertCase(\common\integration\Utility\Str::replace([" ", "+"], "", $user->phone), MB_CASE_LOWER, "UTF-8"),
		        \common\integration\Utility\Str::mbConvertCase(\common\integration\Utility\Str::replace([".", "/", "-"], "", ManipulateDate::getCarbonDayMonthYear($user->dob, 'Ymd')), MB_CASE_LOWER, "UTF-8"),
		        \common\integration\Utility\Str::mbConvertCase(\common\integration\Utility\Str::replace([".", "/", "-"], "", ManipulateDate::getCarbonDayMonthYear($user->dob, 'dmY')), MB_CASE_LOWER, "UTF-8"),
		        \common\integration\Utility\Str::mbConvertCase(\common\integration\Utility\Str::replace([".", "/", "-"], "", ManipulateDate::getCarbonDayMonthYear($user->dob, 'mdY')), MB_CASE_LOWER, "UTF-8"),
	        ];
			
	        // $replaceable_character = [" ", "+", ".", "/", "-", "*", "{", "}", "?", "^", "$", "[", "]", "(", ")", "|"];
	        /*
	         * REMOVE SPECIAL CHARACTER PATTERN
	         */
	        $replaceable_character = '/[^\pL\pN\s]/u';
	        $lower_password = \common\integration\Utility\Str::removeMultipleSpacesWithinString(
			        \common\integration\Utility\Str::mbConvertCase(
					\common\integration\Utility\Str::preg_replace($password, $replaceable_character,''),
			        MB_CASE_LOWER,
			        "UTF-8"
		        ), ''
	        );
			
			
            foreach ($match_array as $key=>$value) {
	            if ($value && \common\integration\Utility\Str::preg_match("/{$lower_password}/i", $value)) {
                    $status = true;
                    break;
                }
            }

            if ($status) {
                return [$status, __("Password should not match with personal information")];
            }
        }

        return [$status, ''];
    }

   public static function guPasswordRestrictionRules ($password, $auth = null) {
       $is_restricted = false;
       $message = '';
       $globalUser = new self();

       if (config('constants.PASSWORD_SECURITY_TYPE') == Profile::SIX_DIGITS_PASSWORD) {
           if (!$is_restricted) {
               list($is_restricted, $message) = $globalUser->checkPasswordBeginning($password);
           }

           if (!$is_restricted) {
               list($is_restricted, $message) = $globalUser->checkAllSimilarValue($password);
           }

           if (!$is_restricted) {
               list($is_restricted, $message) = $globalUser->checkDuplicateTriplicate($password);
           }

           if (!$is_restricted) {
               list($is_restricted, $message) = $globalUser->checkAnySideSuccessive($password);
           }
       } else {
           list($is_restricted, $message) = self::guIsPasswordContainsInfo($password, $auth);
       }

       return [$is_restricted, $message];
   }

    private function checkPasswordBeginning ($password)
    {
        $case_one = substr($password, 0, 1);
        $case_two = substr($password, 0, 2);

        if ($case_one == '0' || $case_two == '19' || $case_two == '20') {
            return [true, __('The password cannot start with 19, 20 and 0')];
        }

        return [false, ''];
    }

    private function checkDuplicateTriplicate ($password)
    {
        $case_one = str_split($password, 2);
        $case_two = str_split($password, 3);

        if (($case_one[0] == $case_one[1]) && ($case_one[0] == $case_one[2])) {
            return [true, __('The password cannot include duplications and/or triplications')];
        } elseif ($case_two[0] == $case_two[1]) {
            return [true, __('The password cannot include duplications and/or triplications')];
        }

        return [false, ''];
    }

    private function checkAnySideSuccessive ($password)
    {
        $lr_cnt = $rl_cnt = 1;
        $ltor = $rtol = 0;
        for ($i = 1; $i < strlen($password); $i ++)
        {
            if ($password[$i] == ($password[$i-1]+1)) {
                $lr_cnt ++;
                $ltor = $lr_cnt > $ltor ? $lr_cnt : $ltor;
            } else {
                $lr_cnt = 1;
            }

            if (($password[$i]+1) == $password[$i-1]) {
                $rl_cnt ++;
                $rtol = $rl_cnt > $rtol ? $rl_cnt : $rtol;
            } else {
                $rl_cnt = 1;
            }
        }
        if(\config('brand.name_code') == \config('constants.BRAND_NAME_CODE_LIST.PB')){
            if ($ltor > 3 || $rtol > 3 ) {
                return [true, __('The password cannot include more than four successive numbers')];
            }
        }else{
            if ($ltor > 2 || $rtol > 2) {
                return [true, __('The password cannot include more than two successive numbers')];
            }
        }

        return [false, ''];
    }

    private function checkAllSimilarValue ($password)
    {
        $sv_cnt = 1;
        for ($i = 1; $i < strlen($password); $i ++) {
            if (($password[$i]) == $password[$i-1]) {
                $sv_cnt ++;
            } else {
                break;
            }
        }

        if ($sv_cnt == 6) {
            return [true, __('Password cannot include all similar numbers')];
        }

        return [false, ''];
    }
    public static function getUserGender()
    {
        return [
            User::MALE => "Male",
            User::FEMALE => "Female"
        ];
    }

    public static function getUserByIds($ids){
        return User::query()->whereIn('id',$ids)->get();
    }

    public static function phoneNumberValidationRule(){

        return 'required|min:13|distinct|regex:/^[+]\d+$/';

    }

    public static function getPaymentMethod($sale_transaction){
        $payment_method = '';
        if($sale_transaction->payment_source == 13 || $sale_transaction->payment_source == 14){
            $payment_method = __(config('constants.SAVEDCARD'));
        }else if(SaleTransaction::isSaleFromFastpayWalletAmount($sale_transaction->payment_source)){
            $payment_method = __(config('constants.FASTPAY_WALLET'));
        }
        else{
            $payment_method = __((new PaymentRecOption())->getPaymentOptionName($sale_transaction->payment_type_id));
//            $payment_method = PaymentRecOption::PAYMENT_OPTION[$sale_transaction->payment_type_id] ?? '-';

            if(BrandConfiguration::getDebitCardwiseDPLAndMP() && !empty($sale_transaction->merchant_sale_card_type)){
                $payment_method = $sale_transaction->merchant_sale_card_type ==
                \App\Models\PaymentRecOption::CREDITCARD ? __('Credit Card') : __('Debit Card');;
            }
            
        }

        return $payment_method;
    }

    public static function resetSecretQuestionCacheKey($user)
    {
        return 'reset_secret_question_' . $user->id . '_' . $user->user_type . '_' . $user->email . '_' . $user->phone;
    }


   public function getUserListInactiveNDays($user_type, $days, $file_type = 'pdf', $is_attachment = false)
   {
      $view_blade = '';
      if ($file_type == 'pdf') {
         $view_blade = 'email/inactivity_notification/inactivity_pdf';
      }

      $profile = new Profile();
      $inactivity_user_list = $profile->getUserListInactiveNDays($user_type, $days);

      if (empty($inactivity_user_list) || count($inactivity_user_list) == 0) {
         return false;
      }

      $filename = 'inactivity_user_list';
      $heading = [
        __('Name'),
        __('Surname'),
        __('User GSM'),
        __('Last activity datetime')
      ];

      $data = [];
      foreach ($inactivity_user_list as $val) {
         $data [] = [
           $val->first_name,
           $val->last_name,
           $val->phone,
           $val->last_activity_datetime,
         ];
      }

      if ($is_attachment) {
         $file_path = $this->fileExport($file_type, collect($data), $filename, $heading, $view_blade, true);

      } else {
         $file_path = $this->fileExport($file_type, collect($data), $filename, $heading, $view_blade);
      }


      return [true, $file_path];

   }
	public static function userInformationCounterCheker($classObject, $data){
      
		$status = false;

		if((!$classObject instanceof Profile && !$classObject instanceof User) ||  empty($data) || !auth()->check()){
			return $status;
		}

     	$manage_log = new ManageLogging;
		$inputs = $data;

		$auth_user_id = auth()->user()->id;
		$input_checker = Self::INFORMATION_CHANGE_FIELDS;

		try{
			if(sizeof($inputs) > 0){
				foreach($inputs as $key => $input){
		
					if(isset($classObject->{$key}) && !empty($input) && in_array($key, $input_checker)){
								
						if($key == 'answer_one'){

							$commonLogTrait = new class { use CommonLogTrait; };
							$classObject->{$key} = $commonLogTrait->customEncryptionDecryption($classObject->{$key}, config('app.brand_secret_key'),'decrypt');

							$input = $commonLogTrait->customEncryptionDecryption($input, config('app.brand_secret_key'),'decrypt');
						}

						if($classObject->{$key} != $input){
							$status = true;
							break;
						}

					}

				}
			}

			if(!empty($status)){

				$profileObj = (new UserProfile)->getUserProfilesById($auth_user_id);

				if(!empty($profileObj)){
                  
               if(($profileObj->info_change_count +1) % Self::INFORMATION_CHNAGE_LIMIT == 0){
                  // THE EMAIL SHOULD SENT IT FROM HERE
                  $to = (new Statistics)->findById(1, 'user_change_information_email')->user_change_information_email;

                  $to_mails = explode(",", str_replace(" ", "", $to));
                  $from = config('app.SYSTEM_NO_REPLY_ADDRESS');

                  $data = [
                     'name' => $classObject->name,
                     'email' => $classObject->email,
                     'first_name' => $classObject->first_name,
                     'last_name' => $classObject->last_name,
                     'user_language' => $classObject->language,
                  ];
                  
                  // here need to change 
                  $language = $data['user_language'] ?? app()->getLocale();
                  $subject = 'info_change_email';
                  $attachment = ''; 
                  $template = 'info_change.info_change';
                  $priority = 1;

                   //out_going_email
                  $useTraitClass = (new class { use SendEmailTrait; });
                  $useTraitClass->setGNPriority(OutGoingEmail::PRIORITY_MEDIUM);

                  if (!empty($inputs['deposit_source']) && $inputs['deposit_source'] == Deposit::SOURCE_FINFLOW) {
                      $notificationAutomationData['email_data'] = $data;
                      $notificationAutomationData['email_template'] = $template;
                      $notificationAutomationData['subject'] = $subject;
                      $notificationAutomationData['language'] = $language;
                      $notificationAutomationData['receiver_email'] = $to;
                      (new NotificationAutomation())->insertEntry($notificationAutomationData, true);
                  } else {
                      $useTraitClass->sendEmail($data, $subject, $from, $to_mails, $attachment, $template, $language, '', $priority);
                  }

                  $manage_log->createLog([
                     'action' => 'INFORMATION_CHANGE_LIMIT_REACHED_SENT_EMAIL',
                     'SENT_TO' => $to_mails,
                  ]);

               }
               $profileObj->info_change_count = $profileObj->info_change_count + 1;
               $profileObj->save();

				}

			}
	
		}catch(\Throwable $e){

			$manage_log->createLog([
				'action' => 'Information changing email sent error',
				'MESSAGE' => $e->getMessage(),
				'FILE' => $e->getFile(),
				'LINE' => $e->getLine(),
				'TRACE' => $e->getTrace(),
			]);

			//return $status;
		}

		// need to check the current checker and the previous checker update it and sent mail to the user
		// dd($status);

	}
   // check is admin verified or not
   public static function isAdminVerifiedChecker($userObj){


      if(($userObj instanceof Profile OR $userObj instanceof User)){

          return Self::lockAndAdminVerifiedNotApprovedChecking($userObj) || Self::pendingUserChecking($userObj);

      }

   }

   public static function lockAndAdminVerifiedNotApprovedChecking($userObj){

        $status = false;

        if(BrandConfiguration::allowLoginBlockTime() || BrandConfiguration::restrictLoginToInactiveUser()){

            if(
                ($userObj->is_admin_verified == Profile::LOCK_USER && $userObj->user_type == Profile::MERCHANT)
                ||
                (($userObj->is_admin_verified == Profile::LOCK_USER || $userObj->is_admin_verified == Profile::ADMIN_VERIFIED_NOT_APPROVED) && $userObj->user_type == Profile::ADMIN)
            ){

                $status = true;

            }
        }

        return $status;
   }

   public static function pendingUserChecking($userObj){

       $status = false;

       if(BrandConfiguration::disablePendingStatusUsersLogin()){

           if($userObj->is_admin_verified == Profile::ADMIN_VERIFIED_PENDING){

               $status = true;

           }

       }

       return $status;

   }

   public static function allowRedirectionBeforeCheckPasswordChange(Request $request){

      return (

            $request->route()->getAction('as') == 'security.index' 
            || $request->route()->getAction('as') == 'local.lang' 
            || $request->route()->getAction('as') == 'security.update'
            || $request->route()->getAction('as') == 'security.verifyotp' 
            || $request->route()->getAction('as') == 'security.resendotp' 
            || $request->route()->getAction('as') == 'logout' 
            || $request->route()->getAction('as') == 'security.secretqa'
            || $request->route()->getAction('as') == 'security.sendEmailVerificationOTP'
            || $request->route()->getAction('as') == 'security.verifyEmailOTP'
            || $request->route()->getAction('as') == 'security.verifyEmailResendOTP'
            || ( BrandConfiguration::walletEmailVerified() && 
                  ( $request->route()->getAction('as') == 'kyc' || $request->route()->getAction('as') == 'kyc.update')
            )
            
         );


   }


   public function sentEmailNotificationOnChangeEmailAndPhone($userObj, $type)
   {

      if(!empty($userObj)){

         if($type == 'change_email'){
            $subject = 'change_email';
            $template = 'change_email.change';
            $log_info = 'MERCHANT_CHANGE_EMAIL_FROM_PANEL';
         }else{
            $subject = 'change_phone';
            $template = 'change_phone.success';
            $log_info = 'MERCHANT_CHANGE_PHONE_FROM_PANEL';
         }

         $to = $userObj->email;
         $from = config('app.SYSTEM_NO_REPLY_ADDRESS');

         $language = $this->getLang($userObj);
         $priority = 1;

         $mail_data = [
           'log_info' => $log_info,
           'lan' => $language,
           'from' => $from,
           'to' => $to,
           'template' => $template,
           'subject' => $subject,
           'priority' => $priority,
         ];

         $data = [
           'changed_date' => Date::format(9, Carbon::now())
         ];

            //out_going_email
         $this->setGNPriority(OutGoingEmail::PRIORITY_HIGH);
         $this->sendEmail($data, $subject, $from, $to, '', $template, $language, '', $priority);

         $log_data = [
           'LOGNAME' => 'CHANGE_PASSWORD_EMAIL',
           'log_info' => $log_info,
           'to' => $to,
           'mail_data' => $mail_data,
         ];
      }else{
         $log_data = [
           'Action' => 'CHANGE_EMAIL_AND_PHONE',
           'log_info' => 'Email sending failed',
           'to' => 'No receiver found',
         ];
      }


      $this->createLog($log_data);

   }

   public function sendOTPforUserPhoneChange($userObj, $input)
   {
      try {

         GlobalFunction::setBrandSession('userPhoneStore', $input, $userObj->id);

         $OTP = $userObj->cacheTheOTP();
         $this->sendSMSToUser($userObj, $OTP);
         $this->sendEmailTOUser($userObj, $OTP);

         return true;
      } catch (\Throwable $exception) {
         return false;
      }
   }

   public function sendSMSToUser($userObj, $OTP)
   {

      $input = $this->getInputData($userObj);

      $logData['action'] = "CHANGE_PHONE_USER_OTP_REQUEST";

      ///Send SMS OTP
      if (!empty($input)) {

         $language = $this->getLang($userObj);
         $header = "";
         $phone = $input['phone'] ?? '';
         $template = $input['sms_template'];
         $message = view($template . $language, compact('OTP'))->render();
         $this->setGNPriority(OutGoingEmail::PRIORITY_EXPRESS);
         $status = $this->sendSMS($header, $message, $phone, 1);

         if ($status) {
            $logData['OTP_STATUS'] = "OTP_SENT";
         } else {
            $logData['OTP_STATUS'] = "OTP_SENT_FAILED";
         }
      } else {
         $logData['OTP_STATUS'] = "OTP_SENT_FAILED";
      }


      $this->createLog($this->_getCommonLogData($logData));
   }

   public function sendEmailTOUser($userObj, $OTP)
   {
      $input = $this->getInputData($userObj);
      /// Send E-mail
      if (!empty($input)) {

         $data['OTP'] = $OTP;
         $language = $this->getLang($userObj);
         $from = config('app.SYSTEM_NO_REPLY_ADDRESS');
         $to = $userObj->email;
         $template = $input['email_template'];

          //out_going_email
         $this->setGNPriority(OutGoingEmail::PRIORITY_EXPRESS);
         $this->sendEmail($data, "LOGIN_OTP", $from, $to, "", $template, $language, 1);

      } else {
         $logData['action'] = "UPDATED_USER_OTP_REQUEST";
         $logData['OTP_STATUS'] = "OTP_SENT_FAILED";
         $this->createLog($this->_getCommonLogData($logData));
      }

   }

   public function getInputData($userObj)
   {
      $data = '';
      if (GlobalFunction::hasBrandSession('userPhoneStore', $userObj->id)) {
         $data = GlobalFunction::getBrandSession('userPhoneStore', $userObj->id);
      }

      return $data;
   }

    public function revokeUserSession($request){
        
        if(isset($request->user_id) && !empty($request->user_id)){
            $revoke_session = (new RevokeTokens())->logoutPastLogin($request->user_id);
            $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
            $data['user_id'] = $request->user_id;
        }else{
            $status_code = ApiService::API_SERVICE_USER_NOT_FOUND;
            $data['user_id'] = '';
        }
        return $this->sendApiResponse(ApiService::API_SERVICE_STATUS_MESSAGE[$status_code], $data, $status_code);
    }

    public static function getRegexValidationRules(){
       return [
            'rules'=>[
                'min:8',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match("/[a-z]/", $value)) {
                        $fail(__("Password must contain").' '.__("At least 1 lower case letter (a..z)"));
                    }
                },
                function ($attribute, $value, $fail) {
                    if (!preg_match("/[A-Z]/", $value)) {
                        $fail(__("Password must contain").' '.__("At least 1 upper case letter (A..Z)"));
                    }
                },
                function ($attribute, $value, $fail) {
                    if (!preg_match("/[0-9]/", $value)) {
                        $fail(__("Password must contain").' '.__("At least 1 number (0..9)"));
                    }
                },
                function ($attribute, $value, $fail) {
                    if (!preg_match("/[@$!%*#?&]/", $value)) {
                        $fail(__("Password must contain").' '.__("At least 1 special character"));
                    }
                },
                function ($attribute, $value, $fail) {
                    if (preg_match("/^(20|19|0)/", $value)) {
                        $fail(__("The password cannot start with 19, 20 and 0"));
                    }
                },
                function ($attribute, $value, $fail) {
                    if (preg_match("/(.)\\1+/", $value)) {
                        $fail(__("The password cannot include duplications and/or triplications"));
                    }
                },
                function ($attribute, $value, $fail) {
                    if (preg_match("/(123(?:4(?:5(?:6(?:7(?:89?)?)?)?)?)?|234(?:5(?:6(?:7(?:89?)?)?)?)?|345(?:6(?:7(?:89?)?)?)?|456(?:7(?:89?)?)?|567(?:89?)?|6789?|789)/", $value)) {
                        $fail(__("The password cannot include more than two successive numbers"));
                    }
                }
            ],
           'messages'=>[
               'regex'=>__("password must contain one uppercase, one lowercase, one symbol and one number"),
               'min'=>__("Password must be 8 digits or long")
           ]
       ];
    }

	public static function isSendEmailFromNewUser($userObj){
		return ($userObj->user_type != Profile::INTEGRATOR && ($userObj->user_type != Profile::SALES_ADMIN && $userObj->user_type != Profile::SALES_EXPERT));
	}

    public static function userDevice($id){
        return UserDevice::query()
            ->where('user_id',$id)
            ->latest()
            ->first();
    }

    public static function userDeviceTokens(Array $userId,$tokenColumn='push_notification_key'){
       return UserDevice::query()
            ->whereIn('user_id',$userId)
            ->latest()
            ->pluck($tokenColumn)
            ->toArray();
    }

    public function incrementWrongSecretAnswerByUserId($user_id){
        return (new User())->where('id', $user_id)
            ->increment('wrong_secret_answer_attempt');
    }

    public function processSecretQuestionAnswer(Request $request, $user_type)
    {
        $data = [];
        $request->merge(['user_type' => $user_type]);

        $userObj  = new User();

        $user = null;
        if ($user_type == User::MERCHANT) {
            $user = $userObj->findByEmail($request->input('email'), $request->input('user_type'));
        } elseif ($user_type == User::ADMIN) {
            $user = $userObj->getByEmail($request->input('email'), $request->input('user_type'));
        } elseif ($user_type == User::CUSTOMER) {
            $user = $userObj->getUserByPhoneAndEmail($request->input('user_type'), $request->input('email'));
        }


        if (!empty($user)) {

            if ($user->is_admin_verified == Profile::ADMIN_VERIFIED_NOT_APPROVED && !$this->processInactiveUser($user)) {
                $data['status'] = false;
                $data['otp'] = false;
                $data['redirect_to_login'] = true;
                $data['message'] = __('Your account is inactive. Please contact us via :email', ["email" => config('app.SUPPORT_EMAIL_ADDRESS')], 'danger');

            } elseif (BrandConfiguration::checkWrongMaxAttemptsSecretAnswer() && $this->processInactiveUser($user)) {
                $data['status'] = false;
                $data['otp'] = false;
                $data['redirect_to_login'] = true;
                $data['message'] = __("Your account is inactive. Please contact us via :email", ["email" => config('app.SUPPORT_EMAIL_ADDRESS'), "web"=> config('app.url')]);

            } elseif (BrandConfiguration::securityImageForResetPasssword() && $user_type == User::ADMIN) {

                if ($user->security_image_id == $request->input('security_image')) {
                    $status = $this->sendOTPAndEmailToUser($request, $user);
	                
	                $otp_cache_key = $this->getCacheKey($request, $user);
	                list($conditions, $otp_time, $response_status) = OtpLimitRate::isCheckingOtpRateLimit
	                ($otp_cache_key, $user, false);
					

                    if ($status) {
                        $data['status'] = true;
                        $data['otp'] = $status;
                        $data['otp_time'] = $otp_time;
                        $data['message'] = __('User data get Successfully!');

                    } else {
                        $data['status'] = false;
                        $data['otp'] = false;
                        $data['message'] = __("Some Error Occurred!");
	                    
	                    if(BrandConfiguration::call([FrontendMix::class, 'disableOtpResendBtnWithTimeLimit'])){
		                    $data['otp_time'] = $otp_time;
		                    $data['message'] = OtpLimitRate::prepareOtpLimitMessage($otp_time);;
	                    }

                    }
                }
                else{
                    if (BrandConfiguration::checkWrongMaxAttemptsSecretAnswer()) {
                        $this->incrementWrongSecretAnswerByUserId($user->id);
                    }
                    $data['status'] = false;
                    $data['otp'] = false;
                    $data['message'] = BrandConfiguration::isUserPanelCustomText() ?  __("Invalid information, please check and re-enter"): __("Security image is wrong!");
                }
                
            } elseif ($user->question_one == $request->input('question_one')) {
                $answer_one = $this->customEncryptionDecryption($user->answer_one, config('app.brand_secret_key'), 'decrypt');

                if ($answer_one == $request->input('answer_one')) {
                    $status = $this->sendOTPAndEmailToUser($request, $user);
	                
					$otp_cache_key = $this->getCacheKey($request, $user);
					
	                list($conditions, $otp_time, $response_status) = OtpLimitRate::isCheckingOtpRateLimit
	                ($otp_cache_key, $user, false);
					
					
                    if ($status) {
                        $data['status'] = true;
                        $data['otp'] = $status;
                        $data['otp_time'] = $otp_time;
                        $data['message'] = __('User data get Successfully!');

                    } else {
                        $data['status'] = false;
                        $data['otp'] = false;
	                    $data['message'] = __("Some Error Occurred!");
						
	                    if(BrandConfiguration::call([FrontendMix::class, 'disableOtpResendBtnWithTimeLimit'])){
		                    $data['otp_time'] = $otp_time;
		                    $data['message'] = OtpLimitRate::prepareOtpLimitMessage($otp_time);
	                    }
						
                    }
                } else {
                    if (BrandConfiguration::checkWrongMaxAttemptsSecretAnswer()) {
                        $this->incrementWrongSecretAnswerByUserId($user->id);
                    }
                    $data['status'] = false;
                    $data['otp'] = false;
                    $data['message'] = BrandConfiguration::isUserPanelCustomText() ?  __("Invalid information, please check and re-enter"): __("Security question is wrong!");

                }
            } else {
                if (BrandConfiguration::checkWrongMaxAttemptsSecretAnswer()) {
                     $this->incrementWrongSecretAnswerByUserId($user->id);
                }
                $data['status'] = false;
                $data['otp'] = false;
                $data['message'] = BrandConfiguration::isUserPanelCustomText() ?  __("Invalid information, please check and re-enter"): __("Security question is wrong!");

            }
        } else {
            $data['status'] = false;
            $data['otp'] = false;

            if (BrandConfiguration::allowWriteEmailInResetPassword()){
                    $data['message'] = __(BrandConfiguration::getUniqueEmailValidationErrorMessage());
            }else if (BrandConfiguration::isResetPasswordRequestHasLimit()){
                $data['message'] =__('E-mail or Answer is Incorrect, please check again!');
            }else{
                $data['message'] = __('Data Not Found!');
            }
        }
        flash($data['message'],'danger');
        return response()->json($data, 200);
    }
	
	
	private function getCacheKey($request, User $user){
		
		if($request->input('user_type') == User::CUSTOMER){
			$otp_timer_cache_key = "FORGOT_PASS_FOR_".$user->id;
		}else{
			$otp_timer_cache_key = "RESET_OTP_TIMER_FOR_".$user->id;
		}
		
		return $otp_timer_cache_key;
	}

    private function sendOTPAndEmailToUser(Request $request, $user){
        $status = false;
        try {
            $request->session()->put('forgetPassword', [
                'email' => $user->email,
                'question_one' => $request->question_one,
                'user_type' => $request->user_type,
            ]);
	        
	        $otp = $user->cacheTheOTP();
	        
	        $otp_timer_cache_key = $this->getCacheKey($request, $user);
	
            // if ($request->input('user_type') == User::CUSTOMER) {
            //     // $otp = $this->cacheTheOTP();
	        //     $otp_timer_cache_key = "FORGOT_PASS_FOR_" . $user->id;
	        //      $this->set_otp_to_cache($otp_timer_cache_key, $otp, 3);
            // } else {
	        //     $otp_timer_cache_key = 'RESET_OTP_TIMER_FOR_' . $user->id;
            //     // $otp = $user->cacheTheOTP();
            // }
	        
	        list($conditions, $otp_expire_time, $response_status) = OtpLimitRate::isCheckingOtpRateLimit
	        ($otp_timer_cache_key, $user, false);
			
	        if($conditions){
		        
		        GlobalFunction::unsetBrandCache($otp_timer_cache_key);
		        
		        $expire_time = Config::get('constants.defines.LOGIN_OTP_EXPIRE_TIME')?: 3;
		        $expire_time_min = intval($expire_time) * 60;
		        // GlobalFunction::setBrandCache($otp_timer_cache_key,Carbon::now()->addMinutes($expire_time),$expire_time_min);
		        
		        GlobalFunction::setBrandCache($otp_timer_cache_key,$otp, $expire_time_min);
		        
		        $this->sendSMSUser($user, $otp);
		        $this->sendEmailUser($user, $otp);
		        $status= true;
	        }
			
        } catch (\Throwable $exception) {
			
            $status = false;
        }
        return $status;
    }

    private function sendSMSUser($user, $OTP)
    {
        ///Send SMS OTP
        $language = $this->getLang($user);
        $header = "";
        $phone = $user->phone;
        $message = view('OTP.profile_change.profile_change_' . $language, compact('OTP'))->render();
        $this->setGNPriority(OutGoingEmail::PRIORITY_EXPRESS);
        $this->sendSMS($header, $message, $phone, 1);
    }

    private function sendEmailUser($user, $OTP)
    {
        /// Send E-mail
        $data['OTP'] = $OTP;
        $language = $this->getLang($user);
        $from = config('app.SYSTEM_NO_REPLY_ADDRESS');
        $to = $user->email;
        $template = "user_profile.user_profile";
        //out_going_email
        $this->setGNPriority(OutGoingEmail::PRIORITY_HIGH);
        $this->sendEmail($data, "LOGIN_OTP", $from, $to, "", $template, $language, '', 1);
    }

    public function processInactiveUser($user){
        $status = false;
        if ($user->updated_at->diffInSeconds(Carbon::now()) > 300) {
            $user->wrong_secret_answer_attempt = 0;
            $user->save();
        }elseif($user->wrong_secret_answer_attempt >= GlobalUser::MAX_WRONG_SECRET_LIMIT) {
            $user->is_admin_verified = Profile::ADMIN_VERIFIED_NOT_APPROVED;
            $user->save();
            $this->sendNotificationInActiveUser($user);
            $status = true;
        }
        return $status;
    }

    private function sendNotificationInActiveUser($user)
    {
        //prepare send data
        $data['email'] = $user->email;
        $data['user_type'] = User::USER_TYPES[$user->user_type];
        $data['name'] = $user->name;
        $data['ip'] = $this->getClientIpAddress();

        //prepare email content
        $language = $this->getLang($user);
        $subject_label = 'secret_question_answer_wrong_attempt';
        $from_email = config('app.SYSTEM_NO_REPLY_ADDRESS');
        $template = 'secret_question_answer.wrong_attempts';
        $statistics = (new Statistics())->findById(1, 'user_wrong_attempts_secret_question_email');
        $receivers = array_map('trim', explode(',', $statistics->user_wrong_attempts_secret_question_email));

        //out_going_email
        $this->setGNPriority(OutGoingEmail::PRIORITY_HIGH);

        //send email
        $this->sendEmail($data, $subject_label,$from_email, $receivers, null, $template, $language);

    }

    public function submitOtpSecretQuestion(Request $request)
    {
        $user_data = $request->session()->get('forgetPassword');

        if (!empty($user_data)) {

            $user = null;
            $userObj = new User();
            if ($user_data['user_type'] == User::MERCHANT) {
                $user = $userObj->findByEmail($user_data['email'], $user_data['user_type'], $user_data['question_one']);
            } elseif ($user_data['user_type'] == User::ADMIN) {
                $user = $userObj->getByEmail($user_data['email'], $user_data['user_type'], $user_data['question_one']);
            } elseif ($user_data['user_type'] == User::CUSTOMER) {
                $user = $userObj->getUserByPhoneAndEmail($user_data['user_type'], $user_data['email'], null, $user_data['question_one']);
            }

            if (!empty($user)) {

                if ($user->user_type == User::CUSTOMER) {
                    $otp = $this->get_otp_from_cache('FORGOT_PASS_FOR_' . $user->id);
                } else {
                    $otp = $user->OTP();
                }

                if ($otp == $request->input('otp')) {
                    $data['otp_status'] = true;
                    $data['message'] = __('OTP is match!');
                    return response()->json($data, 200);
                }
            }
        }
        $data['otp_status'] = false;
        $data['message'] = __("OTP not match!");
        return response()->json($data, 200);
    }

    public static function getUserCacheOTPKey($user)
    {
        return "LOGIN_OTP_TIMER_FOR_" . $user->id . "_" . $user->user_type;
    }

    public function getPhoneListByUserType(Array $contact_list, $type=User::CUSTOMER){
       return User::query()
           ->whereIn('phone',$contact_list)
           ->where('user_type',$type)
           ->pluck('phone')
           ->toArray();
    }

    public function checkCustomerExistsByPhone(Array $contact_list){
        $response_list=[];
        if(count($contact_list)>0){
            $customers=$this->getPhoneListByUserType($contact_list);
            foreach ($contact_list as $contact){
                $response_list[$contact]=in_array($contact,$customers);
            }
            return $response_list;
        }
    }
    
    public static function isUserInactive($userObj)
    {
        $user_inactive = false;
        if (!empty($userObj) && $userObj->is_admin_verified == Profile::ADMIN_VERIFIED_NOT_APPROVED) {
            $user_inactive = true;
        }
        return $user_inactive;
    }

    public static function getUserByPhone($phone_no, $user_type = null){
       if($user_type != null){
           $user_type = (int) $user_type;
       }

        $query = Profile::query();

        if(!empty($phone_no)){
            $query = $query->where('phone', $phone_no);
        }

        if(in_array($user_type, array_keys(Profile::USER_TYPES), true)){
            $query = $query->where('user_type', $user_type);
        }

        return $query->first();

    }

    public function resendEmailVerificationValidation($request)
    {
        $rules_and_messages = AppRequestValidation::resendEmailValidationRulesAndMessages();
        
        return Validator::make($request, $rules_and_messages['rules'], $rules_and_messages['messages']);
    }

    public static function getNameSurnameByFullName($full_name,$merchant_id = null)
    {
        $name = '';
        $surname = '';
        if (! empty($full_name)) {
            $name_array = explode(' ', $full_name);
            if (count($name_array) > 2) {
                $surname = $name_array[\common\integration\Utility\Arr::keyLast($name_array)];
                unset($name_array[\common\integration\Utility\Arr::keyLast($name_array)]);
                $name = implode(' ', $name_array);
            } else if (count($name_array) == 2) {
                $name = $name_array[0];
                $surname = $name_array[1];
            } else {
                $name = $name_array[0];
            }
        }

        if (BrandConfiguration::call([Mix::class, 'isAllowAddIndividualAuthorizedPersonSurname']) && !empty($merchant_id)) {
            $merchantExtras = new MerchantExtras();
            $merchantExtrasObj = $merchantExtras->findByMerchantId($merchant_id);
            $name = $full_name ?? '';
            $surname = !empty($merchantExtrasObj) ? $merchantExtrasObj->authorized_person_surname : $surname;
        }
        return [
            'name' => $name,
            'surname' => $surname
        ];
    }

    public function deactivateUser(Request $request, $user)
    {
        $data['action'] = "Deactivate Self User Account";
        $status_code    = config('apiconstants.API_SUCCESS');
        $description    = "User Successfully Deactivated.";
        $hasBalance     = false;

        $userProfile = (new UserProfile())->getUserProfilesById($user->id);
        if ($userProfile->status != UserProfile::ACTIVE){
            $status_code = config('apiconstants.API_FAILED');
            $description = "You are not an active user. You can't deactivate your account.";
        }
        
        //validate password
        if ($status_code == config('apiconstants.API_SUCCESS') && $request->has('password') && !Hash::check($request->password, $user->password)) {
            $status_code = config('apiconstants.API_FAILED');
            $description = "Your given password is not matched.";
        }

        if ($status_code == config('apiconstants.API_SUCCESS')){
            //check balance
            //check pending transaction
            $wallets = $user->walletsCollection;
            foreach ($wallets as $wallet) {
                if ($wallet->amount > 0) {
                    $hasBalance = true;
                }
            }

            if ($hasBalance) {
                $status_code = config('apiconstants.API_FAILED');
                $description = "You are not allowed to deactivate your account. To deactivate your account please withdraw your balance and try again.";
            }
        }

        if ($status_code == config('apiconstants.API_SUCCESS')) {
            //deactivate account and logout send mail to user
            //is_self_deactivated
            //self_deactivation_date
            $user_data         = ["is_self_deactivated" => self::USER_SELF_DEACTIVATE, "self_deactivation_date" => ManipulateDate::toNow(), "is_send_activation_request" => self::ACCOUNT_ACTIVATION_NOT_SEND];

            [$update_status, $user_profile_status, $wallet_status] = $this->updateUserInfoAndWallet($user, $user_data, UserProfile::DISABLED );

            if (!$update_status || !$user_profile_status || !$wallet_status) {
                $status_code = config('apiconstants.API_FAILED');
                $description = "Something went wrong. Please contact support.";
            }

            if ($status_code == config('apiconstants.API_SUCCESS')) {
                //user successfully deactivated. send email to user
                $this->sendChangedNotification($user, self::USER_STATUS_CHANGE, ['status' => __("Deactivated")]);

            }
        }


        (new ManageLogging())->createLog($data);
        unset($data['action']);
        
        $data['status_code'] = $status_code;
        $data['description'] = __($description);

        return $data;
    }
    
    private function updateUserInfoAndWallet($user, $user_data, $user_status = UserProfile::ACTIVE){
        $data['action'] = "EXCEPTION UPDATE USER INFO AND WALLET";
        $update_status = 0;
        $user_profile_status = 0;
        $wallet_status = 0;
        try {
            DB::beginTransaction();
            $update_status = $user->updateUserById($user->id, $user_data);
            list($user_profile_status, $wallet_status) = $this->updateUserStatus($user->id, $user_status);

            if (!$update_status || !$user_profile_status || !$wallet_status){
                throw new \Exception("Failed to update: User: ".$update_status." User Profile: ".$user_profile_status." User Wallet: ".$wallet_status);
            }
            DB::commit();
        }catch (\Throwable $throwable){
            $data['error'] = $throwable->getMessage();
            $data['line'] = $throwable->getLine();
            (new ManageLogging())->createLog($data);
            DB::rollBack();
            $update_status = 0;
            $user_profile_status = 0;
            $wallet_status = 0;
        }
        return [$update_status, $user_profile_status, $wallet_status];
    }

    public function activateUser($user, $request_from_admin = false){
        $data['action'] = "Activate Self User Account";
        $status_code = config('apiconstants.API_SUCCESS');
        $description = "User Successfully Activated.";
        
        $userProfile = (new UserProfile())->getUserProfilesById($user->id);
        if ($userProfile->status != UserProfile::DISABLED && !$request_from_admin){
            $status_code = config('apiconstants.API_FAILED');
            $description = "You are not a disabled user. You can't activate you account.";
        }
        $user_data = ["is_self_deactivated" => self::USER_SELF_ACTIVATE ,"self_deactivation_date" => null,"is_send_activation_request" => self::ACCOUNT_ACTIVATION_NOT_SEND];


        if ($status_code == config('apiconstants.API_SUCCESS')) {
            [$update_status, $user_profile_status, $wallet_status] = $this->updateUserInfoAndWallet($user, $user_data, UserProfile::ACTIVE);

            if (!$update_status || !$user_profile_status || !$wallet_status) {
                $status_code = config('apiconstants.API_FAILED');
                $description = "Something went wrong. Please contact support.";
            }
            
            if ($status_code == config('apiconstants.API_SUCCESS')) {
                //user successfully activated. send email to user
                $status = $user->language == "tr" ? 'Aktife':'Activated';
                $this->sendChangedNotification($user, self::USER_STATUS_CHANGE, ['status' => $status]);
            }
        }

        (new ManageLogging())->createLog($data);
        unset($data['action']);
        $data['status_code'] = $status_code;
        $data['description'] = __($description);

        return $data;
    }

    public function requestForAccountApproval($user)
    {
        $data['action'] = "Request for account approval.";
        $status_code = config('apiconstants.API_SUCCESS');
        $description = "User Successfully Activated.";
        
        $days_from_deactivation = ManipulateDate::getDaysDiffFromDate($user->self_deactivation_date);
        if ($days_from_deactivation <= GlobalUser::USER_MAX_DEACTIVATE_DAYS) {
            return $this->activateUser($user);
        } else {
            //send request to admin
//            $adminObj = (new User())->getFirstUserByUserType(User::ADMIN);
            $notification_data['user'] = $user;
            $this->sendChangedNotification($user,self::USER_ACTIVATION_REQUEST, $notification_data);
            
            $user_data = ["is_send_activation_request" => User::ACCOUNT_ACTIVATION_SEND];
            $update_status = $user->updateUserById($user->id, $user_data);
            if($update_status){
                $status_code = config('apiconstants.API_SUCCESS');
                $description = "Your Account activation request is processing.";  
            }else{
                $status_code = config('apiconstants.API_FAILED');
                $description = "Failed to send request.";
            }
            
            (new ManageLogging())->createLog($data);
            unset($data['action']);
            
            $data['status_code'] = $status_code;
            $data['description'] = __($description);

            return $data;
        }
    }
    private function sendChangedNotification($userObj, $type='', $data = [])
    {
        //Sending E-mail ,SMS and push notification
        $is_notification = UserSetting::PUSH_NOTIFICATION_DISABLED;
        $is_email = UserSetting::EMAIL_DISABLED;
        $is_sms = UserSetting::SMS_DISABLED;
        
        if($type == self::USER_STATUS_CHANGE) {
            $is_email = UserSetting::EMAILENABLED;
            $notification_data['email_data']['status'] = $data['status'];
            $actionList = UserSetting::action_lists['user_status_change'];
        }elseif ($type == self::USER_ACTIVATION_REQUEST){
            $is_notification = UserSetting::PUSH_NOTIFICATION_ENABLED;
            $data['user'] = $userObj; 
            $notification_data['notification_data']['admin_notification_action'] = CommonNotification::PROFILE_UPDATED;
            $notification_data['notification_data']['message_en'] = view('notifications.account_activation_request.account_activation_en',['data'=>$data]);
            $notification_data['notification_data']['message_tr'] = view('notifications.account_activation_request.account_activation_tr',['data'=>$data]);
            $actionList = UserSetting::action_lists['account_activation_request'];
        }

        if(!empty($type)) {
            
            $this->checkAndSendNotification(
                $actionList,
                $notification_data,
                $is_email,
                $is_sms,
                $is_notification,
                $userObj
            );
        }

        return true;
    }

    public static function getFormatedDateOfBirth($dateOfBirth):array
    {
        $dateOfBirth = ManipulateDate::createFormatDate($dateOfBirth, 'd/m/Y', 'Y-m-d');
        $birth_day = ManipulateDate::getCarbonDayMonthYear($dateOfBirth, 'd');
        $birth_month = ManipulateDate::getCarbonDayMonthYear($dateOfBirth, 'm');
        $birth_year = ManipulateDate::getCarbonDayMonthYear($dateOfBirth, 'Y');

        return [
            $birth_day,
            $birth_month,
            $birth_year,
        ];
    }

    public function wallet_logout()
    {
        $user = auth()->user();
        if ($user->user_category == Profile::NOT_VERIFIED) {
            $user->update(['language' => Profile::LANG_LIST[1]]);
        }

        if (BrandConfiguration::allowLoginSessions()) {
            Auth::user()->login_at = Null;
            Auth::user()->save();
        }

        auth()->guard()->logout();
        session()->invalidate();
    }


    public function merchant_logout(): void
    {
        $authUserObj = auth()->user();
        if(BrandConfiguration::logoutMerchantOnSessionTimeout()) {
            $logData['merchant_id'] = $authUserObj->id ?? '';
            $logData['merchant_name'] = $authUserObj->name ?? '';
            $logData['logout_reason'] = $request->logout_reason ?? 'Default';
            (new ManageLogging())->createLog($logData);
        }

        if(BrandConfiguration::allowLoginSessions()){
            $authUserObj->login_at = null;
            $authUserObj->save();
        }

        auth()->guard()->logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();
    }

    /**
     * @param array $input
     * @return void
     */
    public function handleForgotPasswordRequestLimit(array $input): int
    {
        $increment = 1;
        $reset_password_timer_cache_key = $input['key'];
        if (Cache::has($reset_password_timer_cache_key)) {
            Cache::increment($reset_password_timer_cache_key, $increment);
            return Cache::get($reset_password_timer_cache_key) ;
        } else {
            Cache::add($reset_password_timer_cache_key, $increment, ManipulateDate::addSeconds($input['time']));
            return $increment;
        }
    }

    public function updateKyc(Request $request)
    {

        try {
            $auth_user = Auth::user();
            if(BrandConfiguration::walletEmailVerified() && isset($request->email_verification)){
                (new  GlobalUser())->sentVerifyEmail($auth_user->email);
                $data['status_code']  = ApiService::API_SERVICE_SUCCESS_CODE;
                $data['description'] = 'A verification link has been sent to your registered email address, please check it.';
                $data['result'] = null;
                return $data;
            }

            if ($auth_user->user_category == \App\Models\Profile::NOT_VERIFIED && empty($auth_user->tc_number)) {

                if(BrandConfiguration::call([Mix::class, 'shouldApplyKycVerificationStaticInfo'])) {
                    $user_statistics_data = (new UserStatistic())->preparedUserStatisticData($request);
                }

                $rules_and_messages = AppRequestValidation::walletUserKycUpdateValidationRulesAndMessages();
                $validator = Validator::make($request->all(),$rules_and_messages['rules'], $rules_and_messages['messages']);

                if ($validator->fails()) {
                    $data['status_code'] = ApiService::API_SERVICE_FAILED_CODE;
                    $data['description'] = $validator->errors()->first();
                    $data['result']['errors'] = $validator->errors();
                    $data['result']['inputs'] = $request->all();
                    return $data;
                }

                $birth_day = null;
                $birth_month = null;

                if (isset($request->date_of_birth) && !empty($request->date_of_birth) && !BrandConfiguration::isAllowDayAndMonthOnKyc()) {
                    $date_of_birth = ManipulateDate::format(9, GlobalFunction::getBirthYearToDateOfBirth($request->date_of_birth));
                    $birth_year = explode("-",$date_of_birth);
                }
                
                if(BrandConfiguration::isAllowDayAndMonthOnKyc()){
					
                    list($birth_day, $birth_month, $birth_year) = GlobalUser::getFormatedDateOfBirth($request->date_of_birth);
					
                    $date_of_birth = ManipulateDate::toDateString(ManipulateDate::createFromFormat($request->date_of_birth), true);
					
					if(!empty($birth_year)){
						$request->date_of_birth = $birth_year;
					}
					
                }

                if ($request->isPanel && BrandConfiguration::isUserPanelCustomText()) {
                    if (isset($request->sector) && !empty($request->sector) && $request->sector == Profile::OTHER_SECTOR_ID && empty($request->other_sector)){
                        $data['status_code'] = ApiService::API_SERVICE_FAILED_CODE;
                        $data['description'] = null;
                        $data['result'] = null;
                        $data['redirect_back_with_input'] =  $request->all();
                        return $data;
                    }
                }

                $profile = new Profile();
                if (isset($request->tckn) && !empty($request->tckn)) {
                    $tckn_value = $profile->getUserByTCKNAndType($request->tckn, Profile::CUSTOMER);
                    if(!empty($tckn_value)) {
                        $data['status_code'] = ApiService::API_SERVICE_FAILED_CODE;
                        $data['description'] = __('Account with this ID number already exists. If you do not own that account, please, contact :email with your ID and phone number.',
                            ['email' => config('brand.emails.operations')]);
                        $data['redirect_back_with_input'] = $request->all();
                        $data['result']['user'] = $auth_user;
                        $data['result']['inputs'] = $request->all();
                        if ($request->isPanel){
                            Session::put('login-attempts', 'yes');
                        }
                        return $data;
                    }
                }

                ///capitalized name/surname
                $cap_name = $this->capitalizeTextInput($request->name);
                $cap_surname = $this->capitalizeTextInput($request->surname);

                ///validate KYC
                if (BrandConfiguration::disableKycVerficationForUser()) {
                    //PP-107 Kkb integration will be removed
                    $status = 100;
                } else {
                    $country_name = null;
                    if(BrandConfiguration::call([BackendWallet::class, 'isAllowAddNationalityFromKPSAPIInLog']) && !empty($request->country)){
                        $country_name = (new Country())->getCountryNameById($request->country);
                    }
                    $status = $profile->validateKYCform(
                        $request->tckn, $cap_name, $cap_surname, $request->date_of_birth, $birth_day, $birth_month, $country_name
                    );
                }


                if($status == 3){
                    $data['status_code'] = ApiService::API_SERVICE_FAILED_CODE;
                    $data['description'] = 'KYC information is wrong';
                    $data['redirect_back_with_input'] = $request->all();
                    $data['result']['user'] = $auth_user;
                    $data['result']['inputs'] = $request->all();
                    if(!empty($data['result']['user']['avatar'])) {
                        $data['result']['user']['avatar'] = Storage::url($data['result']['user']['avatar']);
                    }
                    return$data;

                }else if ($status == 100){

                    $inputData = [
                        'name' => $cap_name." ".$cap_surname,
                        'first_name' => $cap_name,
                        'last_name' => $cap_surname,
                        'dob' => $date_of_birth,
                        'tc_number' => $request->tckn,
                        'gender' => $request->gender,
                        'city' => $request->city,
                        'country' => empty($request->country) ? 0 : $request->country,
                        'sector_id' => $request->sector,
                        'address' => $request->address,
                        'verification_token' => str_random(40),
	                    'phone' => \session()->get('non_user_phone') ?? @$auth_user->phone,
                        'updated_password_at' => ManipulateDate::toNow(),
                        'activated_at' => ManipulateDate::toNow()
                    ];
                    if(!BrandConfiguration::hideSecrectQuestionSectionFromUserKycForm() ){
                        $inputData['question_one'] = $request->question_one;
                        $inputData['answer_one'] = $this->customEncryptionDecryption($request->answer_one, config('app.brand_secret_key'), 'encrypt');
                    }
					
					
                    $extra_param = [
                        'user_category' => BrandConfiguration::disabledKYCVerifiedOption() ? Profile::NOT_VERIFIED :
	                        Profile::VERIFIED,
	                        
                    ];
					
                    if ($request->isPanel){
                        $extra_param['other_sector'] = $request->other_sector;
                        $extra_param['income_info'] = $request->income_info ?? 0;
                    }else{
                        $extra_param['notification_for'] = Profile::NOFIT_KYC;
                    }

                    if(!empty($user_statistics_data)) {
                        $extra_param['user_statistics_data'] = $user_statistics_data;
                    }

                    $profileObj = $profile->updateUser(
                        $auth_user->id, $inputData, "EDIT", $extra_param
                    );

                    if (!empty($profileObj)) {
                        //Sending E-mail and push notification to user after successfully verified
                        $noti_data['email_data']['name'] = !empty($request->name) ? $request->name : '';
                        $noti_data['email_data']['currencies'] = GlobalCurrency::getCurrencies($auth_user->id);
                        $noti_data['email_data']['currency_settings'] = (new CurrenciesSettings())->getByUserType(User::CUSTOMER);
                        $noti_data['email_data']['user_types'] = BrandConfiguration::getUserCategoryList(\config('brand.name_code'), \config('constants.BRAND_NAME_CODE_LIST'));
                        $noti_data['email_data']['verified_date'] = ManipulateDate::format(9, ManipulateDate::toNow());
                        $noti_data['notification_data']['verified_date'] = ManipulateDate::format(9, ManipulateDate::toNow());
                        $noti_data['notification_data']['notification_action'] = CommonNotification::PROFILE_KYC_UPDATED;

                        $this->checkAndSendNotification(
                            UserSetting::action_lists['kyc_success'],
                            $noti_data,
                            UserSetting::EMAILENABLED,
                            UserSetting::SMS_DISABLED,
                            UserSetting::PUSH_NOTIFICATION_ENABLED,
                            $profileObj
                        );

                        $data['status_code'] = ApiService::API_SERVICE_SUCCESS_CODE;
                        $data['description']  = BrandConfiguration::kycVerifiedMessage();
                        $data['result']['user'] = $profileObj;
                        $data['redirect_url'] = route('home');
                        if(!empty($data['result']['user']['avatar'])) {
                            $data['result']['user']['avatar'] = Storage::url($data['result']['user']['avatar']);
                        }
                        
                        if (BrandConfiguration::call([Mix::class,'allowToSendEmailForBlackListUserSanctionScannerVerification'])) {

                            $sanction_scanner_data = $this->recheckSacntionScannerAndBlockUser('RECHECK_SANCTION_SCANNER_VERIFICATION', $request['name'], $request['surname'], $profileObj, $auth_user, $request, $data);
                            $data = $sanction_scanner_data;
                        }
                        return $data;

                    } else {
                        $data['status_code'] = ApiService::API_SERVICE_FAILED_CODE;
                        $data['description']  ='Update failed';
                        $data['result']['inputs'] = $request->all();
                        $data['result']['user'] = $auth_user;
                        $data['redirect_back_with_input'] = $request->all();
                        if(!empty($data['result']['user']['avatar'])) {
                            $data['result']['user']['avatar'] = Storage::url($data['result']['user']['avatar']);
                        }
                        return $data;
                    }
                }else{
                    $data['status_code'] = ApiService::API_SERVICE_FAILED_CODE;
                    $data['description']  = __('Unknown error');
                    $data['result']['inputs'] = $request->all();
                    $data['result']['user'] = $auth_user;
                    if(!empty($data['result']['user']['avatar'])) {
                        $data['result']['user']['avatar'] = Storage::url($data['result']['user']['avatar']);
                    }
                    return $data;
                }

            } else {

                $data['status_code'] = ApiService::API_SERVICE_FAILED_CODE;
                $data['description']  = 'User already verified';
                $data['result']['user'] = $auth_user;
                if(!empty($data['result']['user']['avatar'])) {
                    $data['result']['user']['avatar'] = Storage::url($data['result']['user']['avatar']);
                }
                return $data;
            }
        }catch(\Throwable $t){
            $data['status_code'] = ApiService::API_SERVICE_FAILED_CODE;
            $data['description']  = $t->getMessage()." at ".$t->getLine();
            return $data;
        }

    }
    
    public function responseForKycUpdate($result, $isPanel){
        $code = $result['status_code'];
        $description = __($result['description']);
        
        $data = $result['result'];
        
        $log_data['action'] = "KYC_UPDATE";
        $log_data['code'] = $code;
        $log_data['isPanel'] = $isPanel;
        $log_data['description'] = $result['description'];
        
        (new ManageLogging())->createLog($log_data);
        
        if ($isPanel){
            if (isset($result['description']) && in_array($result['description'],$result) && !empty($result['description'])){
                flash($description, $code == ApiService::API_SERVICE_SUCCESS_CODE ? 'success' : 'danger');
            }

            if (isset($result['redirect_back_with_input']) && in_array($result['redirect_back_with_input'],$result) && !empty($result['redirect_back_with_input'])){
                return redirect()->back()->withInput($result['redirect_back_with_input']);
            }
            if (isset($result['redirect_url']) && in_array($result['redirect_url'],$result) && !empty($result['redirect_url'])){
                return redirect()->to($result['redirect_url']);
            }

            if(isset($result['is_logout']) && $result['is_logout']){
                Auth::logout();
            }
            return redirect()->back();
        }else{
            if(isset($result['is_logout']) && $result['is_logout'] && !empty($result['result']['user'])){
                (new RevokeTokens())->logoutPastLogin($result['result']['user']['id']);
            }
            return $this->sendApiResponse($description, $data, $code);
        }
    }


    public static function isRestrictedTransactionsForNonVerifiedUsers ($user_id): bool
    {
        $response = false;
	    if(BrandConfiguration::disabledKYCVerifiedOption()){
		    
		    $user_obj = (new User())->findById($user_id);
			
		    if (!empty($user_obj)
			    && $user_obj->user_type == User::CUSTOMER
			    && $user_obj->user_category == User::NOT_VERIFIED) {
			    /*
				 * User::NOT_VERIFIED means still kyc not updated
				 * User::VERIFIED means kyc updated but no deposit yet
				 */
			    $response = true;
		    }
	    }elseif(BrandConfiguration::restrictTransactionsForNonVerifiedUsers()){
		    
		    $user_obj = (new User())->findById($user_id);
			
		    if (!empty($user_obj)
			    && $user_obj->user_type == User::CUSTOMER
			    && ($user_obj->user_category == User::NOT_VERIFIED
				    || $user_obj->user_category == User::VERIFIED)) {
			    /*
				 * User::NOT_VERIFIED means still kyc not updated
				 * User::VERIFIED means kyc updated but no deposit yet
				 */
			    $response = true;
		    }
	    }

        return $response;
    }

    public static function setCookieForCaptcha($cookie_name, $cookie_value = null){
        $cache_cookie = Cookies::get($cookie_name);
        if(!empty($cookie_value)){
            $value = $cookie_value;
        }else{
            $value = ManipulateDate::today();
        }

        if(empty($cookie_value) || empty($cache_cookie) || (!empty($cache_cookie) && is_numeric($value)) ||  (!empty($cache_cookie) && !ManipulateDate::isToday($cache_cookie))){
            Cookies::set($cookie_name, $value, self::MAX_TIME_OF_COOKIE);
            return false;
        }
        return true;
    }

    public static function getCookieForCaptcha($cookie_name){
        return Cookies::get($cookie_name);
    }

    public static function deleteCookieForCaptcha($cookie_name){
        return Cookies::delete($cookie_name);
    }

    public static function cookieNameForCaptcha($panel, $counter = false){
        $cookie_name = $panel . '_cookie_for_captcha';
        if($counter){
            $cookie_name = $panel . '_cookie_for_wrong_captcha_counter';
        }
        return $cookie_name;
    }
	
	public static function walletUserCategoryAllowProcessForDeposit($userObj){
		return !empty($userObj) && BrandConfiguration::disabledKYCVerifiedOption()
      && !empty($userObj->tc_number) && $userObj->user_category == Profile::NOT_VERIFIED;
	}

    public function sentVerifyEmail($email, $is_api = 0 , $is_panel_admin = false)
    {
        if (empty($email)) {
            return false;
        }

        $token = Str::random(60) . time() . $email;
        $encrypted =DataCipher::customEncryptionDecryption($token, \config('app.brand_secret_key'), 'encrypt', 1);

        if ($is_panel_admin) {
            $data = [
                'link' => config('app.app_frontend_url') . "/verify-email/" . $encrypted . "/" . $is_api
            ];
        } else {
            $data = [
                'link' => route('verify.email', ['token' => $encrypted, 'api' => $is_api])
            ];
        }
        $cacheKey = BrandConfiguration::EMAIL_VERIFIY_TOKEN . $encrypted;

        GlobalFunction::setBrandCache($cacheKey, $email, BrandConfiguration::getEmailVerifyTokenExpireTime() * 60);
        $lan = app()->getLocale();

        $subjet = 'verify_email';

        //out_going_email
        $this->setGNPriority(OutGoingEmail::PRIORITY_EXPRESS);
        $this->sendEmail($data, $subjet, \config('constants.defines.MAIL_FROM_ADDRESS'),
            $email, null, 'login.email_verify',
            $lan);

    }
	
	public static function getDatbofBirthFormat($date_of_birth){
		
		$date = ManipulateDate::format(10, $date_of_birth);
		if(BrandConfiguration::AllowCustomerKycEditView()){
			$date = ManipulateDate::format(1, $date_of_birth);
		}
		
		return $date;
		
	}

    public function createUserAgreement() {
        if (BrandConfiguration::isAllowStaticContent()) {
            $search['category_name'] = StaticContent::USER_AGREEMENT;
            $search['status'] = StaticContent::STATUS_ACTIVE;
            $static_contents = (new StaticContent())->searchStaticContentData($search);
            (new UserAgreement())->createUserAgreementByStaticContent($static_contents, Auth::id());
        }
    }
    
    public static function customerNumberValidationRules()
    {
        return ['required',
            function($attribute, $value, $fail){
                $pattern = '/^([0-9\s\-\+\(\)]*)$/';
                if (!preg_match($pattern, $value)) {
                    $fail($attribute.' is not in the desired format.');
                }
            }];
    }

    public function userEmailVerification($request, $userObj = null)
    {

        $action = $request->input('action') ?? '';
        $log_data = [];
        $status_code = $status_message = '';
        $data = [];
        $updatable_data = [];

        $validator = Validator::make($request->all(), AppRequestValidation::emailVerificationValidationRules($request, $action));
        if ($validator->fails()) {
            $status_message = $validator->errors()->first();
            $status_code = ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR;
        }

        try {

            if (empty($status_code) && empty($userObj)) {
                $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_USER_NOT_FOUND];
                $status_code = ApiService::API_SERVICE_USER_NOT_FOUND;
            }

            if (empty($status_code) && $userObj->is_email_verifyed == Profile::EMAIL_NOT_VERIFIED) {

                if (empty($status_code) && !empty($action) && $action == 'send_email_verification_otp') {

                    $log_data['action'] = 'SEND_EMAIL_VERIFICATION_OTP';
                    [$status_code, $status_message, $updatable_data] = $this->sendEmailVerificationOTP($userObj);

                } elseif (empty($status_code) && !empty($action) && $action == 'verify_email_otp'){

                    $log_data['action'] = 'VERIFY_EMAIL_OTP';
                    [$status_code, $status_message] = $this->verifyEmailOTP($request,$userObj);

                } elseif (empty($status_code) && !empty($action) && $action == 'resend_email_verification_otp'){

                    $log_data['action'] = 'RESEND_EMAIL_VERIFICATION_OTP';
                    [$status_code, $status_message, $updatable_data] = $this->sendEmailVerificationOTP($userObj);

                } else {
                    $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_UNKNOWN_ERROR];
                    $status_code = ApiService::API_SERVICE_UNKNOWN_ERROR;
                }

                if ($status_code == config('constants.SUCCESS_CODE')) {
                    session()->put('SECURITIES_UPDATABLE_DATA', $updatable_data);
                }
            }

        } catch (\Throwable $exception) {
            $status_message = $exception->getMessage();
            $status_code = ApiService::API_SERVICE_UNKNOWN_ERROR;
        }

        $log_data['data'] = $updatable_data;
        $log_data['status_code'] = $status_code;
        $log_data['status_message'] = $status_message;

        (new ManageLogging())->createLog($log_data);


        return [$status_code, $status_message, $data];
    }

    public function sendEmailVerificationOTP($userObj){

        $status_code = $status_message = '';
        $updatable_data = [];

        if (ManageOtp::checkResendOtpLimit(ManageOtp::USER_PANEL_EMAIL_VERIFY, Auth::user()->id)) {
            $status_message = __(ManageOtp::getMaximumOtpLimitExceedMessage());
            $status_code = ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR;
        }

        if (empty($status_code) && !empty($userObj)) {

            $updatable_data = [
              'email' => $userObj->email,
              'user_id' => $userObj->id,
              'language' => $userObj->language,
              'action' => "email-verify-otp"
            ];

            (new ManageOtp())->sendEmailVerificationOTP($userObj, $userObj->phone, $userObj->email, $userObj->language);

            $status_code = config('constants.SUCCESS_CODE');
            $status_message = __('OTP Successfully Send');
        }

        return [$status_code, $status_message, $updatable_data];
    }

    public function verifyEmailOTP($request, $userObj){


        $status_code = '';
        $status_message = '';

        if(!empty($userObj)){

            $otp_key_prefix = "EMAIL_VERIFICATION_OTP_FOR_";
            $requested_otp = $request->input('otp');
            $cached_otp = cache()->get($otp_key_prefix . $userObj->id);
            $action = 'verify-email';

            if ($requested_otp == $cached_otp) {

                ManageOtp::otpCacheClearByActionName($action, $userObj->id);

                $userObj->is_email_verifyed = Profile::EMAIL_VERIFIED;
                $userObj->save();

                $status_code = config('constants.SUCCESS_CODE');
                $status_message = __('Your email has been verified successfully.');


            } else {
                $status_message = __('OTP Not Match OR Expired');
                $status_code = ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR;
                if ($action == "verify-email") {
                    if (ManageOtp::checkResendOtpLimit(ManageOtp::USER_PANEL_EMAIL_VERIFY_OTP_SUBMIT, auth()->user()->id, true)) {
                        $status_message = __(ManageOtp::getMaximumOtpRequestMessage());
                        $status_code = ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR;
                    }

                }
            }
        }



        return [$status_code, $status_message];
    }

    public function checkSMSorEmailEnabledForChangeEmailOTP($action = '')
    {

        $is_enabled_send_otp_sms = UserSetting::SMSENABLED;
        $is_enabled_send_otp_email = UserSetting::EMAIL_DISABLED;

        if (!empty($action)) {

            if ($action == self::ACTION_CHANGEMAIL) {

                if (BrandConfiguration::call([FrontendMix::class, 'isAllowVerifyEmailByOTP'])) {
                    $is_enabled_send_otp_sms = UserSetting::SMS_DISABLED;
                    $is_enabled_send_otp_email = UserSetting::EMAILENABLED;
                }
            }

            if ($action == self::ACTION_EMAIL_VERIFICATION) {

                if (BrandConfiguration::call([FrontendMix::class, 'isAllowVerifyEmailByOTP'])) {
                    $is_enabled_send_otp_sms = UserSetting::SMS_DISABLED;
                    $is_enabled_send_otp_email = UserSetting::EMAILENABLED;
                }
            }
        }


        return [$is_enabled_send_otp_sms, $is_enabled_send_otp_email];
    }


    public function isSendEmailVerificationLink()
    {

        $is_send_email_verification_link = true;

        if (BrandConfiguration::call([FrontendMix::class, 'isAllowVerifyEmailByOTP'])) {
            $is_send_email_verification_link = false;
        }

        return $is_send_email_verification_link;
    }
    public function recheckSacntionScannerAndBlockUser($action, $name = '', $surname = '', $profileObj = [], $auth_user = [], $request = [], $data = []){
        $log_data['action']                  = $action;
        $fullname                            = ($name ?? '') . " " . ($surname ?? '');
        $sanctionScanner                     = (new SanctionScanner())->verifyUserByName($fullname);
        $data['sanction_scanner_status'] = $log_data['sanction_scanner_status'] = $sanctionScanner->isFailed();
        if ($log_data['sanction_scanner_status']) {
            try {
                list($userProfile, $wallet_status) = $this->updateUserStatus($profileObj->id, UserProfile::BLACKLISTED);
                $log_data['profile_update_status'] = $userProfile;
                $log_data['wallet_status'] = $wallet_status;
                //get receiver email
                $statistics                  = (new Statistics())->findDataByColumn('black_list_user_email_receivers');
                $email_receivers             = \common\integration\Utility\Str::explode(",", \common\integration\Utility\Str::replace(" ", "", $statistics));
                $log_data['email_receivers'] = $email_receivers;
                if (!empty($email_receivers)) {

                    $p_data = [
                        'name'          => $request['name'] ?? '',
                        'surname'       => $request['surname'] ?? '',
                        'email'         => $profileObj->email ?? '',
                        'phone'         => $profileObj->phone ?? '',
                        'user_language' => app()->getLocale(),
                    ];

                    $log_data['data'] = $p_data;
                    $status           = $this->sendEmail
                    (
                        $p_data,
                        'blacklist_user',//subject
                        config('app.SYSTEM_NO_REPLY_ADDRESS'), // from email
                        $email_receivers,
                        '',//attachment
                        'info_change_blacklist_user.info_change',//template
                        $p_data['user_language']
                    );
                    $log_data['email_send_status'] = $status ?? "No Email Found";

                    throw new Exception(__("We are unable to process your transaction at this time. If you have any questions, you can contact us through the communication channels on our website :website .",["website"=>\config("brand.contact_info.website")]));
                }
            } catch (\Throwable $throwable) {
                $log_data['error']                = $throwable->getMessage();
                $data['status_code']              = ApiService::API_SERVICE_FAILED_CODE;
                $data['description']              = $log_data['error'];
                $data['is_logout']                = true;
                $data['result']['inputs']         = $request->all();
                $data['result']['user']           = $auth_user;
                $data['redirect_back_with_input'] = $request->all();
                if (!empty($data['result']['user']['avatar'])) {
                    $data['result']['user']['avatar'] = Storage::url($data['result']['user']['avatar']);
                }
            }

            if(BrandConfiguration::call([Mix::class, 'allowToSendEmailForBlackListUserSanctionScannerVerification'])){
                $log_data['error_code'] = 'Blacklist user found';
            }
            (new ManageLogging())->createLog($log_data);
        }
        return $data;
    }

    public function updateUserStatus($user_id, $status){
        $user_profile_status = 0;
        $wallet_status = 0;
        if($status == UserProfile::BLACKLISTED){   //change status to Black listed
            $user_profile_status = (new UserProfile())->updateUserProfileByUserId($user_id,
                [
                    "status" => UserProfile::BLACKLISTED
                ]
            );
            $wallet_status = (new Wallet())->updateWalletDataByUserId($user_id,
                [
                    'status' => Wallet::STATUS_INACTIVE
                ]
            );
        }elseif($status == UserProfile::ACTIVE){

            $user_profile_status = (new UserProfile())->updateUserProfileByUserId($user_id,
                [
                    "status" => UserProfile::ACTIVE
                ]
            );
            $wallet_status = (new Wallet())->updateWalletDataByUserId($user_id,
                [
                    'status' => Wallet::STATUS_ACTIVE
                ]
            );

        }elseif($status == UserProfile::DISABLED){
            $user_profile_status = (new UserProfile())->updateUserProfileByUserId($user_id,
                [
                    "status" => UserProfile::DISABLED
                ]
            );
            $wallet_status = (new Wallet())->updateWalletDataByUserId($user_id,
                [
                    'status' => Wallet::STATUS_INACTIVE
                ]
            );
        }

        return [$user_profile_status, $wallet_status];
    }

    public function ipValidation() : array
    {
        $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
        $status_description = '';
        $server_ip = $this->getServerIpAddress();
        $statisticsObj = (new Statistics())->findFirstRow();
        if (!empty($statisticsObj) && !empty($statisticsObj->brand_server_ip_list)) {
            $ipArray = Arr::map('trim', Arr::explode(',', $statisticsObj->brand_server_ip_list));
            if (!Arr::isAMemberOf($server_ip, $ipArray) && !Helper::isLocalServerEnvironment()) {
                $status_code = ApiService::API_SERVICE_IP_VALIDATION_ERROR;
                $status_description = __('This IP address is not allowed');
            }
        }else{
            $status_code = ApiService::API_SERVICE_IP_VALIDATION_ERROR;
            $status_description = __('This IP address is not allowed');
        }
        return ['status_code' => $status_code, 'status_description' => $status_description];
    }
	
	public static function automaticBalanceTopUp($userObj, $currency_id, $deposit_source="")
	{
		$manageLog = new ManageLogging();
		
		$response = [
			'status' => ApiService::API_SERVICE_FAILED_CODE,
			'message' => __('Automatic Balance Top Up Fail'),
		];
		
		$log_data = [
			'action' => 'AUTOMATIC_BALANCE_TOP_UP_PROCESS_START',
			'user_id' => $userObj->id,
		];
		
		$manageLog->createLog($log_data);
		
		if(!empty($currency_id) && $userObj){
			
			
			$user_category = $userObj->user_category;
			$cashback_channel_id = (new CashbackChannel())->findChannelByCategory($user_category, [
                'is_default'=> CashbackChannel::IS_DEFAULT,
				'order_by' => 'id',
				'select_column' => [
					'id',
				]
			], 'first')?->id;
			

			$log_data['processed_user_cashback_channel_id'] = $cashback_channel_id;
			
			/*
			 * Process Cashback
			 */
			
			if(!empty($cashback_channel_id)){
				
				$cashBack = new CashbackService();
				
				$filter['channel_id'] = $cashback_channel_id;
				$cash_back_data = $cashBack->calculateCashbackAmount(
					$filter,
					0,
					$currency_id,
					CashbackEntity::TRANSACTION_TYPE_DEPOSIT,
                    $userObj->id
				);
				
				
				
				if(
					isset($cash_back_data['status']) &&
					$cash_back_data['status'] == ApiService::API_SERVICE_SUCCESS_CODE &&
					isset($cash_back_data['cashback_amount']) &&
					$cash_back_data['cashback_amount'] > 0
				){
					
					$currencyObj = (new Currency())->getCurrencyById($currency_id);
					
					list($cashback_status_code, $cashback_status_description, $prepare_data)  =
						$cashBack->prepareCashbackData(
							$currencyObj,
							$cash_back_data,
							$cash_back_data['cashback_amount']
						);
					
					/*
					 * BALANCE ADD
					 */

					list($status_code, $status_description, $data) = $cashBack->addcashback(
						$userObj->id,
						$prepare_data,
						null,
						CashbackEntity::TRANSACTION_TYPE_DEPOSIT,
						false,
					);

					
					if($status_code == ApiService::API_SERVICE_SUCCESS_CODE){
						
						$log_data['prepare_cash_back_data'] = $prepare_data;
						$log_data['response'] = $response = [
							'status' => ApiService::API_SERVICE_SUCCESS_CODE,
							'message' => __('Automatic Balance Top Up Success'),
						];
						
						
						/*
						 * SUCCESS EMAIL SHOULD BE SENT
						 */
						if(BrandConfiguration::call([BackendWallet::class,
								'isAllowSendEmailForAutomaticBalanceTopUp']) && $userObj->email)
						{
							
							$prepare_email_data = [
								'name' => !empty($userObj->name) ? $userObj->name : $userObj->first_name. ' '.$userObj->first_name,
								'success_message' => __('Dear').' ' .__(':amount :currency_code has been sent to your account.', [
										'amount' => @$prepare_data['cashback_amount'],
										'currency_code' => @$currencyObj->symbol,
									]),
								'email_content' => __("If your transaction didn't go through, please contact :brand_email to inform them about the issue.", [
									'brand_email' => config()->get('app.SUPPORT_EMAIL_ADDRESS')
								])
							];

                            $subject = 'cash_back_success';
                            $template = 'wallet_users_cash_back.cash_back_content';

                            if(!empty($deposit_source) && $deposit_source == Deposit::SOURCE_FINFLOW) {

                                $notificationAutomationData['email_data'] = $prepare_email_data;
                                $notificationAutomationData['email_template'] = $template;
                                $notificationAutomationData['subject'] = $subject;
                                $notificationAutomationData['language'] = app()->getLocale();
                                $notificationAutomationData['receiver_email'] = $userObj->email;
                                (new NotificationAutomation())->insertEntry($notificationAutomationData, true);

                            } else {
                                (new class { use SendEmailTrait; })->sendEmail(
                                    $prepare_email_data,
                                    $subject,
                                    config('app.SYSTEM_NO_REPLY_ADDRESS'),
                                    $userObj->email,
                                    '',
                                    $template,
                                    app()->getLocale(),
                                );

                                $log_data['email_send'] = 'Email sent method is called for. email is'.$userObj->email;
                            }
						}
						
					}
					
				}
			}
			
		}
		
		$log_data['action'] = 'AUTOMATIC_BALANCE_TOP_UP_PROCESS_END';
		$manageLog->createLog($log_data);

		return $response;
		
	}

    public static function setSalerLoginSession($saler_user_id)
    {
        $salerObj = (new User())->findById($saler_user_id);
        $sessionData = [
          'salerObj' => $salerObj
        ];

        Session::put($sessionData);
    }
	
	
	public static function isEnableCurrencyDropDownForDpl(): array
	{
		$is_enable_currency_dropdown = BrandConfiguration::call([FrontendWallet::class, 'isDisableDplCurrencyDropdown']);
		
		$disabled_btn = '';
		
		if($is_enable_currency_dropdown){
			$disabled_btn = 'disabled';
		}
		
		return [
			$is_enable_currency_dropdown,
			$disabled_btn
		];
	}
	
	public static function getDisabledWalletUserType(): array
	{
		return [
			User::ADMIN,
			User::SALES_ADMIN,
			User::SALES_EXPERT,
		];
	}

    public static function findUserGroupDataByUserId($user_id)
    {
        $data = null;

        $userUsergoup = new UserUsergroup();
        $userUsergoupObj = $userUsergoup->getByUserId($user_id);

        if ($userUsergoupObj->count() > 0) {
            $userGroup = new Usergroup();
            $usergroup_ids = $userUsergoupObj->pluck('usergroup_id')->toArray();
            $data = $userGroup->findByIds($usergroup_ids);
        }

        return $data;
    }

    public static function isAllowedMultiplePosPrograms():bool
    {
        return BrandConfiguration::call([Mix::class,'isAllowedMultiplePosPrograms']);
    }
	
	public function sendWalletUserWelcomeSms($phone): void
	{
		
		$message = __("Welcome to :brand_name! \nYour journey into fast, easy, and advantageous finance has begun. We're thrilled to see you join us. \n\nSee you soon,\n:brand_name", [
			'brand_name' => \common\integration\Utility\Str::titleCase(config()->get('brand.name'))
		]);

		$this->sendSMS('', $message, $phone);
	}
	
	public function updateUserCategory($request)
	{
		$status_code = $status_description = '';
		
		$response = [];
		
		$log_data['action'] = "USER_CATEGORY_UPDATE";
		
		try {
			$system_user_categories = BrandConfiguration::getUserCategoryList(
				\config('brand.name_code'),
				\config('constants.BRAND_NAME_CODE_LIST')
			);
			$validation_rules_message = AppRequestValidation::updateCategoryUpdateValidation(
				Arr::keys(
					$system_user_categories
				)
			);
			
			$validator = Validator::make(
				$request->all(),
				$validation_rules_message['rules'],
				$validation_rules_message['message']
			);
			
			if ($validator->fails()) {
				$status_code = ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR;
				$status_description = $validator->errors()->first();
			}

			if (empty($status_code) && auth()->check()) {
				
				(new Profile())->updateUserCategory(auth()->user()->id, $request->category_id);
				
				$response = [
					'inputs' => $request->all(),
					'system_user_categories'=> collect($system_user_categories)
						->map(function ($item){
							return __($item);
						}),
					'users' => auth()->user()->fresh()
				];
				
				$status_code = ApiService::API_SERVICE_SUCCESS_CODE;
				$status_description = __("User Category is successfully updated.");
				
			}
		} catch (\Throwable $e) {
			
			$log_data['has_exception'] = true;
			$log_data['exception_message'] = \common\integration\Utility\Exception::fullMessage($e, true);
			
		}
		
		$log_data['request_data'] = $request->all();
		$log_data['status_code'] = $status_code;
		$log_data['status_message'] = $status_description;
		(new ManageLogging())->createLog($log_data);
		
		
		return [$status_description, $response, $status_code];
	}

    public function InactiveAlertNotification($userObj,$loginAlertObj){
        $data = [];
        $lang = $this->getLang($userObj);
        $data['first_name'] = $userObj->first_name??'';
        $data['last_name'] = $userObj->last_name??'';
        $data['phone'] = $userObj->phone??'';
        $data['login_time'] = $userObj->login_at??'';
        $data['status'] = isset($loginAlertObj->status) ? $loginAlertObj->status : '';
        $to = empty($loginAlertObj->email_addresses) ? [] : Arr::map("trim", Arr::explode(',', $loginAlertObj->email_addresses));
        $phones = empty($loginAlertObj->sms_numbers) ? [] : Arr::map("trim", Arr::explode(',', $loginAlertObj->sms_numbers));
        $extra = ['receiver_emails'=>$to,'phone'=>$phones,'language' => $lang,'sys_lang'=>$lang];
        $loginAlertObj['language'] = $lang;
        $data['sms_data'] = $data;
        $data['email_data'] = $data;

        $this->checkAndSendNotification(
            UserSetting::action_lists['inactive_user_alert_notification'],
            $data,
            UserSetting::EMAILENABLED,
            UserSetting::SMSENABLED,
            UserSetting::PUSH_NOTIFICATION_DISABLED,
            $loginAlertObj,
            $extra
        );

        $businessAdminLogData['action'] = "USER_LOGIN_ALERT_RESPONSE";
        $businessAdminLogData['email_sent'] = $to;
        $businessAdminLogData['sms_sent'] = $phones;
        $businessAdminLogData['data'] = $loginAlertObj;
        (new ManageLogging())->createLog($businessAdminLogData);
    }
	
	public function sentUserEmail($request)
	{
		$status_code = '';
		$status_description = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_UNKNOWN_ERROR];
		
		$response = [];
		
		$log_data['action'] = "USER_API_EMAIL_SEND";
		
		try {

			$validation_rules_message = AppRequestValidation::sentApiEmailValidation($request);
			$validator = Validator::make(
				$request->all(),
				$validation_rules_message['rules'],
				$validation_rules_message['message']
			);
			
			if ($validator->fails()) {
				$status_code = ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR;
				$status_description = $validator->errors()->first();
			}
			
			if (empty($status_code)) {
				
				$data['contents'] = $request['email_body'];
				$this->sendEmail(
					$data,
					$request['subject'],
					config('app.SYSTEM_NO_REPLY_ADDRESS'),
					collect($request['recipient_addresses'])->implode(','),
					'',
					'automation',
					app()->getLocale(),
				);
				
				if(empty($this->exception_msg)){
					
					$status_code = ApiService::API_SERVICE_SUCCESS_CODE;
					$status_description = __("Email Send Successfully.");
				}
	
				
			}
		} catch (\Throwable $e) {
			
			$log_data['has_exception'] = true;
			$log_data['exception_message'] = \common\integration\Utility\Exception::fullMessage($e, true);
			
		}
		
		$log_data['request_data'] = $request->all();
		$log_data['status_code'] = $status_code;
		$log_data['status_message'] = $status_description;
		(new ManageLogging())->createLog($log_data);
		
		return [$status_description, $response, $status_code];
	}

    public function updateUserLoginAlertSetting($input, $authObj,$loginAlertObj)
    {
        $log_data['action'] = 'USER_LOGIN_ALERT_SETTINGS';
        $status_code = $status_description = '';
        try {

            $is_email = $is_sms = false;
            if (isset($input->is_notification_type_email) && $input->is_notification_type_email == 1) {
                $is_email = true;
            }
            if (isset($input->is_notification_type_sms) && $input->is_notification_type_sms == 1) {
                $is_sms = true;
            }
            $validation = Validator::make($input->all(), AppRequestValidation::userLoginAlertSettingRequestRules($is_email, $is_sms));
            if ($validation->fails()) {
                throw new \Exception($validation->errors()->first());
            }

            if ($is_email || $is_sms) {
                list($amc_response, $amc_message) = (new AdminMakerChecker())->processMakerChecker(
                    AdminMakerChecker::USER_LOGIN_ALERT_SETTINGS, AdminMakerChecker::ACTION_UPDATE, $input, $authObj
                );
                if (!$amc_response) {
                    $inputData = [
                        "user_type" => User::ADMIN,
                        "from_time" => $input->from_time,
                        "to_time" => $input->to_time,
                        "is_notification_type_sms" => $input->is_notification_type_sms ?? 0,
                        "is_notification_type_email" => $input->is_notification_type_email ?? 0,
                        "email_addresses" => $input->email_addresses ?? '',
                        "sms_numbers" => $input->sms_numbers ?? '',
                        "created_by_user_id" => $loginAlertObj->created_by_user_id ?? $authObj->id,
                        "updated_by_user_id" => $authObj->id,
                        "status" => $input->status
                    ];
                    $log_data['request_data'] = $inputData;
                    $loginAlert = new UserLoginAlertSetting();
                    $result = $loginAlert->insertOrUpdate($inputData, $input->id);
                    if ($result) {
                        if (BrandConfiguration::call([BackendAdmin::class, 'isAllowUserLoginAlertSettingsForBothStatus'])) {
                            if ($result->status == UserLoginAlertSetting::INACTIVE) {
                                (new GlobalUser())->InactiveAlertNotification($authObj, $result);
                            }
                        }
                        AdminMakerChecker::setCheckerSession($input, $authObj);
                        $status_description = 'New records has been saved successfully';
                        $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
                    } else {
                        throw new \Exception(__('The records can not be saved'));
                    }
                } else {
                    $status_code = $amc_message[1] == 'success' ? ApiService::API_SERVICE_SUCCESS_CODE : ApiService::API_SERVICE_FAILED_CODE;
                    $status_description = (__($amc_message[0]));
                }

            } else {

                throw new \Exception(__('Must have to select a notification option'));
            }

        } catch (\Throwable $th) {
            $log_data['exception'] = \common\integration\Utility\Exception::fullMessage($th);
            $status_code = ApiService::API_SERVICE_FAILED_CODE;
            $status_description = $th->getMessage();
        }

        $log_data['result'] = $status_code == ApiService::API_SERVICE_SUCCESS_CODE ? 'Success' : 'Failed';
        (new ManageLogging())->createLog($log_data);
        return [$status_code, $status_description];
    }

    public function removePasswordHistory($user){
        $changePasswordHistory = new ChangePasswordHistory();
        $changePasswordHistoryObj = $changePasswordHistory->getPasswordHistoryByUserId($user->id);
        if (!empty($changePasswordHistoryObj) && Arr::count($changePasswordHistoryObj) >= config('constants.PASSWORD_DENY_LAST_USED')) {
            $id = $changePasswordHistoryObj->take(2)->pluck('id') ?? [];
            if (!empty($id)) {
                $ids = $id->toArray() ?? [];
                if (!empty($ids)) {
                    $changePasswordHistory->deleteHistory($ids);
                }
            }
        }

    }
    
    public function updateUserSecurityImage(Request $request): array
    {
        $status_code = $status_description = '';
        $log_data["action"] = "UPDATE_USER_SECURITY_IMAGE";
        $log_data["request_data"] = $request->auth_user->id ?? 0;
        try {
            $validate_data = AppRequestValidation::validateUserSecurityImageInformation($request);

            if(!empty($validate_data['status_code'])){
                throw new \Exception($validate_data['status_message'], ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR);
            }
            $authUser = $request->auth_user;
            $authUser->security_image_id = $request->security_image;
            if ($authUser->save()){
                $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
                $status_description = "Security image update successfully";
            }else{
                throw new \Exception("Failed to update security image");
            }
        }catch(\Throwable $t){
            $log_data['error'] = $t->getMessage();
            $log_data['error_at'] = $t->getLine();
            $status_code = ApiService::API_SERVICE_FAILED_CODE;
            $status_description =  $t->getCode()==ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR ? $t->getMessage():"Something went wrong! please contact support.";
        }

        (new ManageLogging())->createLog($log_data);
        
        return [$status_code, $status_description];
    }
    
    public function getDefaultOtpChannel(): int
    {
        $otp_channel = self::OTP_CHANNEL_ALL;

        if(BrandConfiguration::call([BackendMix::class,'allowDefaultOtpChannelAsSMS'])){
            $otp_channel = self::OTP_CHANNEL_SMS;
        }

        return $otp_channel;
    }
}