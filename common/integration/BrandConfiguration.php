<?php


namespace common\integration;

use App\Models\AdminMakerChecker;
use App\Models\Currency;
use App\Models\DPL;
use App\Models\Merchant;
use App\Models\MerchantEmailReceiver;
use App\Models\Profile;
use App\User;
use App\Models\CCPayment;
use App\Models\MerchantReportHistory;
use App\Models\Statistics;
use common\integration\Brand\Configuration\Backend\BackendAdmin;
use common\integration\Brand\Configuration\Backend\BackendMix;
use common\integration\Brand\PaymentFeatureTrait;
use common\integration\Brand\ViewEntityFeatureTrait;
use common\integration\GlobalFunction;
use common\integration\Payment\Card;
use common\integration\Utility\Arr;
use Illuminate\Support\Facades\Route;
use App\Models\PaymentReceiveOption;
use App\Models\PaymentRecOption;
use common\integration\GlobalUser;
use Illuminate\Support\Carbon;
use App\Models\Pos;
use App\Models\PaidBill;
use App\Models\BlockTimeSettings;
use common\integration\Utility\Helper;
use Illuminate\Support\Str;

class BrandConfiguration
{
    use PaymentFeatureTrait, ViewEntityFeatureTrait;

    const PANEL_USER = 'user';
    const PANEL_MERCHANT = 'merchant';
    const PANEL_ADMIN = 'admin';
    const PANEL_CCPAYMENT = 'ccpayment';
    const PANEL_ALL = 'all';

   // Allowing brands For unblocking merchant user.
   public static function unblockMerchantUsers($brand_code)
   {

      $brands = [
        config('constants.BRAND_NAME_CODE_LIST.PB'),
        config('constants.BRAND_NAME_CODE_LIST.IM'),
	      config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];

      return in_array($brand_code, $brands);
   }

   // Brands For hiding Foreign/Debit card commission section in merchant panel commsission page.
   public static function hideForeignDebitCardBlock($brand_code){
      $brands = [
        config('constants.BRAND_NAME_CODE_LIST.PB'),
	      config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];

      return in_array($brand_code, $brands);
   }


   // Brands For FAQ page.
   public static function faqBrand($brand_code){
      $brands = [
        config('constants.BRAND_NAME_CODE_LIST.VP'),
        config('constants.BRAND_NAME_CODE_LIST.PN'),
        config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
        config('constants.BRAND_NAME_CODE_LIST.AP'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
          config('constants.BRAND_NAME_CODE_LIST.PB'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
	      config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];

      return in_array($brand_code, $brands);
   }

   // Allowing brands For send money b2u.
   public static function allowSendMoneyB2U($brand_code = null)
   {

      if(empty($brand_code)){
         $brand_code = config('brand.name_code');
      }

      $brands = [
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.FP'),
        config('constants.BRAND_NAME_CODE_LIST.AP'),
        config('constants.BRAND_NAME_CODE_LIST.DP'),
        config('constants.BRAND_NAME_CODE_LIST.PB'),
        config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
        config('constants.BRAND_NAME_CODE_LIST.SD'),
        config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];

      return in_array($brand_code, $brands);
   }

   // Allowing brands For showing specific card programs into merchant commission table list
   public static function allowBrandsHideSpecificCardPrograms($brand_code){
      $brands = [
        config('constants.BRAND_NAME_CODE_LIST.PB'),
	      config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];

      return in_array($brand_code, $brands);
   }

   public static function allowDplOnePageMultipleTimeUse($brand_code = ''){

      // Allowing for all brand
      return true;


      if(empty($brand_code)){
         $brand_code = config('brand.name_code');
      }

      $allow_list = [
         config('constants.BRAND_NAME_CODE_LIST.BP'),
         config('constants.BRAND_NAME_CODE_LIST.EP'),
         config('constants.BRAND_NAME_CODE_LIST.FP'),
         config('constants.BRAND_NAME_CODE_LIST.IM'),
         config('constants.BRAND_NAME_CODE_LIST.PB'),
         config('constants.BRAND_NAME_CODE_LIST.PN'),
         config('constants.BRAND_NAME_CODE_LIST.SP'),
         config('constants.BRAND_NAME_CODE_LIST.VP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.AP'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.MP'),
	      config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];
      return in_array($brand_code, $allow_list);
   }

   public static function QUESTION_LIST(){

      // $question_list = [
      //    1 => "What is your mother's maiden name?",
      //    2 => "What is your first pet's name?",
      //    3 => "What is your father's middle name?",
      //    4 => "What is your childhood nickname?",
      //    5 => "What is your favorite author's name?"
      // ];
      $question_list = Profile::QUESTION_LIST;


      $brand_code = config('brand.name_code');
      $allow_list = [
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.PB'),
         config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];

      if(in_array($brand_code, $allow_list)){
         $question_list[1] = "What is your favourite animal?";
      }

       $allow_list_index_three = [
           config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
           config('constants.BRAND_NAME_CODE_LIST.FL'),
           config('constants.BRAND_NAME_CODE_LIST.PP'),
           config('constants.BRAND_NAME_CODE_LIST.SR'),
           config('constants.BRAND_NAME_CODE_LIST.PB'),
           config('constants.BRAND_NAME_CODE_LIST.SD'),
           config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
       ];

       if(in_array($brand_code, $allow_list_index_three)){
           $question_list[3] = "What is your favorite plant?";
       }

      // if(config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.SR')
      //     || config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.PP')){
      //    $question_list[1] = "What is your favourite animal?";
      // }

      return  $question_list;
   }

   public static function allowDplBrandLogo(){
      $brand_code = config('brand.name_code');
      $allow_list = [
         config('constants.BRAND_NAME_CODE_LIST.FP'),
      ];

      return in_array($brand_code, $allow_list);

   }

   public static function AllowDplOption(){
      $brand_code = config('brand.name_code');
      $allow_list = [
         config('constants.BRAND_NAME_CODE_LIST.FP'),
      ];

      return in_array($brand_code, $allow_list);
   }

   public static function allowIpRestriction(){
      $brand_code = config('brand.name_code');
      $allow_list = [
         config('constants.BRAND_NAME_CODE_LIST.FP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
        config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
      ];

      return in_array($brand_code, $allow_list);
   }

   public static function allowMerchantRecientActivities(){
      $brand_code = config('brand.name_code');
      $allow_list = [
         config('constants.BRAND_NAME_CODE_LIST.PB'),
	      config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];

      return in_array($brand_code, $allow_list);
   }

    public static function restrictedDailyAvailablebalance(){
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];

        return in_array($brand_code, $allow_list);
    }

   const FORGET_PASSWORD = 'FORGET_PASSWORD_REDIRECTION';

   public static function allowLoginBlockTime(){
      $panel = config('constants.defines.PANEL');
      $login_block_time = false;
      $brand_code = config('brand.name_code');
      $allow_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
         config('constants.BRAND_NAME_CODE_LIST.PB'),
         config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
	      config('constants.BRAND_NAME_CODE_LIST.VP'),
	      config('constants.BRAND_NAME_CODE_LIST.FP'),
      ];

      return in_array($brand_code, $allow_list);

      // if(in_array($brand_code, $allow_list) && $brand_code == config('constants.BRAND_NAME_CODE_LIST.PB')){
      //    if(!empty($panel) && $panel == self::PANEL_USER){
      //       $login_block_time = true;
      //    }
      //
      //
      // }elseif (in_array($brand_code, $allow_list)){
      //    $login_block_time = true;
      // }
      //
      // return $login_block_time;
   }


    public static function socialMediaButtonsRemoved(){
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];

        return in_array($brand_code, $allow_list);
    }

   public static function logoutMerchantOnSessionTimeout(){
      $brand_code = config('brand.name_code');
      $allow_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];

      return in_array($brand_code, $allow_list);
   }

    // FOR LOCAL ENVIRONMENT IT IS DISABLED
   public static function newTabDisableWithRrightClick(){

       if(GlobalFunction::isLocalEnvironment()){
           return false;
       }

      $panel = config('constants.defines.PANEL');
      $disable_right_click = false;
      $brand_code = config('brand.name_code');
      $allow_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.PB'),
          config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
          config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
          config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];

      // if(in_array($brand_code, $allow_list) && $brand_code == config('constants.BRAND_NAME_CODE_LIST.PB')){
      //    if(!empty($panel) && $panel == self::PANEL_USER){
      //       $disable_right_click = true;
      //    }
      //
      //
      // }elseif (in_array($brand_code, $allow_list)){
      //    $disable_right_click = true;
      // }

       return in_array($brand_code, $allow_list);
   }

   public static function allowPendingUserLogin(){
      $brand_code = config('brand.name_code');
      $allow_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.PB'),
          config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];

      return in_array($brand_code, $allow_list);
   }

   public static function allowSessionTimeDynamic(){
      $panel = config('constants.defines.PANEL');
      $dynamic_session_time = false;
      $brand_code = config('brand.name_code');
      $allow_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.DP'),
        config('constants.BRAND_NAME_CODE_LIST.PB'),
        config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
	      config('constants.BRAND_NAME_CODE_LIST.VP'),
	      config('constants.BRAND_NAME_CODE_LIST.FP'),
      ];

      // if(in_array($brand_code, $allow_list) && $brand_code == config('constants.BRAND_NAME_CODE_LIST.PB')){
      //    if(!empty($panel) && ($panel == self::PANEL_USER || $panel == self::PANEL_ADMIN)){
      //       $dynamic_session_time = true;
      //    }
      //
      //
      // }elseif (in_array($brand_code, $allow_list)){
      //    $dynamic_session_time = true;
      // }

      return in_array($brand_code, $allow_list);
   }

   public static function getSessionUserType(){

       $panels = BlockTimeSettings::PANEL_LIST;
       $brand_code = config('brand.name_code');

       $is_enable_for_admin_merchant = [
           // config('constants.BRAND_NAME_CODE_LIST.PB'),
       ];

       if(in_array($brand_code, $is_enable_for_admin_merchant)){
           unset($panels[BlockTimeSettings::MERCHANT]);
       }

       return $panels;
   }

   public static function allowSecrectQuestionOnMerchantPanel(){
      $brand_code = config('brand.name_code');
      $allow_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.DP'),
        config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        config('constants.BRAND_NAME_CODE_LIST.VP'),
      ];

      return in_array($brand_code, $allow_list);
   }

   public static function AllowCustomerKycEditView (): bool
   {
       $brand_code = config('brand.name_code');
       $allow_list = [
           config('constants.BRAND_NAME_CODE_LIST.YP'),
           config('constants.BRAND_NAME_CODE_LIST.PC'),
           config('constants.BRAND_NAME_CODE_LIST.SD'),
           config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
           config('constants.BRAND_NAME_CODE_LIST.FL'),
           config('constants.BRAND_NAME_CODE_LIST.PP'),
           config('constants.BRAND_NAME_CODE_LIST.SR'),
       ];

	   $status = Arr::isAMemberOf($brand_code, $allow_list);

	   if(request()->route()->getName() == config()->get('constants.defines.APP_CURRENCIES_SETTINGS_SHOW')
		   && BackendAdmin::allowAllCurrencySettings()
		   && config('constants.defines.PANEL') == self::PANEL_ADMIN
	   ){
		   $status = false;
	   }

       return $status;
   }

   public static function getUserCategoryList($brands, $constants, $unset_categories = []){
      // $list = [
      //       '5' => 'All',
      //       '1' => 'Unkown',
      //       '2' => 'Unverified',
      //       '3' => 'Verified',
      //       '4'=>'Contracted'
      // ];

      $list = User::USER_CATEGORIES;

      $allow_list = self::isCustomizedUserCategory($brands, $constants);

      if(in_array($brands, $allow_list)){
         $list[User::NOT_VERIFIED] = 'Application';
         $list[User::VERIFIED] = 'KYC 1';
         $list[User::VERIFIED_PLUS] = 'KYC verified';
         unset($list[User::CONTRACTED]);
      }

      if (!empty($unset_categories)) {
          $list = array_except($list, $unset_categories);
      }

      return $list ;
   }

    public static function isCustomizedUserCategory ($brand, $brand_name_codes, $is_array_response = true)
    {
        $brand_list = [
            $brand_name_codes['YP'],
            $brand_name_codes['PP'],
            $brand_name_codes['SR'],
            $brand_name_codes['SD'],
            // $brand_name_codes['PL'],
        ];

        if ($is_array_response) {
            return $brand_list;
        } else {
            return Arr::isAMemberOf($brand, $brand_list);
        }
    }

    public static function isIgnoredLimitsUiForUnknownCategory ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isCustomedBladeForLimitsUi ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.DP'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);
    }

   public static function apiUserPanelBrandRestriction(){
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.SP'),
      ];

      return in_array($brand_code, $brand_list);
   }

   public static function allowShowMessageAdminPendinOrAdminNotApprove(){
      $brand_code = config('brand.name_code');
      $allow_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.DP'),
        config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];

      return in_array($brand_code, $allow_list);
   }


    public static function secretQuestionResetPassword(){
        $is_allow_secret_question = false;
        $panel = config('constants.defines.PANEL');
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
//            config('constants.BRAND_NAME_CODE_LIST.QP'),
//            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
	        config('constants.BRAND_NAME_CODE_LIST.PM'),
	        config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];

        $list_of_enable_only_for_admin_and_user_panel = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.PC')
        ];

        if (Arr::isAMemberOf($brand_code, $allow_list) &&
            Arr::isAMemberOf($brand_code, $list_of_enable_only_for_admin_and_user_panel)) {
            if (!empty($panel) && ($panel == self::PANEL_ADMIN || $panel == self::PANEL_USER)) {
                $is_allow_secret_question = true;
            }
        } else if (Arr::isAMemberOf($brand_code, $allow_list)) {
            $is_allow_secret_question = true;
        }

        return $is_allow_secret_question;
    }

    public static function transactionLimitExpireDateCheck(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];

        return in_array($brand_code, $brand_list);
    }

    public static function allowSecurityImage(){
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];

        return in_array($brand_code, $allow_list);
   }

    public static function transactionReceiptView()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowSecrectQuestionOnAdminPanel(){
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];

        return in_array($brand_code, $allow_list);
    }

   const EMAIL_VERIFIY_TOKEN = 'EMAIL_VERIFIY_TOKEN_';
   private const EMAIL_VERIFIY_TOKEN_EXPIRE = 5; // MINUTE

   public static function walletEmailVerified(){
      $brand_code = config('brand.name_code');
      $allow_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
         config('constants.BRAND_NAME_CODE_LIST.PB'),
         config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];

      return in_array($brand_code, $allow_list);
   }
    public static function allowPasswordPolicies(){
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];

        return in_array($brand_code, $allow_list);
    }

   public static function digitalContractBrandRestriction(){
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.SP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];

      return in_array($brand_code, $brand_list);
   }

   public static function showCitiesOption(){
      $brand_code = config('brand.name_code');
      $allow_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];

      return in_array($brand_code, $allow_list);
   }

   public static function bankAccountDeleteCheck(){
      $brand_code = config('brand.name_code');
      $allow_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
         config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];

      return in_array($brand_code, $allow_list);
   }

   public static function selectSendPfRecord(){
      $brand_code = config('brand.name_code');
      $allow_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];

      return in_array($brand_code, $allow_list);
   }

    public static function currencyByMerchantPosCommision(){
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];

        return in_array($brand_code, $allow_list);
    }

   public static function allow2D3DCvvLessFiltter(){
      $brand_code = config('brand.name_code');
      $allow_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.SP'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];

      return in_array($brand_code, $allow_list);
   }
   public static function formatD3DCvvLessFiltter($data_type = []){
      if(!Self::allow2D3DCvvLessFiltter() && empty($data_type)){
         return [];
      }

      $search = [
         'is_cvv_less' => CCPayment::IS_CVV_LESS_FALSE, // false
         'payment_source_2d_3d' => [],
      ];

      foreach($data_type as $key => $val){
         if($val == CCPayment::IS_CVVLESS){
            $search['is_cvv_less'] = CCPayment::IS_CVV_LESS_TRUE; // true
         }


         if($val == CCPayment::IS_2D){
            $search['payment_source_2d_3d'] = GlobalFunction::get2dPaymentSource();
         }

         if($val == CCPayment::IS_3D){

            $search['payment_source_2d_3d'] =   array_merge( $search['payment_source_2d_3d'],
               GlobalFunction::get3dPaymentSource()
            );
         }
      }

      return $search;
   }
    public static function allowLatitudeLongitude(){
        $brand_code = config('brand.name_code');
        $allow_list = [
//            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];

        return in_array($brand_code, $allow_list);
    }

    public static function receiptViewRedirectToNotNewTab()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allPrintButtonTransactionDetail()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function bankAccountInactiveCheck(){
      $brand_code = config('brand.name_code');
      $allow_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];

      return in_array($brand_code, $allow_list);
   }

   public static function allowMilesAndSmilesCardList(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.PB'),
	      config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function allowLanguageMerchantUserAdd(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
	        config('constants.BRAND_NAME_CODE_LIST.PM'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowMCCMerchantInformation(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function MCCList($is_only_key = true)
    {

        $mcc_list = [
            "4829" => "Para Transferi - İşyeri",
            "5046" => "Ticari Ekipman (Başka Yerde Sınıflandırılmayan)",
            "5169" => "Kimyasallar ve Benzer Ürünler (Başka Yerde Sınıflandırılmayan)",
            "5199" => "Dayanıksız Tüketim Malları (Başka Yerde Sınıflandırılmayan)",
            "5735" => "Plak ve CD Mağazaları",
            "5931" => "Kullanılmış Eşyalar ve İkinci El Dükkanları",
            "5933" => "Rehin Dükkanları",
            "5937" => "Antika Röprodüksiyonları",
            "5971" => "Sanat Eserleri Satıcıları ve Galeriler",
            "5972" => "Pul ve Madeni Para Dükkanları",
            "5973" => "Dini Malzemeler Dükkanları",
            "5978" => "Daktilo Dükkanları-Satış,Kiralama, Servis",
            "5994" => "Gazete Bayileri",
            "5997" => "Elektrikli Tıraş Makinesi Dükkanları - Satış ve Hizmet",
            "5998" => "Çadır ve Tente  Dükkanları",
            "5999" => "Muhtelif  ve Spesiyalite Perakende Satış Dükkanları",
            "6010" => "Finans Kuruluşları - Manuel Nakit Ödemeleri",
            "6011" => "Finans Kuruluşları - ATM Nakit Ödemeleri",
            "6012" => "Finans Kuruluşları - Mal ve Hizmetler",
            "6050" => "Nakit Muadili İşlemler - Üye Finansal Kuruluşu",
            "6051" => "Nakit Muadili İşlemler - İşyerleri",
            "6211" => "Borsa Komisyoncuları/ Aracıları",
            "6513" => "Emlakçılar  ve Yöneticiler - Kiralama",
            "6529" => "Uzaktan Değer Yükleme - Üye Finans Kuruluşu",
            "6530" => "Uzaktan Değer Yükleme - İşyeri",
            "6532" => "Ödeme Hizmet Sağlayıcısı - Üye Finansal Kuruluşu - Ödeme İşlemi",
            "6533" => "Ödeme Hizmet Sağlayıcısı - İşyeri - Ödeme İşlemi",
            "6534" => "Para Transferi  - Üye Finansal Kuruluşu",
            "6535" => "Değer Satışı - Üye Finansal Kuruluşu",
            "6536" => "MoneySend - Ülke İçi",
            "6537" => "MoneySend - Ülkeler Arası",
            "6538" => "MoneySend Fonlama",
            "6540" => "Kart Kabul Noktası(POI)  Fonlama İşlemleri - MoneySend Hariç",
            "7993" => "Video Eğlence Oyun Gereçleri",
            "9950" => "Şirket İçi Alımlar",
            "3351" => "Affiliated Auto Renalt",
            "3352" => "American Intl Rent-A-Car",
            "3353" => "Brooks Rent-A-Car",
            "3354" => "Action Auto Rental",
            "3355" => "Sixt Rent A Car",
            "3357" => "Hertz Auto Rental",
            "3359" => "Payless Rent A Car",
            "3360" => "Snappy Rent A Car",
            "3361" => "Airways Rent A Car",
            "3362" => "Altra Auto Rental",
            "3364" => "Agency Rent A Car",
            "3366" => "Budget Rent A Car",
            "3368" => "Holiday Rent A Car",
            "3370" => "Rent-A- Wreck",
            "3374" => "Accent Rent A Car",
            "3376" => "Ajax Rent A Car",
            "3380" => "Triangle Rent A Car",
            "3381" => "Europ Car",
            "3385" => "Tropical Rent A Car",
            "3386" => "Showcase Rental Cars",
            "3387" => "Alamo Rent A Car",
            "3388" => "Merchants Ren A Car",
            "3389" => "Avis Rent A Car",
            "3390" => "Dollar Rent A Car",
            "3391" => "Europe By Car",
            "3393" => "National Car Rental",
            "3394" => "Kemwell Groupe Rent A Car",
            "3395" => "Thrifty Car Rental",
            "3396" => "Tilden Rent A Car",
            "3398" => "Econo  Car Rent A Car",
            "3400" => "Auto Host Car Rentals",
            "3405" => "Enterprise Rent A Car",
            "3409" => "General Rent A Car",
            "3412" => "A-1 Rent A Car",
            "3414" => "Godfrey Natl Rent A Car",
            "3420" => "Ansa Intl Rent A Car",
            "3421" => "Allstate Rent A Car",
            "3423" => "Avcar Rent A Car",
            "3425" => "Automate Rent A Car",
            "3427" => "Avon Rent A Car",
            "3428" => "Carey Rent A Car",
            "3429" => "Insurance Rent A Car",
            "3430" => "Major Rent A Car",
            "3431" => "Replacement Rent A Car",
            "3432" => "Reserve Rent A Car",
            "3433" => "Ugly Duckling Rent A Car",
            "3434" => "Usa Rent A Car",
            "3435" => "Value Rent A Car",
            "3436" => "Autohansa Rent A Car",
            "3437" => "Cite Rent A Car",
            "3438" => "Interent Rent A Car",
            "3439" => "Milleville Rent A Car",
            "3441" => "Advantage Rent A  Car",
            "7512" => "Araç Kiralama  Acentesi",
            "7513" => "Nakliye Aracı ve Römork Kiralama",
            "4457" => "Tekne Kiralama",
            "4468" => "Marinalar, Marina Servisleri ve Araç Gereçleri",
            "5013" => "Motorlu  Araç Malzemeleri ve Yedek Parçalar",
            "5271" => "Karavan Satıcıları",
            "5511" => "Otomobil ve  Yük Aracı Bayileri  (Yeni ve İkinci El) Satış, Servis, Tamir, Parça ve Kiralama",
            "5521" => "Otomobil ve  Yük Aracı Bayileri  (Sadece İkinci El) Satış, Servis, Tamir,  Yedek Parça ve Kiralama",
            "5531" => "Ev ve Otomobil Gereçleri Mağazaları",
            "5532" => "Oto Lastik Bayileri",
            "5533" => "Oto Parça ve Aksesuar  Dükkanları",
            "5551" => "Tekne Bayileri",
            "5561" => "Karavan ve Römork Satıcıları",
            "5571" => "Motosiklet Dükkanları  ve Bayileri",
            "5592" => "Kamping  Karavanı ve Motorlu  Karavan Bayileri",
            "5598" => "Kar Motosikleti Bayileri",
            "5599" => "Çeşitli Otomotiv,Uçak ve Tarm Aletleri Satıcıları (Başka Bir Şekilde Sınıflandırılmamış)",
            "5935" => "Hurdalıklar",
            "5940" => "Bisiklet Dükkanları-  Satış ve Servis",
            "7519" => "Kamp Aracı, Motorlu  Karavan ve Eğlence Aracı Kiralama",
            "7523" => "Otomobil Park Yerleri ve Garajlar",
            "7531" => "Kaporta Tamir Atölyeleri",
            "7534" => "Lastik Kaplama ve Tamir Atölyeleri",
            "7535" => "Oto  Boyama Atölyeleri",
            "7538" => "Otomotiv Servis Dükkanları",
            "7542" => "Araç Yıkama",
            "7549" => "Araç Çekme Hizmetleri",
            "5172" => "Petrol ve Petrol  Ürünleri",
            "5541" => "Servis İstasyonları (Asistans-Yardım Servisi Olan veya Olmayan)",
            "5542" => "Yakıt Dolum Otomatları",
            "5552" => "Elektrikli Araç Şarj İstasyonu",
            "5983" => "Yakıt Satıcıları- Benzin, Odun, Kömür ve Sıvılaştırılmış Petrol",
            "3000" => "United Airlines",
            "3001" => "American Airlines",
            "3002" => "Pan American",
            "3003" => "EUROFLY",
            "3004" => "DRAGONAIR",
            "3005" => "British Airways",
            "3006" => "Japan Airlines",
            "3007" => "Air France",
            "3008" => "Lufthansa",
            "3009" => "Air Canada",
            "3010" => "KLM (Royal Dutch Airlines)",
            "3011" => "Aeroflot",
            "3012" => "Qantas",
            "3013" => "Alitalia",
            "3014" => "Saudi Arabian Airlines",
            "3015" => "Swissair",
            "3016" => "SAS",
            "3017" => "South African Airways",
            "3018" => "Varig (Brazil)",
            "3019" => "GRMNWGAIR",
            "3020" => "Air India",
            "3021" => "Air Algeria",
            "3022" => "Philippine Airlines (PAL)",
            "3023" => "Mexicana",
            "3024" => "Pakistan International Airlines",
            "3025" => "Air New Zealand",
            "3026" => "Emirates Airlines",
            "3027" => "UTA (UNION DE TRANSPORTS AERIENS)",
            "3028" => "Air Malta",
            "3029" => "Sabena Airlines",
            "3030" => "Aerolineas Argentinas",
            "3031" => "Olympic Airways",
            "3032" => "El Al",
            "3033" => "Ansett Airlines",
            "3034" => "ETIHAD AIRWAYS",
            "3035" => "TAP (Portugal)",
            "3036" => "VASP (Brazil)",
            "3037" => "EgyptAir",
            "3038" => "Kuwait Airways",
            "3039" => "Avianca",
            "3040" => "Gulf Air (Bahreyn)",
            "3041" => "Balkan - Bulgarian Airlines",
            "3042" => "Finnair",
            "3043" => "Aer Lingus",
            "3044" => "Air Lanka",
            "3045" => "Nigeria Airways",
            "3046" => "Cruzeiro Do Sul (brazil)",
            "3047" => "THY",
            "3048" => "Royal Air Maroc",
            "3049" => "Tunis Air",
            "3050" => "Icelandair",
            "3051" => "Austrian Airlines",
            "3052" => "Lanchile",
            "3053" => "Aviaco (Spain)",
            "3054" => "Ladeco (Chile)",
            "3055" => "Lab (Bolivio)",
            "3056" => "JET AIR",
            "3057" => "Virgin America",
            "3058" => "Delta",
            "3059" => "DBA AIR",
            "3060" => "Northwest",
            "3061" => "Continental",
            "3062" => "HLX",
            "3063" => "U.S. Airways",
            "3064" => "Adria Airways",
            "3065" => "Air Inter",
            "3066" => "Southwest",
            "3067" => "Vanguard Airlines",
            "3068" => "AIRSTANA",
            "3069" => "SunCountyAir",
            "3071" => "Air British Columbia",
            "3072" => "CEBU PACIFIC - CEBU PAC",
            "3075" => "Singapore Airlines",
            "3076" => "Aeromexico",
            "3077" => "Thai Airways",
            "3078" => "China Airlines",
            "3079" => "Jetstar Airways",
            "3082" => "Korean Airlines",
            "3083" => "Air Afrique",
            "3084" => "Eva Airways Corporation",
            "3085" => "Midwest Express Airlines",
            "3086" => "Carnival Airlines",
            "3087" => "Metro Airlines",
            "3088" => "Croatia Airlines",
            "3089" => "Transaero Airlines",
            "3090" => "Uni Airways",
            "3094" => "Zambia Airways",
            "3096" => "Air Zimbabwe",
            "3097" => "Spanair",
            "3098" => "Asiana",
            "3099" => "Cathay Airlines",
            "3100" => "Malaysian Airline System",
            "3102" => "Iberia",
            "3103" => "Garuda (Indonesia)",
            "3106" => "Braathens S.A.F.E.(Norway)",
            "3110" => "Wings Airways",
            "3111" => "British Midland",
            "3112" => "Windward Island",
            "3115" => "Tower Air",
            "3117" => "Viasa",
            "3118" => "Valley Airlines",
            "3125" => "Tan Airlines",
            "3126" => "Talair",
            "3127" => "Taca International",
            "3129" => "Surinam Airways",
            "3130" => "Sun World International",
            "3131" => "VLM Airlines",
            "3132" => "Frontier",
            "3133" => "Sunbelt Airlines",
            "3135" => "Sudan Airways",
            "3136" => "Qatar Airways",
            "3137" => "Sigleton",
            "3138" => "Simmons Airlines ",
            "3143" => "Scenic Airlines",
            "3144" => "Virgin Atlantic",
            "3145" => "San Juan Airlines",
            "3146" => "Luxair",
            "3148" => "AIR LITTORAL",
            "3151" => "Air Zaire",
            "3154" => "Princeville Air",
            "3156" => "GOFLY     ",
            "3159" => "Provincetown-Boston Airways",
            "3161" => "All Nippon Airways",
            "3164" => "Norontair",
            "3165" => "New York Helicopter",
            "3167" => "Aerocontinente",
            "3170" => "Mount Cook",
            "3171" => "Canadian Airlines ",
            "3172" => "Nation Air",
            "3174" => "Jetblue Airways",
            "3175" => "Middle East Air",
            "3176" => "Metroflight Airlines",
            "3177" => "Airtrans",
            "3178" => "Mesa Air",
            "3180" => "Westjet Airlines",
            "3181" => "Malev",
            "3182" => "Lot (Poland)",
            "3183" => "Oman Air",
            "3184" => "Liat",
            "3185" => "Lav (Venezuela)",
            "3186" => "Lap (Paraguay)",
            "3187" => "Lacsa (Costa Rika)",
            "3188" => "Virgin Express",
            "3190" => "Jugoslav Air",
            "3191" => "Island Airlines",
            "3193" => "Indian Airlines",
            "3196" => "Hawaiian Air",
            "3197" => "Havasu Airlines",
            "3200" => "Guyana Airways",
            "3203" => "Golden Pacific Air",
            "3204" => "Freedom Airlines",
            "3206" => "China East",
            "3211" => "Norwegian",
            "3212" => "Dominicana De Aviacion",
            "3213" => "Malmo Aviation",
            "3215" => "Dan Air Services",
            "3216" => "Cumberland Airlines",
            "3217" => "CSA Ceskoslovenske Aerolinie",
            "3218" => "Crown Air",
            "3219" => "COPA (Compania Panamena De Aviacion)",
            "3220" => "Compania Faucett",
            "3221" => "Transportes Aeros Militares Ecuatorianos",
            "3222" => "Command Airways",
            "3223" => "Comair",
            "3226" => "Skyways",
            "3228" => "Cayman Airways",
            "3229" => "Saeta Sociaedad Ecuatorianos DeTransporters Aeros",
            "3231" => "Sahsa Servicio Aereo De Honduras",
            "3233" => "Capitol Air",
            "3234" => "Caribbean",
            "3235" => "Brockway Air",
            "3236" => "Air Arabia",
            "3238" => "Bemidji Airlines",
            "3239" => "Bar Harbor Airlines",
            "3240" => "Bahamasair",
            "3241" => "Aviateca",
            "3242" => "Avensa",
            "3243" => "Austrian Air Service",
            "3245" => "Easy Jet",
            "3246" => "Ryan Air ",
            "3247" => "Gol Airlines",
            "3248" => "TAM Airlines",
            "3252" => "Alm Antilean Airlines",
            "3253" => "America West",
            "3254" => "US Air Shuttle",
            "3256" => "Alaska Airlines",
            "3260" => "Spirit Airlines",
            "3261" => "Air China",
            "3262" => "Reno Air,Inc",
            "3263" => "Aserca Aero Servico Carabobo",
            "3266" => "Air Seychelles",
            "3267" => "Air Panama",
            "3280" => "Air Jamaica",
            "3282" => "Air Djibouti",
            "3284" => "Aero Virgin Island",
            "3285" => "Aero Peru",
            "3286" => "Aerolineas Nicaraguenses",
            "3287" => "Aero Coach Aviation",
            "3292" => "Cypus Airways",
            "3293" => "Ecuatoriana",
            "3294" => "Ethiopian Airlines",
            "3295" => "Kenya Airways",
            "3296" => "AirBerlin",
            "3297" => "Tarom Romanian Air Transport",
            "3298" => "Air Mauritius",
            "3299" => "Wideroe's Flyveselskap",
            "4511" => "Havayolları  ve Hava Taşımacılığı (Başka Yerde Sınıflandırılmayan)",
            "4011" => "Demiryolları - Yük Taşımacılığı",
            "4111" => "Yerel ve Banliyö Yolcu Taşımacılığı - Feribotlar Dahil",
            "4112" => "Demiryolları - Yolcu Taşımacılığı",
            "4121" => "Taksiler ve Şoförlü  Limuzinler",
            "4131" => "Otobüs Hatları",
            "4214" => "Motorlu  Yük Taşıyıcıları ve Nakliyeciler - Yerel ve Uzun Mesafe Nakliye ve Depolama Şirketleri, Yerel Teslimat Hizmetleri",
            "4215" => "Kurye Hizmetleri  - Hava, Yer ve Yük Nakliyecileri",
            "4411" => "Seyahat Gemileri(Kruvaziyerler)",
            "4582" => "Hava Limanları, Uçuş Pistleri ve Havaalanı Terminal Binaları",
            "4722" => "Seyahat Acentaları ve Tur Operatörleri",
            "4784" => "Otoyol ve Köprü Ücretleri",
            "4789" => "Taşımacılık Hizmetleri  -Başka Yerde Sınıflandırılmayan",
            "3501" => "Holidey Inns",
            "3502" => "Best Western Hotels",
            "3503" => "Sheraton Hotels",
            "3504" => "Hilton Hotels",
            "3505" => "Forte Hotels",
            "3506" => "Golden Tulip Hotels",
            "3507" => "Friendship Inns",
            "3508" => "Quality Inns",
            "3509" => "Marriott",
            "3510" => "Day Inns",
            "3511" => "Arabella Hotels",
            "3512" => "Intercontinental Hotels",
            "3513" => "Westin",
            "3514" => "Amerisuites",
            "3515" => "Rodeways Inns",
            "3516" => "Laquinta Inns",
            "3517" => "Americana Hotels",
            "3518" => "Sol Hotels",
            "3519" => "Pullman Internationel Hotels",
            "3520" => "Meridien Hotels",
            "3521" => "Royal Lahaina Resort",
            "3522" => "Tokyo Hotel",
            "3523" => "Peninsula Hotel",
            "3524" => "Welcomgroup Hotels",
            "3525" => "Dunfey Hotels",
            "3526" => "Prince Hotels",
            "3527" => "Downtowner - Passport Hotel",
            "3528" => "Red Lion Inns",
            "3529" => "Canadian Pacific  Hotels",
            "3530" => "Renaissance Hotels",
            "3531" => "Kauai Coconut  Beach Resort",
            "3532" => "Royal Kona Resort",
            "3533" => "Hotels Ibis",
            "3534" => "Southern Pacific Hotel",
            "3535" => "Hilton Internationals",
            "3536" => "Amfac Hotels",
            "3537" => "Ana Hotels",
            "3538" => "Concorde Hotels",
            "3539" => "Summerfield Suites Hotels",
            "3540" => "Iberotel Giteks",
            "3541" => "Hotel Okura",
            "3542" => "Royal Hotels",
            "3543" => "Four Seasons Hotels",
            "3544" => "Ciga Hotels",
            "3545" => "Shangri - La International",
            "3546" => "Hotel Sierra",
            "3547" => "Breakers Resort",
            "3548" => "Hotels Melia",
            "3549" => "Auberge Des Governeures",
            "3550" => "Regal & Inns",
            "3551" => "Mirage Hotel Casino",
            "3552" => "Coast Hotels",
            "3553" => "Park Inns International",
            "3554" => "Pinehurst resort",
            "3555" => "Tressure Island Hotel and Casino",
            "3556" => "Barton Creek Resort",
            "3557" => "Manhattan East Suite Hotels",
            "3558" => "Jolly Hotels",
            "3559" => "Candlewood Suites",
            "3560" => "Aladdin Resort",
            "3561" => "Golden Nugget",
            "3562" => "Comfort Inns",
            "3563" => "Journey's End Motels",
            "3564" => "Sam's Town Hotels and Casino",
            "3565" => "Relax Inns",
            "3566" => "Garden Palace Hotels",
            "3567" => "Soho Grand Hotel",
            "3568" => "Ladbroke Hotels",
            "3569" => "Tribeca Grand Hotel",
            "3570" => "Forum Hotels",
            "3571" => "Grand Wailea Hotels",
            "3572" => "Miyako Hotels",
            "3573" => "Sandman Hotels",
            "3574" => "Venture Inn",
            "3575" => "Vagabond Hotels",
            "3576" => "La Quinta Resort",
            "3577" => "Mandarin Hotels",
            "3578" => "Frankenmuth Bavarian",
            "3579" => "Hotel Mercure",
            "3580" => "Hotel Del Coronado",
            "3581" => "Delta Hotels",
            "3582" => "California Hotel and Casino",
            "3583" => "RADISSON BLU",
            "3584" => "Princess Hotel International",
            "3585" => "Hungar Hotels",
            "3586" => "Sokos Hotels",
            "3587" => "Doral Hotels",
            "3588" => "Helmsley Hotels",
            "3589" => "Doral Golf Resort",
            "3590" => "Fairmont Hotels",
            "3591" => "Sonesta Hotels",
            "3592" => "Omni Hotels",
            "3593" => "Cunard Hotels",
            "3594" => "Arizona Biltmore",
            "3595" => "Hospitality Inns",
            "3596" => "Wynn Las Vegas",
            "3597" => "Riverside Resort",
            "3598" => "Regent International Hotels",
            "3599" => "Pannonia Hotels",
            "3600" => "Saddlebrook Resort",
            "3601" => "Tradewinds Resort",
            "3602" => "Hudson Hotel",
            "3603" => "Noah's Hotels",
            "3604" => "Hilton garden Inn",
            "3605" => "Jurys Dyle Hotel",
            "3606" => "Jefferson Hotel",
            "3607" => "Fontainebleau Resort",
            "3608" => "Gaylord Opryland",
            "3609" => "Gaylord Palms",
            "3610" => "Gaylord Texan",
            "3611" => "C Mon Inn",
            "3612" => "Moevenpick Hotels",
            "3613" => "Microtel Inns",
            "3614" => "AmericInn",
            "3615" => "Travelodge",
            "3616" => "Hermitage Hotels",
            "3617" => "America's Best Value Inn",
            "3618" => "Great Wolf",
            "3619" => "Aloft Hotels",
            "3620" => "Binion's Horseshoe Club",
            "3621" => "Extended Stay",
            "3622" => "Merlin Hotels",
            "3623" => "Dorint Hotels",
            "3624" => "Lady Luck Hotel and Casino",
            "3625" => "Hotel Universale",
            "3626" => "Stuido Plus",
            "3627" => "Extended Stay America",
            "3628" => "Excalibur Hotel and Casino",
            "3629" => "Dan Hotels",
            "3630" => "Extended Stay Deluxe",
            "3631" => "Sleep Inns",
            "3632" => "The Phoenician",
            "3633" => "Rank Hotels",
            "3634" => "Swissotel",
            "3635" => "Reso Hotel",
            "3636" => "Sarova Hotel",
            "3637" => "Ramada Hotel",
            "3638" => "Howard Johnson",
            "3639" => "Mount Charlotte Thistle",
            "3640" => "Hyatt Hotels",
            "3641" => "Sofitel Hotels",
            "3642" => "Novotel Hotels",
            "3643" => "Steigenberger Hotels",
            "3644" => "Econo Lodges",
            "3645" => "Queens Moat Houses",
            "3646" => "Swallow Hotels",
            "3647" => "Husa Hotels",
            "3648" => "De Vera Hotels",
            "3649" => "Radisson Hotels",
            "3650" => "Red Roof Inns",
            "3651" => "Imperial London Hotels",
            "3652" => "Embassy Hotels",
            "3653" => "Penta Hotels",
            "3654" => "Loews Hotels",
            "3655" => "Scandic Hotels",
            "3656" => "Sara Hotels",
            "3657" => "Oberoi Hotels",
            "3658" => "New Otani Hotels",
            "3659" => "Taj Hotels International",
            "3660" => "Knight's Inn",
            "3661" => "Metropole Hotels",
            "3662" => "Circus Circus Hotel and Casino",
            "3663" => "Hoteles El Presidente",
            "3664" => "Flag Inns",
            "3665" => "Hampton Inns",
            "3666" => "Stakis Hotels",
            "3667" => "Luxor Hotel and Casino",
            "3668" => "Maritim Hotels",
            "3669" => "Eldorado Hotel and Casino",
            "3670" => "Arcade Hotels",
            "3671" => "Arctia Hotels",
            "3672" => "Campanile Hotels",
            "3673" => "Ibusz Hotels",
            "3674" => "Rantasipi Hotels",
            "3675" => "Interhotel Cedok",
            "3676" => "Monte Carlo Hotel and Casino",
            "3677" => "Climat De France Hotels",
            "3678" => "Cumulus Hotels",
            "3679" => "Silver Legacy Hotel and Casino",
            "3680" => "Hoteis Othan",
            "3681" => "Adams Mark Hotels",
            "3682" => "Sahara Hotel and Casino",
            "3683" => "Bradbury Suites",
            "3684" => "Budget Hosts Inn",
            "3685" => "Budgetel Inns",
            "3686" => "Susse Chalet",
            "3687" => "Clarion Hotels",
            "3688" => "Compri Hotels",
            "3689" => "Consort Hotels",
            "3690" => "Courtyard By Marriott",
            "3691" => "Dillon Inns",
            "3692" => "Doubletree Hotels ",
            "3693" => "Drury Inns",
            "3694" => "Economy Inns of America",
            "3695" => "Embassy Suites",
            "3696" => "Excel Inns",
            "3697" => "Fairfield Hotels",
            "3698" => "Harley Hotels",
            "3699" => "Midway Motor Lodge",
            "3700" => "Motel 6",
            "3701" => "La Mansion del Rico",
            "3702" => "The Registry Hotels",
            "3703" => "Residence Inns",
            "3704" => "Royce Hotels",
            "3705" => "Sandman Inns",
            "3706" => "Shilo Inns",
            "3707" => "Shoney's Inns",
            "3708" => "Virgin River Hotel and Casino",
            "3709" => "Super 8 Motels",
            "3710" => "The Ritz - Carlton",
            "3711" => "Flag Inns (Australia)",
            "3712" => "Buffalo Bill's Hotel and Casino",
            "3713" => "Quality Pacific Hotel",
            "3714" => "Four Seasons Hotel (Australia)",
            "3715" => "Fairfield Inn",
            "3716" => "Carlton Hotels",
            "3717" => "City Lodge Hotel",
            "3718" => "Karos Hotel",
            "3719" => "Protea Hotels",
            "3720" => "Southern Sun Hotels",
            "3721" => "Conrad Hotels",
            "3722" => "Wyndham Hotels",
            "3723" => "Rica Hotels",
            "3724" => "Inter Nor Hotels",
            "3725" => "Sea Pines Resort",
            "3726" => "Rio Suites",
            "3727" => "Broadmoor Hotel",
            "3728" => "Bally's Hotel and Casino",
            "3729" => "John Ascuaga's Nugget",
            "3730" => "Mgm Grand Hotel",
            "3731" => "Harrah's Hotel and Casinos",
            "3732" => "Opryland Hotel",
            "3733" => "Boca Raton Resort",
            "3734" => "Harvey / Bristol Hotels",
            "3735" => "Masters Economy Inns",
            "3736" => "Colarado Belle / Edgewater Resort",
            "3737" => "Riviera Hotel and Casino",
            "3738" => "Tropicana Resort & Casino",
            "3739" => "Woodside Hotel & Resorts",
            "3740" => "Towneplace Suites",
            "3741" => "Milenium Hotels",
            "3742" => "Club Med",
            "3743" => "Biltmore Hotels & Suites",
            "3744" => "Carefree Resorts",
            "3745" => "St. Regis Hotel",
            "3746" => "Eliot Hotel",
            "3747" => "Club Corp / Club Resorts",
            "3748" => "Wellesley Inns",
            "3749" => "The Beverly Hotel",
            "3750" => "Crowne Plaza Hotel",
            "3751" => "Homewood Suites",
            "3752" => "Peabody Hotels",
            "3753" => "Greenbriar Resorts",
            "3754" => "Amelia İsland Platation",
            "3755" => "Homestead",
            "3757" => "Canyon Ranch",
            "3758" => "Khala Mandarin Oriental Hotel",
            "3759" => "The Orchid At Mauna Lani",
            "3760" => "Halekulani Hotel / Waikiki Parc",
            "3761" => "Primadonna Hotel and Casino",
            "3762" => "Whiskey Pete's Hotel and Casino",
            "3763" => "Chateau Elan Winer and Resort",
            "3764" => "Beau Rivage Hotel and Casino",
            "3765" => "Bellagio Hotel",
            "3766" => "Fremont Hotel and Casino",
            "3767" => "Main Street Station Hotel and Casino",
            "3768" => "Silver Star Hotel and Casino",
            "3769" => "Stratosphere Hotel and Casino",
            "3770" => "Springhill Suites",
            "3771" => "Ceasers Hotel",
            "3772" => "Nemacolin Woodlands",
            "3773" => "Venetian Resort Hotel",
            "3774" => "New York New York Hotel",
            "3775" => "Sands Resort",
            "3776" => "Nevele Grande Resort and Country Club",
            "3777" => "Mandalay Bay Resort",
            "3778" => "Four Points Hotels",
            "3779" => "W Hotels",
            "3780" => "Disney Resorts",
            "3781" => "Patricia Grand Resort Hotels",
            "3782" => "Rosen Hotels Resorts",
            "3783" => "Town and Country Club",
            "3784" => "First Hospitality Hotels",
            "3785" => "Outrigger Hotels",
            "3786" => "Ohana Hotels",
            "3787" => "Caribe Royal Resort",
            "3788" => "Ala Moana Hotel",
            "3789" => "Smugglers Notch Resort",
            "3790" => "Raffles Hotels",
            "3791" => "Staybridge Suites",
            "3792" => "Claridge Casino Hotel",
            "3793" => "The Flamingo Hotels",
            "3794" => "Grand Casino Hotels",
            "3795" => "Paris Las Vegas Hotels",
            "3796" => "Peppermill Hotel",
            "3797" => "Atlantic City Hilton",
            "3798" => "Embassy Vacation Resort",
            "3799" => "Hale Koa Hotel",
            "3800" => "Homestead Suites",
            "3801" => "Wilderness Hotel and Golf Resort",
            "3802" => "The Palace Hotel",
            "3803" => "Wigwam Golf Resort and Spa",
            "3804" => "The Diplomat Club Country Club and Spa",
            "3805" => "The Atlantic",
            "3806" => "Princeville Resort",
            "3807" => "Element",
            "3808" => "LXR",
            "3809" => "Settle Inn",
            "3810" => "La Costa Resort",
            "3811" => "Premier Inn",
            "3812" => "Hyatt Place",
            "3813" => "Hotel Indigo",
            "3814" => "The Roosevelt Hotel",
            "3815" => "Holiday Inn Nickelodeon",
            "3816" => "Home2Suites",
            "3817" => "Affinia",
            "3818" => "Mainstay Suites",
            "3819" => "Oxford Suites",
            "3820" => "Jumeirah Essex House",
            "3821" => "Caribe Royal",
            "3822" => "Crossland",
            "3823" => "Grand Sierra",
            "3824" => "Aria",
            "3825" => "Vdara",
            "3826" => "Autograph",
            "3827" => "Galt House",
            "3828" => "Cosmopolitan of Las Vegas",
            "3829" => "COUNTRY INN BY CARLSON",
            "3830" => "PARK PLAZA HOTEL",
            "3831" => "WALDORF",
            "7011" => "Konaklama - Oteller, Moteller, Tatil Yerleri (Başka Yerde Sınıflandırılmayan)",
            "7012" => "Devremülkler",
            "5813" => "İçkili Yerler (Alkollü İçkiler)- Barlar, Tavernalar, Gece Kulüpleri, Kokteyl  Salonları ve Diskotekler",
            "7800" => "Kullanım Dışı (Milli Piyango İşlemleri)",
            "7995" => "Kumarhane  Fişleri, Off-Track ve Hipodrom Bahisleri Dahil Bahisler",
            "8000" => "Milli Piyango ve Diğer Bahis İşlemleri",
            "9406" => "Milli Piyango İşlemleri",
            "5094" => "Saat Dükkanları",
            "5944" => "Kıymetli Taşlar ve Madenler, Mücevher ve Gümüş Takı Dükkanları",
            "0742"  => "Veterinerlik Hizmetleri",
            "4119" => "Ambulans Hizmetleri",
            "5047" => "Diş Hekimliği , Labaratuar , Tıbbi /,Hastane Teçhizat ve Gereçleri",
            "5122" => "İlaçlar, Patentli İlaçlar ve Ecza Ürünleri  -Ecza Depoları",
            "5912" => "Eczaneler",
            "5975" => "İşitme Cihazları-Servis, Satış ve Malzemeleri",
            "5976" => "Ortopedik Malzemeler-Protez Aletleri",
            "5977" => "Kozmetik  Dükkanları",
            "7299" => "Sınıflandırılmamış Kişisel Bakım Ürünleri",
            "8011" => "Hekimler  ve Doktorlar (Başka Yerde Sınıflandırılmayan",
            "8021" => "Diş hekimleri ve Ortodontistler",
            "8031" => "Osteopati Uzmanları",
            "8041" => "Hareket Sistemi Sağlığı Uzmanları",
            "8042" => "Optometristler ve Göz Doktorları",
            "8043" => "Gözlükçüler, Optik Ürünler ve Gözlükler",
            "8049" => "Ayak Sağlığı Uzmanları",
            "8050" => "Bakımevleri ve Kişisel  Bakım Kuruluşları",
            "8062" => "Hastaneler",
            "8071" => "Tıp ve Diş Laboratuarları",
            "8099" => "Tıbbi Hizmetler ve Pratisyenler (Başka Yerde Sınıflandırılmayan)",
            "5811" => "Catering Şirketleri",
            "5812" => "Yemek Yerleri ve Restoranlar",
            "5814" => "Fast Food Dükkanları",
            "5131" => "Parça Mallar, Tuhafiye ve Diğer Kuru Mallar",
            "5137" => "Erkek, Bayan ve Çocuk Üniformaları ve Ticari Giysiler",
            "5139" => "Ayakkabıcılar (Ticari)",
            "5611" => "Erkek ve Erkek Çocuk Giysi ve Aksesuar  Dükkanları",
            "5621" => "Bayan Hazır Giyim Dükkanları",
            "5631" => "Bayan Aksesuarları ve Özel Giyim Mağazaları",
            "5641" => "Çocuk ve Bebek Giyim Mağazaları",
            "5651" => "Aile Giyim Mağazaları",
            "5655" => "Spor ve Bincilik Kıyafetleri Mağazaları",
            "5661" => "Ayakkabı Mağazaları",
            "5681" => "Kürkçü ve Kürk Mağazaları",
            "5691" => "Erkek ve Kadın Giyim Mağazaları",
            "5697" => "Terziler",
            "5698" => "Peruk Dükkanları",
            "5699" => "Muhtelif  Giyim ve Aksesuar  Mağazaları",
            "5941" => "Spor Malzemeleri Mağazaları",
            "5948" => "Bavul ve Deri Eşya Dükkanları",
            "5949" => "Kumaş, Dikiş ve Nakış Malzemeleri ",
            "5300" => "Toptancı Mağazalar",
            "5309" => "Duty Free Mağazaları",
            "5310" => "İndirim  Mağazaları",
            "5311" => "Çok Reyonlu Mağazalar (Department  Stores)",
            "5331" => "İndirim  Dükkanları",
            "5399" => "Muhtelif  Genel Ürünler",
            "5411" => "Bakkallar   ve Süpermarketler",
            "5021" => "Büro  ve Ticari Mobilya",
            "5099" => "Dayanıklı Tüketim Malları (Başka Yerde Sınıflandırılmayan)",
            "5712" => "Mobilya,Ev Mefruşat  ve Gereçleri Mağazaları  - Elektrikli Aletler Dışında",
            "5713" => "Yer Kaplama Mağazaları",
            "5714" => "Kumaşçılık, Pencere Kaplama ve Döşemecilik Mağazaları",
            "5718" => "Şömine, Şömine Kafesi ve Aksesuarları Dükkanları",
            "5719" => "Muhtelif  Ev Mefruşat Eşyaları Spesiyalite Dükkanları",
            "5950" => "Cam ve Kristal Eşya Dükkanları",
            "4816" => "Bilgisayar Network ve Bilgi Servisleri",
            "5045" => "Bilgisayar, Bilgisayar Donanım ve Yazılım",
            "5065" => "Elektrikli Parçalar ve Ekipman",
            "5722" => "Elektrikli Ev Aletleri  Dükkanları",
            "5732" => "Çeşitli Elektrik, Elektronik ve Beyaz Eşya Dükkanları ve Yazarkasa Sağlayıcıları",
            "5734" => "Bilgisayar Yazılım Mağazaları",
            "5946" => "Kamera ve Fotoğraf  Gereçleri Dükkanları",
            "7379" => "Bilgisayar Bakım, Tamir ve Hizmetleri  -Başka Yerde Sınıflandırılmayan",
            "4812" => "Telekomünikasyon Cihazları ve Telefon Satışları",
            "4813" => "Telekomünikasyon Hizmetleri - Key Entry  Lokal ve Uzun Mesafeli Telefon Aramaları ",
            "4814" => "Telekomünikasyon Hizmetleri - Kredi Kartıyla Yapılan Şehir içi ve Şehirlerarası Telefon Görüşmeleri, Manyetik Şerit Kart Okuyuculu Telefonlar",
            "4820" => "Yenileme Merkezi",
            "4821" => "Telgraf Hizmetleri",
            "0763"  => "Tarım Kooperatifleri",
            "0780"  => "Peyzaj ve Bahçe Düzenleme Hizmetleri",
            "2741" => "Çeşitli Yayıncılık ve Matbaacılık",
            "2791" => "Dizgi, Klişe İmalatı ve İlgili Hizmetler",
            "2842" => "Özel Temizleme, Cilalama ve Hijyen Sağlama",
            "4225" => "Depolama - Tarım Ürünleri,   Dondurulmuş Ürünler, Ev Eşyaları  ve Depolama",
            "4899" => "Kablolu  ve Diğer Ödemeli Televizyon  Hizmetleri",
            "4900" => "Hizmetler ? Elektrik, Doğalgaz, Fuel Oil,Su, Temizlik",
            "5193" => "Çiçekçi Malzemeleri, Fidanlık Stokları ve Çiçekler",
            "5261" => "Fidanlıklar, Çim ve Bahçe Malzemeleri Dükkanları",
            "5733" => "Müzik Marketler-Müzik Aletleri, Piyanolar ve Notalar",
            "5815" => "Dijital Ürünler - Yayınlar, Kitaplar, Filmler ve Müzik",
            "5816" => "Dijital Ürünler - Oyunlar",
            "5817" => "Dijital Ürünler - Uygulamalar",
            "5818" => "Dijital Ürünler - Çoklu Kategori",
            "5932" => "Antika Dükkanları-Satış, Onarım ve Restorasyon Hizmetleri",
            "5992" => "Çiçekçiler",
            "5995" => "Evcil Hayvan Dükkanları,  Evcil Hayvan Yem ve Malzeme Dükkanları",
            "5996" => "Yüzme Havuzları - Satış ve Hizmet",
            "7032" => "Spor ve Dinlenme Kampları",
            "7033" => "Karavan Parkları ve Kamp Yerleri",
            "7210" => "Çamaşırhane, Temizlik  ve Giysi Hizmetleri",
            "7211" => "Çamaşırhaneler- Aile ve Ticari",
            "7216" => "Kuru Temizleyiciler",
            "7217" => "Halı ve Döşeme Temizleme",
            "7221" => "Fotoğraf  Stüdyoları",
            "7230" => "Güzellik ve Kuaför Salonları",
            "7251" => "Ayakkabı Tamir Dükkanları,  Lostra Salonları ve Şapka Temizleme Dükkanları",
            "7261" => "Cenaze Hizmetleri  ve Krematoryum",
            "7273" => "Randevu ve Eskort Hizmetleri",
            "7276" => "Vergi Beyannamesi Düzenleme Hizmetleri",
            "7277" => "Danışmanlık Hizmetleri - Borç, Evlilik  ve Şahsi",
            "7278" => "Alışveriş Klüpleri ve Servisleri ",
            "7296" => "Giysi Kiralama - Kostümler, Üniformalar, Resmi Giysi",
            "7297" => "Masaj Salonları",
            "7298" => "Sağlık ve Güzellik Banyoları  - Kaplıcalar",
            "7311" => "Reklamcılık  Hizmetleri",
            "7321" => "Müşteri Kredi Raporlama  Acenteleri",
            "7333" => "Ticari Fotoğraf, Sanat ve Grafikler",
            "7338" => "Hızlı Fotokopi, Çoğaltım ve Ozalit Hizmetleri",
            "7339" => "Stenografik ve Sekreter Yardımı",
            "7342" => "İlaçlama ve Dezenkfenkte  Hizmetleri",
            "7349" => "Temizlik, Bakım ve Bakıcı Hizmetleri",
            "7361" => "İş Bulma Büroları  ve Geçici İşçi Hizmetleri",
            "7372" => "Bilgisayar Programcılığı, Bilgi İşlem ve Entegre Sistem Tasarım Hizmetleri",
            "7375" => "Bilgi Edinme Servisleri",
            "7392" => "Yönetim, Danışmanlık  ve Halkla İlişkiler Hizmetleri",
            "7393" => "Dedektiflik ve Koruma Büroları, Zırhlı Araçlar, Güvenlik Hizmetleri ve Zırhlı Araçlar",
            "7394" => "Ekipman, Alet, Mobilya, Cihaz Kiralama  ve Leasing",
            "7395" => "Fotoğraf  Laboratuarları ve Fotoğraf  Banyosu",
            "7399" => "Diğer Ticari Faaliyetler  (Başka yerde Sınıflandırılmayan)",
            "7622" => "Elektronik Tamir Servisleri",
            "7623" => "Klima ve Buzdolabı  Tamir Servisleri",
            "7629" => "Elektrikli ve Küçük Aletler Tamir Servisleri",
            "7631" => "Kol Saati, Saat ve Mücevher Tamiri",
            "7641" => "Mobilya Döşeme ve Tamir",
            "7692" => "Kaynak Yapma Hizmetleri",
            "7699" => "Muhtelif  Tamir Servisleri ve İlgili Hizmetler",
            "7829" => "Film ve Video Kaset Prodüksiyonu ve Dağıtımı",
            "7832" => "Sinemalar",
            "7841" => "Video Kaset Kiralama Dükkanları",
            "7911" => "Dans Salonları, Stüdyolar ve Okullar",
            "7922" => "Tiyatro Prodüktörleri (Film Hariç) ve Bilet Acentaları",
            "7929" => "Orkestralar ve Muhtelif Eğlence Hizmetleri (Başka Şekillerde Sınıflandırılmamış)",
            "7932" => "Bilardo  Salonları",
            "7933" => "Bowling Merkezleri",
            "7941" => "Profesyonel Spor Kulüpleri, Spor Alanları ve Spor Etkinlikleri",
            "7991" => "Turistik Yerler ve Sergiler",
            "7992" => "Halka Açık Golf Sahaları",
            "7994" => "Video Oyunu ve Eğlence Merkezleri/Tesisleri",
            "7996" => "Eğlence Parkları, Sirkler, Karnavallar ve Falcılar",
            "7998" => "Akvaryumlar, Deniz Havuzları,Yunus Havuzları",
            "7999" => "Eğlence Hizmetleri  (Başka Yerde Sınıflandırılmayan",
            "8111" => "Hukuki İşlemler ve Avukatlar",
            "8351" => "Çocuk Bakım Hizmetleri",
            "8734" => "Test Laboratuarları (Tıbbi Olmayan Testler)",
            "8911" => "Mimarlık, Mühendislik ve Kadastro  Hizmetleri",
            "8931" => "Muhasebe, Denetim ve Defter Tutma Hizmetleri",
            "8999" => "Profesyonel Hizmetler (Başka Yerde Sınıflandırılmayan)",
            "5960" => "Doğrudan  Pazarlama- Sigorta Hizmetleri",
            "6300" => "Sigorta Satışı, Sigortalama ve Primler",
            "5039" => "İnşaat Malzemeleri (Başka Yerde Sınıflandırılmayan)",
            "5072" => "Hırdavat, Ekipman ve Malzemeler",
            "5074" => "Boru Tesisatı ve Isınma Teçhizatlerı Gereçleri",
            "5085" => "Endüstiriyel Gereçler  (Başka Yerde Sınıflandırılmayan)",
            "5198" => "Boya, Vernik ve Malzemeler",
            "5200" => "Ev Eşyaları Mağazaları",
            "5211" => "Kereste ve İnşaat Malzemeleri Dükkanları",
            "5231" => "Cam, Boya ve Duvar Kağıdı Mağazaları",
            "5251" => "Hırdavatçılar",
            "5962" => "Doğrudan  Pazarlama-Seyahatle  İlgili Hizmetler",
            "5963" => "Kapıdan Kapıya Satış",
            "5964" => "Doğrudan  Pazarlama-Katalog İşyerleri",
            "5965" => "Doğrudan  Pazarlama- Kombinasyon Katalog ve Perakende İşyerleri",
            "5966" => "Doğrudan  Pazarlama-Outbound Aramalarla  Pazarlama Yapan İşyerleri",
            "5967" => "Doğrudan  Pazarlama-Inbound Teleservices İşyerleri",
            "5968" => "Doğrudan  Pazarlama-Abonelik İşlemleri  Yapan İşyerleri",
            "5969" => "Doğrudan  Pazarlama Olmayan Mail Order/ Telefon Order Yöntemi İle Çalışan İşyeri",
            "5422" => "Et Marketleri ve Perakendeciler, Soğuk Depolama",
            "5441" => "Tatlılar, Çerezler ve Şekerleme Dükkanları",
            "5451" => "Süt Ürünleri Mağazaları",
            "5462" => "Fırınlar",
            "5499" => "Çeşitli Yiyecek Dükkanları---Çok Amaçlı Dükkanlar  ve Spesiyalite Marketleri",
            "5921" => "Ambalajlı  Ürün Dükkanları  - Bira, Şarap ve Alkollü İçecekler",
            "5993" => "Sigara Dükkanları  ve Büfeleri",
            "7997" => "Üyelik Kulüpleri (Spor, Eğlence, Jimnastik), Özel Golf Sahaları",
            "8398" => "Hayır Kurumları ve Sosyal Hizmet Kurumları",
            "8641" => "Sosyal Dernekler ve Koruma Dernekleri",
            "8651" => "Politik Organizasyonlar",
            "8661" => "Dini Organizasyonlar",
            "8675" => "Otomobil Dernekleri",
            "8699" => "Üyelik Organizasyonları - Başka Yerde Sınıflandırılmayan",
            "5044" => "Fotoğrafçılık, Fotokopi, Mikrofilm Donanımı ve Malzemeleri",
            "5111" => "Ofis Malzemeleri, Baskı ve Yazı Kağıdı",
            "5192" => "Kitaplar,  Dergiler ve Gazeteler",
            "5942" => "Kitapçılar",
            "5943" => "Kırtasiyeler ve Okul Gereçleri Dükkanları",
            "5945" => "Hobi, Oyuncak ve Oyun Dükkanları",
            "5947" => "Hediyelik  Eşya, Kartpostal, Hatıra Eşyası Satan Dükkanlar",
            "5970" => "Sanatçı Gereçleri ve Elişleri  Dükkanları",
            "8211" => "İlk ve Orta Dereceli Okullar",
            "8220" => "Kolejler,  Üniversiteler, Meslek Okulları ve Yüksek Okullar",
            "8241" => "Uzaktan Eğitim Okulları",
            "8244" => "İş ve Sekreterlik Okulları",
            "8249" => "Meslek ve Ticaret Okulları",
            "8299" => "Okullar ve Eğitim Hizmetleri  (Başka Yerde Sınıflandırılmayan)",
            "1520" => "Müteahhitler - Konut ve Ticari",
            "1711" => "Isıtma, Tesisat ve Klima Müteahhitleri",
            "1731" => "Elektrik  İşleri Müteahhitleri",
            "1740" => "Duvar Örme, Taş İşleri, Karo Döşeme, Sıva ve İzolasyon  İşleri Müteahhitleri",
            "1750" => "Marangozluk İşleri Müteahhitleri",
            "1761" => "Çatı, Cephe Kaplama ve Metal Plaka İşleri Müteahhitliği",
            "1771" => "Beton İşleri Müteahhitleri",
            "1799" => "Özel Ticari Müteahhitler (Başka Yerde Sınıflandırılmayan)",
            "5051" => "Metal Ürün Toptan Satıcıları",
            "9211" => "Mahkeme Masrafları - Nafaka ve Çocuk Yardımı",
            "9222" => "Para Cezaları",
            "9223" => "Kefalet ve Senet Ödemeleri",
            "9311" => "Vergi Ödemeleri",
            "9399" => "Kamu  Hizmetleri  (Başka Yerde Sınıflandırılmayan)",
            "9402" => "Posta Hizmetleri  - Sadece Devlet Yönetimindeki",
            "9405" => "Noter İşlemleri",
            "6363" => "Emeklilik ve Hayat Şirketleri",
            "0744" => "CHAMPAGNE PRODUCERS",
            "0743" => "WINE PRODUCERS"
        ];
        if($is_only_key){
            return array_keys($mcc_list);
        }
        return $mcc_list;
    }

    public static function companyContactPersonEMail(){
        $brand_code = config('brand.name_code');

        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];

        $message = '';
        if(in_array($brand_code, $allow_list)){
            $message =  __(':company Contact Person E-Mail', ['company' => config('brand.name')]);
        }else{
            $message = __(':company Accountant Email', ['company' => config('brand.name')]);
        }
        return $message;
    }

   public static function allowSixDigitOnLoginPage(){
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.AP'),
        config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function allowTaxiMerchant(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowFastPayWalletMerchant(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function allowSecurityImageInAdminPanel(){
      $brand_code = config('brand.name_code');
      $allow_list = [
        config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.VP'),
      ];

      return in_array($brand_code, $allow_list);
   }
    public static function kycAgeLimitCheck(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function allowVKNunique(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         // config('constants.BRAND_NAME_CODE_LIST.FP'),
         // config('constants.BRAND_NAME_CODE_LIST.YP'),
         // config('constants.BRAND_NAME_CODE_LIST.PC'),
    /*     config('constants.BRAND_NAME_CODE_LIST.PP'),*/
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
          //config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }

   public static function showSecurityImage(){
      $brand_code = config('brand.name_code');
      $allow_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.DP'),
        config('constants.BRAND_NAME_CODE_LIST.PB'),
        config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        config('constants.BRAND_NAME_CODE_LIST.VP'),
      ];

      return in_array($brand_code, $allow_list);
   }

   public static function allowAccessControlExport(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.FP'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.PB'),
          config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }
    public static function allowPayBillSubModule(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowLoginSessions(){
        $panel = config('constants.defines.PANEL');
        $allow_login_sessions = false;

        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
	        config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];

        if(in_array($brand_code, $allow_list) && $brand_code == config('constants.BRAND_NAME_CODE_LIST.PB')){
            if(!empty($panel) && $panel == self::PANEL_ADMIN){
                $allow_login_sessions = true;
            }
        }else if (in_array($brand_code, $allow_list)){
            $allow_login_sessions = true;
        }

        return $allow_login_sessions;
    }


   public static function allowFinancialTransactionWithPassword(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         //config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function allowFinancialTransactionWithPassApi(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function emailContentChanges(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         // config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];
      return in_array($brand_code, $brand_list);
   }


   /**
    * brand and module wise admin maker checker condition
    */

   public static function allowedAdminMakerCheckerBrand()
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
//         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
         config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
         // config('constants.BRAND_NAME_CODE_LIST.PB'),
         config('constants.BRAND_NAME_CODE_LIST.SD'),
         config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        config('constants.BRAND_NAME_CODE_LIST.FP'),
      ];

      return in_array($brand_code, $brand_list);
   }

    public static function allowSelectedMakerCheckerOperation (): array
    {
        $brand_code = config('brand.name_code');
        $route_list = [];

        if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.FP')) {
            $route_list = [
                AdminMakerChecker::MERCHANT_APPLICATION_CONTROL_STATUS,
                AdminMakerChecker::MERCHANT_PACKAGE_CONTROL_STATUS,
                AdminMakerChecker::MERCHANT_UPDATE_INFORMATION,
                AdminMakerChecker::MERCHANT_INFORMATION,
                AdminMakerChecker::SHOW_HAS_KEY_PASSWORD,
                AdminMakerChecker::SHOW_CLIENT_ID_CLIENT_SECRET,
//                AdminMakerChecker::MERCHANT_APPLICATION_REJECT,
//                AdminMakerChecker::MERCHANT_APPLICATION_APPROVE
            ];
        }

        return $route_list;
    }

   public static function getAdminMakerCheckerMethodAndRoute($section_name, $action_name, $request)
   {
      $response = false;
      $method = '';
      $route = '';
      $get_params = [];

      $route_and_method_list = [
         AdminMakerChecker::ANNOUNCEMENTS => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::WALLET_ANNOUNCEMENTS => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::HOLIDAY_SETTINGS => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_INFORMATION => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_PAYMENT_SETUP => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_WITHDRAWAL => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_AUTOMATIC_WITHDRAWAL => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_DIPOSIT => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_B2B => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_B2B_REFUND => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_OTPL => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_IP_ASSIGNMENT => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_BILLING_INFO => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_POS_PF_SETTING => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_BANK_ACCOUNT => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_ROLLING_RESERVE => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_API_SETTING => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_WEB_HOOK => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_COMMISSION_SETTINGS => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_TRANSACTION_LIMIT => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_CHARGEBACK_FORCE => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::POS_SETTING_ASSIGN => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_POS_EDIT => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_POS_PROGRAM_EDIT => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_POS_ADVANCE_COMMISSION => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_DOCUMENT_STATUS => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_DOCUMENT_UPLOAD => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::TICKET_CLOSE => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::AMLS => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::BANKS => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::CURRENCY_EXCHANGE_RATE => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::CURRENCIES => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::CURRENCIES_SETTINGS => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::FRAUD_RULE => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::VPOS_MONITORING_REJEECT => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],

          AdminMakerChecker::VPOS_MONITORING_APPROVE => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],

          AdminMakerChecker::WALLET => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::USER_BANK => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],

          AdminMakerChecker::USER_GSM_CHANGE => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::AWAITING_REFUNDS => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],

          AdminMakerChecker::USER_EMAIL_CHANGE => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],

          AdminMakerChecker::USER_STATUS_CHANGE => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::SECTOR => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::MERCHANT_SETTLEMENT => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],

          AdminMakerChecker::SIPAY_BANK => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],

          AdminMakerChecker::REASON => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],

          AdminMakerChecker::WITHDRAWAL_COT => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::SERVICE_TYPE => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::WITHDRAWAL_COMMISSION => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::POS => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::TMP_POS => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::CAMPAIGN => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::ALLOCATION => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::CUSTOMER_RESEND_ACTIVATION => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::CUSTOMER_CHANGE_EMAIL_LINK => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::CUSTOMER_CHANGE_PHONE_LINK => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::CUSTOMER => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::VPOS_MONITORING_COMMENT => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::POS_ASSIGN => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::CARD_BLACKLIST => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => false, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::SINGLE_PAYMENT_COMMISSION_SETUP => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::MAIL_RECEIVERS => [
              AdminMakerChecker::ACTION_CREATE => false, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => false
          ],

          AdminMakerChecker::ACCESS_CONTROL_USER_MANAGEMENT => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],

          AdminMakerChecker::ACCESS_CONTROL_ROLE_MANAGEMENT => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::ACCESS_CONTROL_USER_GROUP => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::ACCESS_CONTROL_USER_GROUP_AND_ROLE_ASSO => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::ACCESS_CONTROL_ROLE_AND_PAGE_ASSO => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],

          AdminMakerChecker::ACCESS_CONTROL_ADMIN_MANAGEMENT => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],

          AdminMakerChecker::ACCESS_CONTROL_ADMIN_ROLE_MANAGEMENT => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::ACCESS_CONTROL_ADMIN_USER_GROUP => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::ACCESS_CONTROL_ADMIN_USER_GROUP_AND_ROLE_ASSO => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::ACCESS_CONTROL_ADMIN_ROLE_AND_PAGE_ASSO => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::MERCHANT_CHARGEBACK_APPROVE => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::MERCHANT_CHARGEBACK_REJECT => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::MERCHANT_APPLICATION_APPROVE => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::MERCHANT_APPLICATION_REJECT => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::TRANSACTION_REFUND => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
         AdminMakerChecker::MERCHANT_TCKN_VKN_BLACKLIST => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::MERCHANT_B2C => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
         AdminMakerChecker::CHECKLIST_CONTROL => [
          AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
         ],
          AdminMakerChecker::SESSION_TIME_SETTINGS => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::POS_REDIRECTION => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::AML_RULE => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::SITE_SETTINGS => [
              AdminMakerChecker::ACTION_CREATE => false, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => false
          ],
          AdminMakerChecker::AWAITING_BANK_ACCOUNT => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => false
          ],
          AdminMakerChecker::TICKET_CATEGORIES => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::WITHDRAWAL_BANKS => [
            AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::NOTIFICATION_SETTINGS => [
              AdminMakerChecker::ACTION_CREATE => false, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => false
          ],
          AdminMakerChecker::BLOCKING_TIME_SETTING => [
              AdminMakerChecker::ACTION_CREATE => false, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => false
          ],
          AdminMakerChecker::MERCHANT_AGREEMENT => [
              AdminMakerChecker::ACTION_CREATE => false, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => false
          ],
          AdminMakerChecker::ONBOARDING_API => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::USER_LOGIN_ALERT_SETTINGS => [
              AdminMakerChecker::ACTION_CREATE => false, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => false
          ],
	      AdminMakerChecker::USER_SIM_BLOCK => [
		      AdminMakerChecker::ACTION_CREATE => false, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => false
	      ],
          AdminMakerChecker::MERCHANT_APPLICATION_CONTROL_STATUS => [
              AdminMakerChecker::ACTION_CREATE => false, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => false
          ],
          AdminMakerChecker::MERCHANT_PACKAGE_CONTROL_STATUS => [
              AdminMakerChecker::ACTION_CREATE => false, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => false
          ],
          AdminMakerChecker::MERCHANT_UPDATE_INFORMATION => [
              AdminMakerChecker::ACTION_CREATE => false, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => false
          ],
          AdminMakerChecker::DEPOSIT_METHOD => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::BLOCK_CC => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => false, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::STATISTICS => [
              AdminMakerChecker::ACTION_CREATE => false, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => false
          ],
          AdminMakerChecker::PROVIDER_ERROR_CODE => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => false, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::INTEGRATORS => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => false, AdminMakerChecker::ACTION_DELETE => false
          ],
          AdminMakerChecker::BLOCK_IP => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => false, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::KYC_VERIFICATION_SERVICE => [
              AdminMakerChecker::ACTION_CREATE => false, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => false
          ],
          AdminMakerChecker::WITHDRAWAL_METHOD => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => false, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::BLOCK_CC_BIN => [
              AdminMakerChecker::ACTION_CREATE => true, AdminMakerChecker::ACTION_UPDATE => false, AdminMakerChecker::ACTION_DELETE => true
          ],
          AdminMakerChecker::SHOW_HAS_KEY_PASSWORD => [
              AdminMakerChecker::ACTION_CREATE => false, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => false
          ],
          AdminMakerChecker::SHOW_CLIENT_ID_CLIENT_SECRET => [
              AdminMakerChecker::ACTION_CREATE => false, AdminMakerChecker::ACTION_UPDATE => true, AdminMakerChecker::ACTION_DELETE => false
          ]
      ];
       $fixed_routes = self::allowSelectedMakerCheckerOperation();


       if (!empty($fixed_routes)) {
           $route_and_method_list = array_filter($route_and_method_list, function ($k) use ($fixed_routes) {
               return Arr::isAMemberOf($k, $fixed_routes);
           }, ARRAY_FILTER_USE_KEY);
       }
      if (isset($route_and_method_list[$section_name][$action_name]) && $route_and_method_list[$section_name][$action_name]) {
         $response = true;
         $method = $request->method();
         $route = Route::current()->action['controller'];
         $parameters = Route::current()->parameters;
         $parameterNames = Route::current()->parameterNames;
         $get_params = [];

         foreach ($parameterNames as $key=>$value) {
            $get_params[$value] = $parameters[$value] ?? '';
         }
      }

      return [$response, $method, $route, $get_params];
   }

    public static function allowMakerForActiveMerchant (): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }


   const MAX_OTP_ATTEMPT = 5;
   const WRONG_OTP_TIMER = 1440; // 1 Day
   const INFO_NOT_MATCHED = "Information Not Matched Please Try Again";
   const REQUIRED_FIELD_ALL_FIELDS = "Please fill all fields";
   const WRONG_OTP_ATTEMPT_HEADER = "Security Question & Password";
   const WRONG_OTP_ATTEMPT_PREFIX = 'WRONG_OTP_COUNTER_';


   public static function allowWrongOTPAttempt(){
       $allow_login_sessions = false;
       $panel = config('constants.defines.PANEL');
       $brand_code = config('brand.name_code');
       $allow_list = [
           config('constants.BRAND_NAME_CODE_LIST.YP'),
           config('constants.BRAND_NAME_CODE_LIST.PC'),
           config('constants.BRAND_NAME_CODE_LIST.FL'),
           config('constants.BRAND_NAME_CODE_LIST.PP'),
           config('constants.BRAND_NAME_CODE_LIST.SR'),
           config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
           config('constants.BRAND_NAME_CODE_LIST.DP'),
           config('constants.BRAND_NAME_CODE_LIST.PB'),
           config('constants.BRAND_NAME_CODE_LIST.SD'),
           config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
       ];
       if (in_array($brand_code, $allow_list) && $brand_code == config('constants.BRAND_NAME_CODE_LIST.PB')) {
           if (!empty($panel) && ($panel == self::PANEL_ADMIN || $panel == self::PANEL_USER)) {
               $allow_login_sessions = true;
           }
       } else if (in_array($brand_code, $allow_list)) {
           $allow_login_sessions = true;
       }
       return $allow_login_sessions;
   }

   public static function notAllowWalletService ():bool
   {
       $brand_code = config('brand.name_code');
       $brand_list = [
           config('constants.BRAND_NAME_CODE_LIST.SP'),
           config('constants.BRAND_NAME_CODE_LIST.YP'),
           config('constants.BRAND_NAME_CODE_LIST.PC'),
           config('constants.BRAND_NAME_CODE_LIST.HP'),
           config('constants.BRAND_NAME_CODE_LIST.MOP'),
           config('constants.BRAND_NAME_CODE_LIST.QP'),
           config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
           // config('constants.BRAND_NAME_CODE_LIST.PM'),
           // config('constants.BRAND_NAME_CODE_LIST.PL'),
       ];
       return Arr::isAMemberOf($brand_code, $brand_list);
   }

   public static function vknAndTaxNumberSame(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }

   public static function getSurnameLength(){
      $brand_code = config('brand.name_code');
      $lenght = 30;

      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.FP'),
      ];

      if(in_array($brand_code, $brand_list)){
         $lenght = 90;
      }

      return $lenght;
   }
   // Here, we validate wallet user max receive limit and max receive transaction limit.
   public static function allowCustomerMaxLimit(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }
   // show invoice_id and payment_source in all transaction
    public static function allowPaymentSource(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function merchnatCreationFromApplication(){
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
          config('constants.BRAND_NAME_CODE_LIST.FP'),
      ];
      return in_array($brand_code, $brand_list);

   }

   /*
    * merchnatCreationFromApplication() method is to check if allowed
    * merchantApplicationCustomizedParams() method is to manipulate few input params
    */
    public static function isMerchantApplicationCustomizedWay (): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP')
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function hideTaxNumber(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
         config('constants.BRAND_NAME_CODE_LIST.PB'),
         config('constants.BRAND_NAME_CODE_LIST.PM'),
          config('constants.BRAND_NAME_CODE_LIST.FP')
      ];
      return in_array($brand_code, $brand_list);
   }

   public static function allowRolePageExport(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.FP'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
         config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }
    // For log write check
    public static function allowLogChecker()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
         config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
	        config('constants.BRAND_NAME_CODE_LIST.PB'),
        ];

        return in_array($brand_code, $brand_list);
    }


   public static function allowMonthlyDepositLimit()
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.DP'),
        config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];

      return in_array($brand_code, $brand_list);
   }

    public static function allowActivationCodeService(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array(
            $brand_code,
            $brand_list
        );
    }

    public static function sessionUserMails(){
        $mails = '';
        if(self::allowLoginSessions()){
            $statistics = (new Statistics())->findById(1,'session_user_email');
            $mails = $statistics->session_user_email;
        }
        return $mails;
    }
   // From admin payment setting b2c wallet commission setting shoudl be hide
   public static function hideB2CWalletFromAdminPanel(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.FP'),
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];

      return in_array($brand_code, $brand_list);
   }

   // check the user is locked if s/he is locked will change is status as active
   public static function isUserLocked($userObj = null){

      if(Self::allowLoginBlockTime() && !empty($userObj) && $userObj->is_admin_verified == Profile::LOCK_USER){
         return true;
      }


      return false;
   }

   // when the user change his password by using forget password mail and form panel change password
   public static function allowChangePasswordMail(){
       $allow_login_sessions = false;
       $panel = config('constants.defines.PANEL');
       $brand_code = config('brand.name_code');
       $allow_list = [
           config('constants.BRAND_NAME_CODE_LIST.YP'),
           config('constants.BRAND_NAME_CODE_LIST.PC'),
           config('constants.BRAND_NAME_CODE_LIST.FL'),
           config('constants.BRAND_NAME_CODE_LIST.PP'),
           config('constants.BRAND_NAME_CODE_LIST.SR'),
           config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
           config('constants.BRAND_NAME_CODE_LIST.DP'),
           config('constants.BRAND_NAME_CODE_LIST.PB'),
           config('constants.BRAND_NAME_CODE_LIST.SD'),
           config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
       ];

       if (in_array($brand_code, $allow_list) && $brand_code == config('constants.BRAND_NAME_CODE_LIST.PB')) {
           if (!empty($panel) && $panel == self::PANEL_USER) {
               $allow_login_sessions = true;
           }
       } else if (in_array($brand_code, $allow_list)) {
           $allow_login_sessions = true;
       }

       return $allow_login_sessions;
   }


      public static function allowTopFooterText()
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];

      return in_array($brand_code, $brand_list);
   }

   // // Allow a language forder for brand wise
   // public static function allowLanguageFolder(){
   //    $brand_code = config('brand.name_code');
   //    $brand_list = [
   //       config('constants.BRAND_NAME_CODE_LIST.YP'),
   //    ];
   //    return in_array($brand_code, $brand_list);
   // }

   // SENT EMAIL FOR MONTHLY COMISSION TO MERCHANT WISE
   public static function allowMonthlyComissionEmailForAllMerchant(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
//         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
         //config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.SR'),
        // config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }


   public static function allowTCKNRequiredAndUnique(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
          //config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }


   public static function validateBrandForFastpayPayment():bool
   {
       return in_array(config('brand.name_code'),
       [
           config('constants.BRAND_NAME_CODE_LIST.FP'),
           config('constants.BRAND_NAME_CODE_LIST.SR')
       ]
       );
   }

    public static function conditionallyRefundButtonShow(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function removeSameContentFromWellComeMail(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function getReceiptFooterChangeAndText(){
      $brand_code = config('brand.name_code');
      $content = '' ;

       $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.SD'),
           config('constants.BRAND_NAME_CODE_LIST.SR'),
           config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
       ];

      if(in_array($brand_code, $brand_list)){
         $allow = true;
         $content = '<tr>';
            $content .= '<td style="padding: 20px 5px 1px 5px; text-align: center;">';
               $content .= '<p style="marginh: 0; padding: 0; text-align: center;font-size: 12px;">';
                  $content .= __('This receipt has no financial value. The transaction commission amount deducted by you will also be sent to your e-mail address as an invoice.');
               $content .= '</p>';
            $content .= '</td>';
         $content .= '</td>';
      }

      return $content;
   }

    public static function isRequiredTickedCloseNote()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }
   public static function forceChargebackAttachment(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
         config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }


   public static function allowedWalletGateBrand()
   {
      // only for sipay
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.SP'),
      ];

      return in_array($brand_code, $brand_list);
   }


   // when a customer or user change h/her password it will give the url where it will be redirected
   public static function passwordChangeRedirectionOfCustomerPanel(){

      $redirect_url = route('logout');

      $brand_code = config('brand.name_code');
      $same_page_brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];

      if(in_array($brand_code, $same_page_brand_list)){
         $redirect_url = route('security.index');
      }

      return $redirect_url;
   }

    public static function hasActivationCodeServiceWarning($user_category)
    {
        return BrandConfiguration::allowActivationCodeService()
            && !GlobalFunction::isNotKYCVerifiedUser($user_category)
            && GlobalFunction::isNotActivationCodeServiceVerified($user_category);
    }

   public static function hidePaymentReciveSection($value = PaymentReceiveOption::SIPAY_WALLET){
      $brand_code = config('brand.name_code');
      $not_showing_list = [
         config('constants.BRAND_NAME_CODE_LIST.FP'),
         config('constants.BRAND_NAME_CODE_LIST.PN'),
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return (!in_array($brand_code, $not_showing_list)) && GlobalFunction::checkPaymentSetupHasPermission($value);
   }


   public static function agreementCheckboxRules(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.PB'),
         config('constants.BRAND_NAME_CODE_LIST.SD'),
          //config('constants.BRAND_NAME_CODE_LIST.PL'),
         ];
      return in_array($brand_code, $brand_list);
   }

   public static function isLogoutOtherDeviceWarning(){
      $panel = config('constants.defines.PANEL');
      $logout_warning = false;
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.DP'),
        config('constants.BRAND_NAME_CODE_LIST.PB'),
        config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
	      config('constants.BRAND_NAME_CODE_LIST.FP'),
      ];

      if(in_array($brand_code, $brand_list) && $brand_code == config('constants.BRAND_NAME_CODE_LIST.PB')){
         if(!empty($panel) && ($panel == self::PANEL_USER || $panel == self::PANEL_ADMIN)){
            $logout_warning = true;
         }


      }elseif (in_array($brand_code, $brand_list)){
         $logout_warning = true;
      }

      return $logout_warning;
   }


    public static function userTypesAndLimits()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];

        return in_array($brand_code, $brand_list);
    }


   public static function restrictFinflowCashIn () {
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.SP'),
         config('constants.BRAND_NAME_CODE_LIST.FP')
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function isCashInOutBasicAuthWebHook ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }


    public static function redirectToLoginAtPasswordReset () {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function allowCommissionFromSender(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.AP'),
         config('constants.BRAND_NAME_CODE_LIST.PN'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }
   // KYC verified Message

   public static function kycVerifiedMessage(){
      $brand_code = config('brand.name_code');
      $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.YP'),
          config('constants.BRAND_NAME_CODE_LIST.PC'),
          config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.SR'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      $message = __('Thank you. You are now verified.');
      if(in_array($brand_code, $brand_list)){
         $message = __("Your account has been verified, congratulations. Now you can enjoy financial freedom with :company wallet.",['company' => config('brand.name')]);
      }
      return $message;
   }

   public static function moneyTransferEmailAttachment() {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.FP'),
        config('constants.BRAND_NAME_CODE_LIST.SP'),
      ];
      return in_array($brand_code, $brand_list);
   }

   // Fraud rules cron job timer should be change on 2 mins for chaching
   public static function fraudRulesCronJobTimePrevent() {

      $brand_code = config('brand.name_code');
	  $minute = 18;

      if(Arr::isAMemberOf($brand_code, [
	      config('constants.BRAND_NAME_CODE_LIST.SR'),
      ])){
		  $minute = 2;
      }

      return 60 * $minute;

   }

   // Add calender on daily balance report
   public static function allowCalenderOnDailyBalanceReport(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         // config('constants.BRAND_NAME_CODE_LIST.FP'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function allowRollingReserveCurrency(){

        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowDifferentSettlementForSingleAndMultipleInstallment(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function chooseSettlementIdByInstallment($installment_number, $settlement_id, $single_payment_settlement_id = null , $card_type = '', $debit_card_settlement_id = null)
    {
        if (!is_null($single_payment_settlement_id) && BrandConfiguration::isAllowDifferentSettlementForSingleAndMultipleInstallment()) {
            if (is_null($installment_number) || $installment_number <= 1) {
                $settlement_id = $single_payment_settlement_id;
            }
        }

        if (!empty($debit_card_settlement_id) && $card_type == Card::CARD_TYPE_DEBIT_CARD && BrandConfiguration::call([BackendMix::class, 'isAllowDebitCardSettlementForMerchant'])) {
            $settlement_id = $debit_card_settlement_id;
        }

        return $settlement_id;
    }

   public static function cashoutToBankTemplate() {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.SP'),
      ];
      return in_array($brand_code, $brand_list);
   }

   // Branded Payment Gateway when user doesnt have account on system
   public static function hideWalletPaymentShowingOnDpl($mobile_number = null) {
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
         // config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];

      if(in_array($brand_code, $brand_list) && !is_null($mobile_number)){
         return !GlobalUser::getStatusWalletByMobileNo($mobile_number);
      }

	  if(\is_null($mobile_number)){
		return in_array($brand_code, $brand_list);
	  }

      return false;
      //return (in_array($brand_code, $brand_list) && GlobalUser::getStatusWalletByMobileNo($mobile_number));
   }

   public static function sendmoneyDesclaimerChange() {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function getProdIP():array
    {
        $brand_code = config('brand.name_code');

        switch($brand_code){
            case config('constants.BRAND_NAME_CODE_LIST.SP'):
                return ['176.53.6.134', '76.53.34.104'];
            default:
                return [];
        }

    }

   // the screen is showing on the mail receivers panel
   const BLOCK_USER_LOG_KEY = 'BLOCK_USER_EMAIL';
   public static function allowSentBlockMailToAdmins(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.PB'),
          config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }

   // when a merchant is rejected by merchant applications
   const MERCHANT_APPLICATION_REJECTED_KEY = 'MERCHANT_APPLICATION_REJECTED_KEY';
   const MERCHANT_APPLICATION_RETURN_KEY = 'MERCHANT_APPLICATION_RETURN_KEY';
   const MERCHANT_APPLICATION_MAKER_CHECKER_KEY = 'MERCHANT_APPLICATION_MAKER_CHECKER_KEY';
   public static function allowSentMailToRejectedFromMerchantApplication(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);

   }

   // POS COT persentage values decimal places dynamically change
   public static function decimalDigitOfPosCotPersentageValue() {

      $defult_digit = 2;
      $brand_code = config('brand.name_code');

      $allow_three_digit = [
         config('constants.BRAND_NAME_CODE_LIST.FP'),
      ];

      if(in_array($brand_code, $allow_three_digit)){
         $defult_digit = 3;
      }

      return $defult_digit;
   }


   public static function userDepositChangedText(){
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }

   const NON_BRAND_USER_ERROR_TEXT = 'Process can not be done.';
   // when a user create a new sent money to non brand users
   public static function nonBrandSendMoneyRestiction(){
      $brand_code = config('brand.name_code');
      $brand_list = [
      //   config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
         config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.PB'),
          config('constants.BRAND_NAME_CODE_LIST.SD'),
          //config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function isIgnoreMerchantWelcomeMail(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
	        config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isIgnoreMerchantWelcomeMailForMerchantNewUser(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowMerchantFirstActivationMail(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
	        config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowMakerCheckerNotificationMail(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowMerchantStatusUpdateMail(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function tcknVknBlacklist() {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.DP'),
        config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        config('constants.BRAND_NAME_CODE_LIST.FP'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function userChangesNotifincationSend() {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

   // UserName column show on withrawls table
   public static function isShowMechantWitdrawTablelUserName(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.PB'),
	      config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];
      return in_array($brand_code, $brand_list);
   }


   public static function billPaymentAvailableBrand() {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
        config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        // config('constants.BRAND_NAME_CODE_LIST.SP'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function isShowAnalyticsRevenue(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.VP'),
	        config('constants.BRAND_NAME_CODE_LIST.FP'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return in_array($brand_code, $brand_list);
    }
    // DPL 5 digit limit for YPAY
   public static function dplDigitLimit(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
          config('constants.BRAND_NAME_CODE_LIST.SR'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
      ];
      $custom_brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.PM')
      ];
      $lenght=12;
      if(in_array($brand_code, $brand_list)){
         $lenght=4;
      }
      if(Arr::isAMemberOf($brand_code, $custom_brand_list)){
         $lenght= DPL::CUSTOM_AMOUNT_LENGTH;
      }
      return $lenght;
   }

    public static function isAllowResetSecretQuestion(){
        $panel = config('constants.defines.PANEL');
        $allow_login_sessions = false;
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];

        if (in_array($brand_code, $allow_list) && $brand_code == config('constants.BRAND_NAME_CODE_LIST.PB')) {
            if (!empty($panel) && $panel == self::PANEL_ADMIN) {
                $allow_login_sessions = true;
            }
        } else if (in_array($brand_code, $allow_list)) {
            $allow_login_sessions = true;
        }

        return $allow_login_sessions;
    }
    public static function isAllowExportForFraudRoles(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];

        if($brand_code == config('constants.BRAND_NAME_CODE_LIST.SP') && Helper::isSpNginxServerEnvironment()) {
            $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.SP');
        }

        return in_array($brand_code, $brand_list);
    }

    public static function isAllowExportForAmlRules(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function isAllowInactivityNotificationMail(){
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
          config('constants.BRAND_NAME_CODE_LIST.PB'),
          config('constants.BRAND_NAME_CODE_LIST.SR'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
	      config('constants.BRAND_NAME_CODE_LIST.FL'),
	      config('constants.BRAND_NAME_CODE_LIST.VP'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function allowDifferentCOTForRiskyCountries(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }


   public static function allowNameSurnameFields(){
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
      ];

      $status = Arr::isAMemberOf($brand_code, $brand_list);
       if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.PM') && Helper::isProdServerEnvironment()) {
           $status =  false;
       }

      return $status;
   }


   public static function getUserTypesForCurrencySetting ()
   {
        $userTypes = User::USER_TYPES;
        unset($userTypes[User::ADMIN]);
        unset($userTypes[User::INTEGRATOR]);
        unset($userTypes[User::SALES_ADMIN]);
        unset($userTypes[User::SALES_EXPERT]);

        $brand_code = config('brand.name_code');

        if(!BrandConfiguration::isWalletPaymentExist()){
            unset($userTypes[User::CUSTOMER]);
        }

        return $userTypes;
   }


	public static function allowSentMailForInformationChange(){
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
			config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
		];
		return in_array($brand_code, $brand_list);
	}
   public static function isEnableChatService(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            // config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];

        return in_array($brand_code, $brand_list);
   }

	public static function restrictLoginToInactiveUser(){
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
         config('constants.BRAND_NAME_CODE_LIST.FL'),
         config('constants.BRAND_NAME_CODE_LIST.PP'),
         config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
		];
		return in_array($brand_code, $brand_list);
	}

   // One time DPL removing from PB
   public static function notAllowDplOneTime(){
      $brand_code = config('brand.name_code');
      $hidden="";
      $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.PB'),
	      config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];
      if(in_array($brand_code, $brand_list)){
         $hidden="disabled hidden";
      }
      return $hidden;
 }
   // One time DPL removing from PB
   public static function isLinkTypeMultiType(){
      $expire_date=Carbon::now();
      $brand_code = config('brand.name_code');
      $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.PB'),
	      config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];
      if (in_array($brand_code, $brand_list)){
         $expire_date=$expire_date->addYear(2);
      }
      return in_array($brand_code, $brand_list);
 }

   public static function userLoginAlertSettings() {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.PB'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
        config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.SR'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
          config('constants.BRAND_NAME_CODE_LIST.FL'),
          config('constants.BRAND_NAME_CODE_LIST.PP'),
          config('constants.BRAND_NAME_CODE_LIST.VP'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function allowForgetPasswordWithOtp ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function notificationIconInAPI(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function sendMoneyOTPCheckRemoved (): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];

        // skipping OTP step will not be applicable for PROD of specific brands
        if ($response = Arr::isAMemberOf($brand_code, $brand_list)) {
            $skip_for_prod_brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PL'),
            ];

            if (Arr::isAMemberOf($brand_code, $skip_for_prod_brand_list) && Helper::isProdServerEnvironment()) {
                $response = false;
            }
        }

        return $response;
    }

    public static function searchFilterInMoneyTransfer(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return in_array($brand_code, $brand_list);
    }
    public static function getExpiryYear(){
      $year=10;
      $brand_code = config('brand.name_code');
      $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
          config('constants.BRAND_NAME_CODE_LIST.DP'),
      ];
      if(in_array($brand_code, $brand_list)){
         $year=28;
      };
      return $year;
  }

  	public static function getDebitCardwiseDPLAndMP(){
		$brand_code = config('brand.name_code');


		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FP'),
		];

		return in_array($brand_code, $brand_list);
	}

    public static function userPanelActivityListApiProfilePicture(){
		$brand_code = config('brand.name_code');

		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
			config('constants.BRAND_NAME_CODE_LIST.PL'),
		];

		return in_array($brand_code, $brand_list);
	}

    public static function isAllowUserFavouriteOperations(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return in_array($brand_code, $brand_list);
    }
   public static function isAllowMerchantInformationUpdateMail(): bool
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }

   public static function restrictMerchantByAccountManager(): bool
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.SP'),
        config('constants.BRAND_NAME_CODE_LIST.PB'),
	      config('constants.BRAND_NAME_CODE_LIST.PC'),
	      config('constants.BRAND_NAME_CODE_LIST.VP'),
      ];
      return in_array($brand_code, $brand_list);
   }

   public static function walletPanelRedirection()
   {
      $brand_code = config('brand.name_code');
      $redirect_url = '';

      $merchant_panel_redirection = [
        config('constants.BRAND_NAME_CODE_LIST.FP'),
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
	      config('constants.BRAND_NAME_CODE_LIST.PC'),
	      config('constants.BRAND_NAME_CODE_LIST.PB'),
      ];

      $sipay_panel_redirection = [
         config('constants.BRAND_NAME_CODE_LIST.SP'),
      ];

      $ypay_panel_redirection = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];

       $pl_panel_redirection = [
           config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
       ];

      if(in_array($brand_code, $merchant_panel_redirection)){

         $redirect_url = config('constants.APP_MERCHANT_DOMAIN');

      }elseif(in_array($brand_code, $sipay_panel_redirection)){

         if(config('app.env') == 'sp_prod'){

            $redirect_url = 'https://sipay.com.tr';

         }else{

            $redirect_url = 'https://sipay.com.tr';

         }

      }elseif(in_array($brand_code, $ypay_panel_redirection)){

         if(config('app.env') == 'yp_sr_admin_prod' || config('app.env') == 'yp_sr_admin_prov'){

            $redirect_url = config('app.APP_ADMIN_URL');

         }

      }elseif(in_array($brand_code, $pl_panel_redirection)){

          $app_env = config('app.env');

          if($app_env == 'pl_admin_prov' || $app_env == 'pl_admin_prod'){
              //ADMIN PANEL
              $redirect_url = config('app.APP_ADMIN_URL');
          }elseif($app_env == 'pl_merchant_prov' || $app_env == 'pl_merchant_prod'){
              // MERCHANT PANEL
               $redirect_url = config('constants.APP_MERCHANT_DOMAIN');
          }elseif($app_env == 'pl_ccpayment_prov' || $app_env == 'pl_ccpayment_prod'){
              // CCPAYMENT
              $redirect_url = config('app.CC_PAYMENT_URL');
          }
      }

      return $redirect_url;
   }
   //User Panel Sector Update for YPAY
   public static function userSectorUpdate(){
      $brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
			config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
		];
      return in_array($brand_code, $brand_list);
	}

   public static function supportTicketPendingStatus(): bool
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
          config('constants.BRAND_NAME_CODE_LIST.PB'),
          config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.SR'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }

   public static function changeMerchantUserEmailPhone(){
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.SR'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function isAllowAdminWelcomeMail(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.DP'),
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
	        config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
	        config('constants.BRAND_NAME_CODE_LIST.HP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function checkWrongMaxAttemptsSecretAnswer(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function brandWisePaymentRecOption()
    {
        $payment_option = PaymentRecOption::PAYMENT_OPTION;

        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];

        if(in_array($brand_code, $brand_list)){
            unset($payment_option[2]); // for mobile payment
            unset($payment_option[3]); // for wallet payment
        }
        return $payment_option;
    }

    public static function isWalletPaymentExist(): bool
    {

        return false;
    }

   public static function customerEmailVerificationRemoveButton(){
      $brand_code = config('brand.name_code');
      $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.DP'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function brandWiseUserTypes($user_types = [])
    {
        $user_types_list = GlobalUser::getUserTypeList();
        if(!empty($user_types)){
            foreach ($user_types_list as $key => $user_type) {
                if(!in_array($key, $user_types)){
                    unset($user_types_list[$key]);
                }
            }
        }
        if(!BrandConfiguration::isWalletPaymentExist()){
            unset($user_types_list[User::CUSTOMER]);
        }
        return $user_types_list;
    }
    public static function brandWiseAuditorRtList()
    {
        $auditor_rt_list = GlobalUser::getMerchantReportAuditorRtList();

        if(self::unsetBankBrandDepositId()){
            unset(
                $auditor_rt_list[MerchantReportHistory::RT_SIPAY_BANK_CUSTOMERS_DEPOSITS]
            );
        }

        if(!BrandConfiguration::isWalletPaymentExist()){
            unset(
				$auditor_rt_list[MerchantReportHistory::RT_CUSTOMERS_LIST],
				$auditor_rt_list[MerchantReportHistory::RT_SIPAY_BANK_CUSTOMERS_DEPOSITS],
				$auditor_rt_list[MerchantReportHistory::RT_CREDIT_CARD_CUSTOMERS_DEPOSITS],
				$auditor_rt_list[MerchantReportHistory::RT_CUSTOMERS_WITHDRAW],
				$auditor_rt_list[MerchantReportHistory::RT_B2C_TO_WALLET],
				$auditor_rt_list[MerchantReportHistory::RT_C2C_MONEY_TRANSFER],
				$auditor_rt_list[MerchantReportHistory::RT_B2C_TO_BANK_SENDER_CORPORATE_MERCHANTS],
				$auditor_rt_list[MerchantReportHistory::RT_B2C_TO_BANK_SENDER_INDIVIDUAL_MERCHANTS],
				$auditor_rt_list[MerchantReportHistory::RT_B2B_SENDER_CORPORATE_RECEIVER_CORPORATE_MERCHANTS],
				$auditor_rt_list[MerchantReportHistory::RT_B2B_SENDER_CORPORATE_RECEIVER_INDIVIDUAL_MERCHANTS],
				$auditor_rt_list[MerchantReportHistory::RT_B2B_SENDER_INDIVIDUAL_RECEIVER_CORPORATE_MERCHANTS],
				$auditor_rt_list[MerchantReportHistory::RT_B2B_SENDER_INDIVIDUAL_RECEIVER_INDIVIDUAL_MERCHANTS]
			);
        }
        return $auditor_rt_list;
    }


   public static function allowChargeBackForCancelledOne (){
      $brand_code = config('brand.name_code');
      $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.SP'),
          config('constants.BRAND_NAME_CODE_LIST.FP'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function isShowKycVerificationAndLimitsFields(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return !in_array($brand_code, $brand_list);
    }

    public static function isRemoveRequiredFieldsFromMailReceivers(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
	}

	public static function isHideAddCard(){
		$brand_code = config('brand.name_code');
		$brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
	}

	public static function isHideRecurringPanel(){
		$brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
	}

	public static function isHideFinFlow(){
        /*
         * should use isAllowedFinflowService() instead
         */
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
		];
		return in_array($brand_code, $brand_list);
	}

   public static function disableManualPosRecureing(){
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
		];
		return in_array($brand_code, $brand_list);
	}

   public static function isHideB2BPaymentSettings(){
      $brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
		];
		return in_array($brand_code, $brand_list);
   }

   public static function isHideB2CPaymentSettings(){
      $brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
		];
		return in_array($brand_code, $brand_list);
   }

    public static function isHideServiceType(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function getRedirectRouteDashboard($type){
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
      ];

      $dynamic_route = '';

      return $dynamic_route;

   }

   public static function showOnlyTryCurrencyOnCashOut(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
      ];
      return in_array($brand_code, $brand_list);
   }

   public static function isHideBarndServerIp(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
      ];
      return in_array($brand_code, $brand_list);
   }

   //Alert message changing of QP
   public static function binNumberDisclaimer(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
      ];
      return in_array($brand_code, $brand_list);
   }

   public static function hideSendMonyModuleFromMerchantPanel(){
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.HP'),
        config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),config('constants.BRAND_NAME_CODE_LIST.YP'),
      ];
      return in_array($brand_code, $brand_list);
   }

   public static function changeWithdrawErrorMessage(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.YP'),
         config('constants.BRAND_NAME_CODE_LIST.PC'),
          config('constants.BRAND_NAME_CODE_LIST.SR'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }

   public static function brandWiseAuditorRtFileType(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
      ];

      $file_list = MerchantReportHistory::FORMAT_LIST;
      if(!in_array($brand_code, $brand_list)){
         unset(
            $file_list[MerchantReportHistory::FORMAT_XLS],
            $file_list[MerchantReportHistory::FORMAT_PDF]
         );
      }else{
         unset(
            $file_list[MerchantReportHistory::FORMAT_PDF]
         );
      }

      return $file_list;
   }


    public static function btoBCronjobReceiverMerchant ()
    {
        $brand_code = config('brand.name_code');
        $app_env = config('constants.APP_ENVIRONMENT');
        $receiver_merchant_id = [];

        if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.SP')) {
            if ($app_env == 'sp_prod') {
                $receiver_merchant_id = [41517, 85757];
            } elseif ($app_env == 'sp_prov') {
                $receiver_merchant_id = [98950, 10294, 12111];
            } else {
                $receiver_merchant_id = [35646];
            }
        }
        return $receiver_merchant_id;
    }

    public static  function validBrandForRefundCommissionDeductionValidation():bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowChangeCustomerPhoneNumber()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowSendMailOnEmailChanged()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowSendMailOnPhoneChanged()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowVirtualCard()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    /**
     * @return bool
     */
    public static function addChargeToSenderInB2BPayment(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }
	public static function allowDashboardRedirectionOnMerchantPanel()
	{
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
		];
		return in_array($brand_code, $brand_list);
	}

	public static function hideCurrencyExchangeRate(){
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
			config('constants.BRAND_NAME_CODE_LIST.FP'),
		];
		return in_array($brand_code, $brand_list);
	}

   Public static function allowUserActionHistories(){
      $brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FP'),
		];
		return in_array($brand_code, $brand_list);
   }

   public static function allowAccessControllUpdateAt(){
      $brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FP'),
		];
		return in_array($brand_code, $brand_list);
   }

   public static function qnbExtraPfRecordsParams()
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
      ];
      return in_array($brand_code, $brand_list);
   }

   public static function isAllowMerchantSettlementReports()
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.FP'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
          config('constants.BRAND_NAME_CODE_LIST.PB'),
          config('constants.BRAND_NAME_CODE_LIST.YP'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function checkAnnouncementLanguage(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
	        config('constants.BRAND_NAME_CODE_LIST.PM'),
        ];
        return in_array($brand_code, $brand_list);
    }

     public static function allowRolePageAllRoleExport(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.FP'),
      ];
      return in_array($brand_code, $brand_list);
   }
   public static function allowBrandAdjustExpiryMonth(){
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.SR')
      ];
      return in_array($brand_code, $brand_list);
   }
   public static function checkCardHolderNameWithExtraCardHolderName(){
      $brand_code = config('brand.name_code');
      $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.FP')
      ];
      return in_array($brand_code, $brand_list);
   }
    public static function isCustomQueueEnable(){

		if(GlobalFunction::isLocalEnvironment()){
            return false;
        }

        return true;

        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];

        return in_array($brand_code, $brand_list);
    }

    public static function allowedReportsApiBrands ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function hideOnBoundApis()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

	public static function allowInterTechCurrencyExchangeApi(){
		$brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP')
        ];

        return in_array($brand_code, $brand_list);
	}

    public static function allowCollectapiCurrecnyExchangeApi(){

        $brand_code = config('brand.name_code');
        $brand_list = [

        ];
        return in_array($brand_code, $brand_list);
   }

    public static function allowHiddenCaptcha()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            //config('constants.BRAND_NAME_CODE_LIST.FP')
        ];

        return in_array($brand_code, $brand_list);
    }

    public static function allowMultiFraudRules()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
        ];

        if($brand_code == config('constants.BRAND_NAME_CODE_LIST.SP') && Helper::isSpNginxServerEnvironment()) {
            $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.SP');
        }

        return in_array($brand_code, $brand_list);
    }
	public static function allowSendSmsAndEmailSerially(){
		$brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.FL')
        ];
        return in_array($brand_code, $brand_list);
	}

	public static function stopRegistrationAtBrandGatway(){
		return ( Self::apiUserPanelBrandRestriction() || Self::hideWalletPaymentShowingOnDpl() );
	}

    public static function sendMailViaWsdl()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            //config('constants.BRAND_NAME_CODE_LIST.HP'),
            //config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        // return in_array($brand_code, $brand_list);
        return in_array($brand_code, $brand_list) && !(config('app.env') == 'qp_dev' || config('app.env') == 'qp_tenat_dev');
    }

    public static function isBrandForFastpayFraudService():bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
          //  config('constants.BRAND_NAME_CODE_LIST.SR')
        ];

        return in_array($brand_code, $brand_list);
    }

    public static function allowMerchantApplicationRejectText(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];

        return in_array($brand_code, $brand_list);
    }

    public static function restrictSupportTicketAssignment ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowMerchantBlockingMail(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];

        return in_array($brand_code, $brand_list);
    }

    public static function allowSomeDocumentsMerchantApplication()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
             config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowNoSecureConnection(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            //config('constants.BRAND_NAME_CODE_LIST.PL')
	        config('constants.BRAND_NAME_CODE_LIST.MOP'),
        ];

        return in_array($brand_code, $brand_list);
    }

    public static function allowOffTimeFinflowCall ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowedFinflowCrossValidation ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.PM')
        ];

        if (Helper::isProvServerEnvironment() && config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.PP')) {
            $brand_list = Arr::merge($brand_list, [config('constants.BRAND_NAME_CODE_LIST.PP')]);
        }
        if (Helper::isProvServerEnvironment() && config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.SR')) {
            $brand_list = Arr::merge($brand_list, [config('constants.BRAND_NAME_CODE_LIST.SR')]);
        }

        return in_array($brand_code, $brand_list);
    }

    public static function isAllowedFinflowIpRestriction ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.VP')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowedFinflowHashKey ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
        ];
        /*
         * the if() below will be removed along with the
         * removal of CashInOut::isAllowedFinflowAdditionalParam()
         */
        if (CashInOut::isAllowedFinflowAdditionalParam()) {
            $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.SP');
        }

        return in_array($brand_code, $brand_list);
    }

	public static function hideSendPaymentReceiptOnInformationPage(){

        $brand_code = config('brand.name_code');
        $brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.PB')
        ];
        return !in_array($brand_code, $brand_list);

	}

    public static function allowMerchantApplicationChangedContent ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
	}

	public static function getMerchantFeeCalculationWithEndUserFee(){

        $brand_code = config('brand.name_code');
        $brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.PB')
        ];
        return in_array($brand_code, $brand_list);

	}

	public static function viewInstallmentTableOnManualPos(){

		$brand_code = config('brand.name_code');
		$brand_list =  \array_merge(Pos::MANUAL_POS_INST_TABLE, [

		]);
		return in_array($brand_code, $brand_list);
    }

	public static function hideTakeComissionFromUser(){
		$brand_code = config('brand.name_code');
        $brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FP'),
			config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
			config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
	        config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return !in_array($brand_code, $brand_list);
	}

    public static function cashoutFinflowViaCronjob ()
    {
        /*
         * now finflow service run via cronjob
         */
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowCompanyNameAsAccHolder ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            //config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowBankAccHolderNameInFinflow ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP')
        ];
        return in_array($brand_code, $brand_list);
    }

	public static function hideIssuerFromBankNotification(){
		$brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP')
        ];
        return !in_array($brand_code, $brand_list);
	}

    public static function restrictSupportTicketReAssignment()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function disableKycVerficationForUser()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
//            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            //config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function enableExtraColumnInAccountStatementReport()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function allowDplSavedLink(){
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.SP')
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function userEmailChangeSessionOut()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function allowRestrictFilterForMerchantAndUsers(){

      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.SP'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function allowSalesPanel()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
//            config('constants.BRAND_NAME_CODE_LIST.HP'),
//            config('constants.BRAND_NAME_CODE_LIST.MOP'),
//            config('constants.BRAND_NAME_CODE_LIST.QP'),
//            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
//            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }


    public static function allowTakePaymentFeature()
    {
        $brand_code = config('brand.name_code');

        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.PN'),
          config('constants.BRAND_NAME_CODE_LIST.DP'),
          config('constants.BRAND_NAME_CODE_LIST.EP'),
          config('constants.BRAND_NAME_CODE_LIST.SR'),
          config('constants.BRAND_NAME_CODE_LIST.AP'),
          config('constants.BRAND_NAME_CODE_LIST.MP'),
          config('constants.BRAND_NAME_CODE_LIST.FL'),
          config('constants.BRAND_NAME_CODE_LIST.PP'),
          config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
          config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowMerchantWithdrawal()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function disableMakerCheckerForTicketClose()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function allowBrandChecklistControl()
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.SP'),
      ];
      return in_array($brand_code, $brand_list);
   }

   	public static function allowBankKartCombo(){
		$brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP')
        ];
        return in_array($brand_code, $brand_list);
	}

    public static function allowVerifyEmailSendChangeEmailUser()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function setMerchantAnalyticsDaily ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB')
        ];
        return in_array($brand_code, $brand_list);
    }

    // Password validation with regex ypay-186
    public static function allowAlphaNumericPasswordRegex(){
        $brand_code = config('brand.name_code');
        $brands = [
            // config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            //config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];

        return in_array($brand_code, $brands);
    }

   public static function isAllowDataModificationNotification ()
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.SD'),
          config('constants.BRAND_NAME_CODE_LIST.SR'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
          config('constants.BRAND_NAME_CODE_LIST.FP'),
      ];
      return in_array($brand_code, $brand_list);
   }


    public static function allowSupportTicketEmail()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function isValidatePhoneNumberLength ()
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
	        config('constants.BRAND_NAME_CODE_LIST.PM'),
     //   config('constants.BRAND_NAME_CODE_LIST.SP')
      ];
      return in_array($brand_code, $brand_list);
   }

   public static function allowedSplitAccountBrands ()
   {
       $brand_code = config('brand.name_code');
       $brand_list = [
           config('constants.BRAND_NAME_CODE_LIST.FL'),
           config('constants.BRAND_NAME_CODE_LIST.PP'),
           config('constants.BRAND_NAME_CODE_LIST.SR'),
       ];
       return in_array($brand_code, $brand_list);
   }


    public static function otpLimitEnable()
    {
        return true;
    }

    public static function allowExtraParameterInManualPos ()
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function hideDepositMethod()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowAvailableBalanceInfo()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),

        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowDplLinkAdvanceSettingCheckbox()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    //VEP-598 SMS Data coding
    public static function allowSmsDataCoding(){
        $brand_code = config('brand.name_code');
        $brands = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return in_array($brand_code, $brands);
    }

    //VEP-598 SMS Data coding
    public static function allowSecretQuestionOnRegistration(){
        $brand_code = config('brand.name_code');
        $brands = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];

        return in_array($brand_code, $brands);
    }
    //Remove Remember Me button
    public static function removeRememberMeButton()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowProtectedAmountReport()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

     public static function isAllowformatFraudRoleName()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
        ];

        if($brand_code == config('constants.BRAND_NAME_CODE_LIST.SP') && Helper::isSpNginxServerEnvironment()) {
            $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.SP');
        }

        return in_array($brand_code, $brand_list);
    }

   public static function isAllowImportBinRange()
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }

   public static function isAllowManageBinRange()
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        config('constants.BRAND_NAME_CODE_LIST.SP'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
        config('constants.BRAND_NAME_CODE_LIST.PB'),
        config('constants.BRAND_NAME_CODE_LIST.PM'),
	      config('constants.BRAND_NAME_CODE_LIST.FP'),
	      config('constants.BRAND_NAME_CODE_LIST.VP'),
      ];
      return in_array($brand_code, $brand_list);
   }

   public static function isAllowCardInfoFromBinRangeQp()
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        //    config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function showPasswordRulesOnRegistration()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
	        config('constants.BRAND_NAME_CODE_LIST.FL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function disableMutiUseOneTimeDPLFailedPayment()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowPasswordResetMailMessage(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function disableImportedTransactionHistory()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            // config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowMT()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowPavo()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
	        config('constants.BRAND_NAME_CODE_LIST.PL'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowOxivo()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowedHugin()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowWetSignedMerchantReprotSubModule()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

   public static function allowWriteEmailInResetPassword()
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.YP'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
          config('constants.BRAND_NAME_CODE_LIST.SR'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
      ];
      return in_array($brand_code, $brand_list);
   }
   // SMS and Email texts will be changed for QNB QNBPAY-161
   public static function smsAndEmailChangings()
    {
           $brand_code = config('brand.name_code');
           $brand_list = [
               config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
           ];
           return in_array($brand_code, $brand_list);
    }

    public static function allowDplLinkNotificationRemainder()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function storeFrontRedirectedUrlChange()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowedAmlService(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SD')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowRefundToWalletSMS(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function hideApiSettingSomeCheckbox()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isWithOptOutgoingViaCron ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowCraftgateApiCall ()
    {
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
	        config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.VP'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];

        return in_array($brand_code, $allow_list);
    }

    public static function sendEmailAndSmsViaCron ()
    {
        return false;
        /*if(GlobalFunction::isLocalEnvironment()){
            return false;
        }

        return true;*/

        // $brand_code = config('brand.name_code');
        // $brand_list = [
        //     //config('constants.BRAND_NAME_CODE_LIST.SP')
        // ];
        // return in_array($brand_code, $brand_list);
    }


    public static function isAllowedPanelWiseAml(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
//            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAlllowActiveFraud(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];

        if($brand_code == config('constants.BRAND_NAME_CODE_LIST.SP') && Helper::isSpNginxServerEnvironment()) {
            $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.SP');
        }

        return in_array($brand_code, $brand_list);
    }

    public static function allowMerchantCustomizedCost()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return in_array($brand_code, $brand_list);
    }


    public static function isUpcomingWithdrawalUniqueId ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowRequiredFieldForDPL():bool{
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowSendMailOnSecretQuestionChanged():bool{
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowWithdrawalFileProcess ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }
    public static function disableYoutubeIconInEmailFooter(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowExtraTextInFooter()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function disableCreateWithdrawForMerchant():bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isTestPostAllowed()
    {
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];

        return in_array($brand_code, $allow_list);
    }

    public static function allowFinflowCheckStatus()
    {
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];

        return in_array($brand_code, $allow_list);
    }

    public static function allowChangeSubmitButton()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }


   public static function enableExtraColumnForAccountStatementReport()
   {
      $brand_code = config('brand.name_code');
      $brand_list = [
        config('constants.BRAND_NAME_CODE_LIST.SP'),
      ];
      return in_array($brand_code, $brand_list);
   }

    public static function isTenant()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }

    // only ui
    public static function disableRefundToWallet():bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    // only ui
    public static function disableDashboardDailySummary():bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    // only ui
    public static function disableAlternativePayment():bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    // only ui and validation
    public static function disableSWIFTCode():bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    // only ui
    public static function disableUserForBank():bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    // only ui
    public static function disableMerchantPayment():bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function addWelcomeMailAlertContent()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function showInvoiceId()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }


   public static function isAllowPaycellBillPayment()
   {
      $brand_code = config('brand.name_code');
      $allow_list = [
        config('constants.BRAND_NAME_CODE_LIST.FL'),
        config('constants.BRAND_NAME_CODE_LIST.PP'),
        config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.SD'),
         config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        config('constants.BRAND_NAME_CODE_LIST.PC'),
      ];

      return in_array($brand_code, $allow_list);
   }


    public static function allowedMultiplePosCampaignCategory(){
        $brand_code = config('brand.name_code');
        $allow_list = [
         // config('constants.BRAND_NAME_CODE_LIST.SP')
        ];

        return in_array($brand_code, $allow_list);
    }

    public static function isLicenseOwnerTenant()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }


    public static function allowMerchantTypeFilter():bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function showMerchantIdForReport()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function convertToFloatForReport()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function disableSendTransactionRecept()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function curlRequestVerifyFalseForCodec(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowOtpTimerAdminLogin()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function disableWindoAnimation(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isEnabledCronjobSetting()
    {
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];

        return in_array($brand_code, $allow_list);
    }

    public static function sendSmsProviderNameChangeAsBrandName()
    {
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];

        return in_array($brand_code, $allow_list);

    }

    public static function isAllowSendDPLLinkMultipleUsers()
    {
        $brand_code = config('brand.name_code');
        $allow_list = [
          config('constants.BRAND_NAME_CODE_LIST.FP')
        ];

        return in_array($brand_code, $allow_list);
    }

    public static function allowContactCheck()
    {
        $brand_code = config('brand.name_code');
        $allow_list = [
          config('constants.BRAND_NAME_CODE_LIST.FL'),
          config('constants.BRAND_NAME_CODE_LIST.PP'),
          config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];

        return in_array($brand_code, $allow_list);
    }



    public static function isEnabledOtherReason ()
    {
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $allow_list);
    }

    public static function isCustomMailAllow(){
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $allow_list);
    }

    public static function userPanelLogNameChangeToSmsEmail(){
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $allow_list);
    }
    public static function hideExchangeHistory(){
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
		];
		return in_array($brand_code, $brand_list);
	}

    public static function allowAppPushNotifications(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function newBrandGateCreditcardTemplate(){
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.PB')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowSendAnnouncementToSubMerchant()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function showInstallmentInCheckStatusApi(){
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.PB'),
          config('constants.BRAND_NAME_CODE_LIST.VP'),
          config('constants.BRAND_NAME_CODE_LIST.SP')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowSmsAndEmailChanges()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowGetUserCategoryLimitApi(){
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.FL'),
          config('constants.BRAND_NAME_CODE_LIST.PP'),
          config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowDefaultDateRangeByUpdatedAt(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowSideBarFooterContent(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowSideBarLogo(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowedFinflowService ()
    {
        /*
         * please do not add any brand unless
         * it doesn't have finflow service activated
         */
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isIgnoreUpdateAmlDetectedTransactionsForCustomer(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowStaticContent ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.FL'),
          config('constants.BRAND_NAME_CODE_LIST.PP'),
          config('constants.BRAND_NAME_CODE_LIST.SR'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
          config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function changeMerchantReportColumns(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowNameSurNameAmlExport()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
         config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return in_array($brand_code, $brand_list);
    }
    public static function showOnlyTLOnAutoWithdrawal(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

     public static function allowOnlyMerchant(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isBrandForSaleAsynchCustomQueue()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function changeBrandNameFromKey ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isDisabledUserPanel ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowDiffereceMerchantCommisionReportTypes ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowMercnatIdOnAmlTable()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowExtraColumnInMerchantReport()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
           config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowSmsVerifierHashString()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
           config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isQPNewMerchantTemplate()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.QP'),
          config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isHideMerchantPanelBottomFooterArea()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.FP'),
          config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
          config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function disablePendingStatusUsersLogin(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function logoutForDaynamicIp(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function checkTrustedDevice ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function checkProductionUrlForSmsAndEmail(){

        $app_env = config('constants.APP_ENVIRONMENT');
        $status = false;
        if($app_env == 'qp_prod' || $app_env == 'qp_tenat_prod'){
            $status = true;
        }
        return $status;

    }

    public static function hideTestMerchantApiInformation(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isNotAllowedImportedTransactions()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            // config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowMerchantDepositCommissionSettingsValidation(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowMerchantWithdrawalCommissionSettingsValidation(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isDifferentMerchantBankAccountHolderName(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowSpanishLanguage(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowedIncomeInfo ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }


    /**
     * Disable Transaction State Add & Delete Option for all brand
     */
    public static function disableTransactionStateAddDeleteOption(){
        return false;
    }

    public static function shouldAllowActiveAndPassiveAutoApproveAsPassiveAmlRuleCategory()
    {
         $brand_code = config('brand.name_code');
         $brand_list = [
             config('constants.BRAND_NAME_CODE_LIST.YP'),
             config('constants.BRAND_NAME_CODE_LIST.PC'),
             config('constants.BRAND_NAME_CODE_LIST.SR'),
             config('constants.BRAND_NAME_CODE_LIST.PB'),
             config('constants.BRAND_NAME_CODE_LIST.PM'),
             config('constants.BRAND_NAME_CODE_LIST.PL'),
             config('constants.BRAND_NAME_CODE_LIST.FL'),
             config('constants.BRAND_NAME_CODE_LIST.PP'),
             config('constants.BRAND_NAME_CODE_LIST.SD')
         ];

         return in_array($brand_code, $brand_list);
    }

    public static function allowDisableInactiveAdmins()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.SD'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowShowFraudRuleTransactionInMerchantTransactions()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
        ];

        if($brand_code == config('constants.BRAND_NAME_CODE_LIST.SP') && Helper::isSpNginxServerEnvironment()) {
            $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.SP');
        }
        return in_array($brand_code, $brand_list);
    }

    public static function allowSortingOnTable()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            //config('constants.BRAND_NAME_CODE_LIST.PB'),
	        //config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isDisplayWithdrawalFailureReason()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowSmsLogs(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowIntegratorInstallments()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB')
        ];
        return in_array($brand_code, $brand_list);
    }
    public static function isAllowedIKSMerchant($shouldCheckEnv=true)
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
	        config('constants.BRAND_NAME_CODE_LIST.PM'),
        ];
        $isBrandAllowed = Arr::isAMemberOf($brand_code, $brand_list);

        if ($isBrandAllowed && $shouldCheckEnv)
        {
            $isBrandAllowed = !empty(config('constants.IKS_CLIENT_ID')) && !empty(config('constants.IKS_CLIENT_SECRET'));
        }

        return $isBrandAllowed;
    }
    public static function hideSomeFieldsOnReceipt()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowPasswordOnBrandedPay ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isQPNewAdminTemplate()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP')
        ];
        return in_array($brand_code, $brand_list);
    }
    public static function customUserActivationCodeText()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function showCurrencyConversions()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function hideCreateExecutionLog()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }


    public static function allowMerchantApplicationDetailsShow()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowCurrrencyCodeOnWithdrawalOnSms(){

        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP')
        ];
        return in_array($brand_code, $brand_list);

    }

    /**
     * @return bool
     */
    public static function allowDplMultipleResource():bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }

    /**
     * @return bool
     */
    public static function isAllowLoginCaptcha():bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            //config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
            config('constants.BRAND_NAME_CODE_LIST.FP'),
	        config('constants.BRAND_NAME_CODE_LIST.PM'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowMerchantFtpSetting()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    /**
     * @return bool
     */
    public static function isAllowAdminAddSupportTicket():bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);

    }
    public static function hideRefundFee()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isOptionalWithdrawMailReceiver ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowWithdrawalSuccesfulNotificationEmail() {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }
    public static function enableEditBinRangeResponse()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
	        config('constants.BRAND_NAME_CODE_LIST.FP'),
	        config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function enableAccountHolderName()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowTotalSaleCountForMerchantAnaliticsReport(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
	        config('constants.BRAND_NAME_CODE_LIST.VP'),
	        config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return in_array($brand_code, $brand_list);
    }
    public static function socialMediaImageColorChange(){
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];

        return in_array($brand_code, $allow_list);
    }

    public static function customGenerateReceipt(){
        $brand_code = config('brand.name_code');
        $allow_list = [
        config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];

        return in_array($brand_code, $allow_list);
    }

    public static function resizeLogo(){
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];

        return in_array($brand_code, $allow_list);
    }

    public static function changeSmstext(){
        $brand_code = config('brand.name_code');
        $allow_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];

        return in_array($brand_code, $allow_list);
    }

    public static function isAllowSariTaxi()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function hideSiteSettingsAdvertisementCodeSection(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return !Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function hideManagementContents(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isEnableBrandBankAccountUserType(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isFPNewMerchantTemplate()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.FP')
        ];
        return in_array($brand_code, $brand_list);
    }


    public static function isAllowDeactivateAccount(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function showUserLimitForVerifiedUnverifiedAndContractedUser()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL')
        ];
        return in_array($brand_code, $brand_list);
    }

    /**
     * @return bool
     */
    public static function isResetPasswordRequestHasLimit():bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowDayAndMonthOnKyc(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    /**
     * @return bool
     */
    public static function isAllowMassWithdrawalApproval()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.PC')
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function allowMerchantScheduleReport()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
            config('constants.BRAND_NAME_CODE_LIST.SP')
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function curlRequestVerifyFalseForVerimore(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowedUserManagementWithOutOTP(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function enableRequestListsExtraField(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    /**
     * @return bool
     */
    public static function isUserAuthoritiesHideInUserManagement()
    {
        return true;
    }

    public static function enableDeleteButton()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    /**
     * @return bool
     */
    public static function isUserPanelCustomText()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function hideSecrectQuestionSectionFromUserKycForm()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isDisableSSLverify(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            //config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function allowCustomUserActivation()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function splitTransactionIdOfSplitAccount(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isShowCustomerAccountDetails (): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowedMakerCheckerMultiCreate (): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }


    public static function isAllowedSiteSettingHelpDocument(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowExportXls(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowGrayLog(): bool
    {
        $brand_code = config('brand.name_code');
        $app_env = config('app.env');
        $is_graylog_host_url_present = !empty(config('constants.GRAYLOG_HOST'));

        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
        ];

        $app_env_list = [
            Helper::APP_SIPAY_NGIX_PROD_ENVIRONMENT,
//            Helper::APP_SIPAY_PROD_ENVIRONMENT,
            Helper::APP_PROALPARA_ADMIN_PROD_ENVIRONMENT,
            Helper::APP_YEMEKPAY_ADMIN_PROD_ENVIRONMENT,
            Helper::APP_YEMEKPAY_PROV_ENVIRONMENT,
            Helper::APP_HEPSIPAY_PROD_ENVIRONMENT
        ];

        return Arr::isAMemberOf($brand_code, $brand_list) && Arr::isAMemberOf($app_env, $app_env_list) && $is_graylog_host_url_present;
    }

    public static function isAllowGrayLogWithoutVerifySSL(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.HP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function allowMaskBankCredentials(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function hideUnblockedStatusFromList(){
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }


    public static function defaultCheckRememberMeButton()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.YP'),

        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function userPanelHiddenField()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.YP'),

        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function allowOthersAdminToDeleteBlockedBin(){
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function showTenantApprovalStatus(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function hiddenWalletPanelCapcha(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            //config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function merchantSupportTicketEnableCustomLength(){

        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);

    }

    public static function enableAutoComplete(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function showMerchantInformationOnMakerChecker(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isEnabledReconciliationReport(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function disableEmailSendingWhilePosChanged(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function isReportMailTextChanged(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function disableSendMoneyExplainFieldRequired(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function allowPosAnalytics(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowedBTrans(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
	        config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
	        config('constants.BRAND_NAME_CODE_LIST.PL'),
	        config('constants.BRAND_NAME_CODE_LIST.FP'),
	        config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
	        config('constants.BRAND_NAME_CODE_LIST.YP'),
	        config('constants.BRAND_NAME_CODE_LIST.PM'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list) && config('constants.IS_BTRANS_ENABLED');
    }

    public static function enableOnlyDigitsForOtp(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT')
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isEnableDeviceDetection(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),

        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isPasswordSaveRestrictedToBrowser(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
//            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
	        config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowDownloadXlsxFile(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    /*
     * send to finflow again from admin panel
     */
    public static function isAllowedResendToFinflow ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.FP')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isallowReceiverSuccessMailTextChanged(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function hidePhoneNumberOnNotification(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }



    public static function isAllowedInfiniteLimit ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function mobileResponsiveForAllReceipt(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
public static function preventUserPanelWrongPasswordRedirection(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
	        config('constants.BRAND_NAME_CODE_LIST.PM'),
	        config('constants.BRAND_NAME_CODE_LIST.FL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }




    public static function allowIncorrectLoginAttemptsNotification(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isVPNewColorScheme(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }


    public static function disableDataModificationNotification(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function showAgreementReadAlert()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function totalAndCommissionAmountSeparately(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function sendWelcomeMailOnAthorizedPersonEmailChanged(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function redirectToLoginAfterPasswordChange()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function enableReasonForChargebackTransactions(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }


    public static function ignoreTokenValidation(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list) ;
    }

    public static function isAllowAccountStatementPdfTextChange()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowTransactionSMSNotification()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.VP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowMerchantUserShowOwnTransaction()
    {
        return true;
    }

    public static function disableMakerCheckerForMerchantAdd(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function receiptAndEmailContentChanges()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }
    public static function hideCreditCardCommissionUser()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function allowVerifyUserByTckn(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function changeRegisterEmailValidationText(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isDisabledMerchantCardBlackList(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function allowFilterWithCreatedDateInAllTransaction(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

	public static function enbaleMetadataIndexing(){
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.VP'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}

    public static function isHideDepositMethod(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            //config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isHideWithdrawalMethod(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            //config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isHidePayTokens(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            //config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isNotRequiredBusinessArea(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function getWithdrawalReceiptTitle()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        if(Arr::isAMemberOf($brand_code, $brand_list)){
            return "Progress Payment Receipt";
        }
        return "Withdrawal";
    }

    public static function isAllowExtraParametersTransactionReport(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function restrictTransactionsForNonVerifiedUsers (): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            //config('constants.BRAND_NAME_CODE_LIST.PP'),
	        config('constants.BRAND_NAME_CODE_LIST.FL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function hideMerchantPanelWithdrawalMenu(){
        $brand_code = config('brand.name_code');
        $brand_list = [
          /*  config('constants.BRAND_NAME_CODE_LIST.YP'),*/
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function hideMerchantPanelDepositMenu(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowSomeColumnSwapTransactionReport(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isEnabledAgreementPolicyOnMerchantPanel(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

     public static function isAllowReceiverGsmMoneyTransferRecept(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowGSMReplaceByNameAndSurname()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function unsetBankBrandDepositId(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function showDeleteReasonOnclickDelete()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function getMerchantWithdrawsTransactionsLabelsContent(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        if(Arr::isAMemberOf($brand_code, $brand_list)){
            return [
                'input_placeholder' => 'Trans. ID',
                'table_header' => 'Trans. ID',
            ];
        }
        return [
            'input_placeholder' => 'Trans. ID, Order ID',
            'table_header' => 'Payment ID',
        ];
    }
    public static function getAllowedCurrency($currency_ids = []){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        $currencies = (new Currency())->getAll();
        if(Arr::isAMemberOf($brand_code, $brand_list)) {
            $currencies = array_intersect_key( $currencies, array_flip( $currency_ids ) );
        }
        return $currencies;
    }

    public static function isDisallowSendMoneyForRevenue()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isPaymentItemParamOptional(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function allowMaskReceiverName(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function getConfirmPhoneValidationMessage(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        if(Arr::isAMemberOf($brand_code, $brand_list)) {
            return "Please enter valid information.";
        }
        return "The confirm phone and phone must match";
    }

    public static function getUniqueEmailValidationErrorMessage(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        if(Arr::isAMemberOf($brand_code, $brand_list)) {
            return "Invalid information, please check and re-enter";
        }
        return "The information you entered does not match our records";
    }


    public static function addNationalityToUserVerify(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        $status = Arr::isAMemberOf($brand_code, $brand_list);

        if($brand_code == config('constants.BRAND_NAME_CODE_LIST.PP') && Helper::isProdServerEnvironment()){
            $status = false;
        }else if($brand_code == config('constants.BRAND_NAME_CODE_LIST.SR') && Helper::isProdServerEnvironment()){
            $status = false;
        }
        return $status;
    }

    public static function requestMoneyEmailContentChanges(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return in_array($brand_code, $brand_list);
    }

	public static function allowCardService()
	{
		$brand_code = config('brand.name_code');
		$brand_list = [
//			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
		];
		return in_array($brand_code, $brand_list);
	}

    public static function isVknAndTaxNumberSame()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowTaxOfficeName()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowFinflowIbanValidator(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function allowSoftroboticsIbanValidator(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowUserExportXls()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function userWrongPasswordMailContent ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function userAgeRestrictionOnKycUpdate()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowSupportTicketEmailReceivers()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.VP'),
            config('constants.BRAND_NAME_CODE_LIST.SP')
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function allowOnlyNumericForDplPhone()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

	public static function allowSimCardBlock(){

		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
		];

		$status = Arr::isAMemberOf($brand_code, $brand_list);

		if($brand_code == config('constants.BRAND_NAME_CODE_LIST.SR') && Helper::isProdServerEnvironment()){
			$status = false;
		}

		return $status;

	}

    public static function disableAuthSurnameOnMerchantInformation()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function hideDenizBankCardProgram()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }

	public static function fixedWalletPanelApiOtp(){

		$brand_code = config('brand.name_code');
		$brand_list = [
			//config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.PL'),
		];
		return in_array($brand_code, $brand_list);

	}


    public static function applyCaptchaRulesForAdminAndMerchant() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
	        config('constants.BRAND_NAME_CODE_LIST.HP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }


    public static function isAllowedJWTService ()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return in_array($brand_code, $brand_list);
    }



    public static function isAllowPhoneNumberMask()
    {
        $brand_code = config('brand.name_code');

        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isShowFormattedPhoneNumber()
    {
        $brand_code = config('brand.name_code');

        $brand_list = [
            //config('constants.BRAND_NAME_CODE_LIST.PM'),
        config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isChangedUserRecentActivityUI (): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function showComRowOnUserRecentActivityUI (): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PL'),
	        config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowProcessSaleBackup()
    {
        return true;
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowSequentialCustomerId()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowDiffDisclaimerForWithdraw() :bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isIgnoredBankAccountNo(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isHideGenderForWallet() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowedServiceCredentials (): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function changeMoneyTransferSmsAndEmailContent() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function welcomeMailNewContentForMerchantV1(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.QP'),
            config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isAllowMerchantIbanChangerEmailAlert() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function hideMerchantExportedReportColumn(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function isCustomizedOtpMessage(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            //config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
//            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.PL')
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function changeMerchantStausTcknVknDigit() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isTakeCommissionFromSenderOrReceiver()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
           /* config('constants.BRAND_NAME_CODE_LIST.PL'),*/
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

	public static function disableSplitAccountConfirmationSms(){
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}

     public static function isShowCommissionRateTransaction()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowRandomCustomerIdWithoutPrefix()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function securityImageForResetPasssword(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];
        return in_array($brand_code, $brand_list);
    }

    public static function changeMoneyTransferSms() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function maskNameSurnameOnMoneyTransfer() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

	public static function allowCustomMonthlyComissionsFooter(): bool
	{
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}

	public static function allowRefundedTransactionStatusChange(): bool
	{
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}

    public static function otpChannelEnableForBothSmsAndEmail() : bool
    {
        return true;
        /*$brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);*/
    }

    public static function allowMerchantNameInEnglishOnly(): bool
	{
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.SP'),
			config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}


	public static function enableCustomEmailFooterImage(): bool
	{
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}

	public static function customSubjectForMailToOperationTeam (): bool
	{
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}

    public static function customizedFailedLoginApiResponse (): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

	public static function disableCardTransactionsView(){
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}
    public static function disabledKYCVerifiedOption(){
            $brand_code = config('brand.name_code');
            $brand_list = [
//                config('constants.BRAND_NAME_CODE_LIST.FL'),
                config('constants.BRAND_NAME_CODE_LIST.PP'),
                config('constants.BRAND_NAME_CODE_LIST.SR'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

	public static function merchantInformationMasking(){
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.YP'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}

    public static function userDashboardActivityDisplayAmountChange(){
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}

    public static function isAllowSanctionScannerUserVerification(): bool
    {
		$brand_code = config('brand.name_code');
		$brand_list = [
			//config('constants.BRAND_NAME_CODE_LIST.FL'),
//            config('constants.BRAND_NAME_CODE_LIST.PP'),
            //config('constants.BRAND_NAME_CODE_LIST.SR'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}

    public static function changeUserDashboardActivityListApiResponseForCommission(){
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}

    public static function getEmailVerifyTokenExpireTime() : int
    {
        $brand_code = config('brand.name_code');
        $expire_time = self::EMAIL_VERIFIY_TOKEN_EXPIRE;

        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];

        if(Arr::isAMemberOf($brand_code, $brand_list)){
            $expire_time = 2880; // two days
        }

        return $expire_time;
    }


    public static function isAllowMerchantMonthlyFee() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function allowMaskingAtTransactions(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
	         config('constants.BRAND_NAME_CODE_LIST.PM'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function allowAllTransactionReportThroughApi(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }


	public static function allowWithdrawalApprovalInRequest ($user_type): bool
    {   /*
         * while requesting withdrawal, after requesting successfully
         * process withdrawal will be called automatically
         */
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.YP'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list) && ($user_type == User::CUSTOMER);
	}


    public static function isAllowDepositByCreditCardKycZero($auth_user): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];

        $status = true;
        if (!empty($auth_user) && $auth_user->user_category == User::NOT_VERIFIED && Arr::isAMemberOf($brand_code, $brand_list)) {
            $status = false;
        }
        return $status;
    }

	public static function allowAddingMoneyTransferExtraCommission(): bool
	{
		$brand_code = config('brand.name_code');
		$brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}
    public static function userDepositAndWithdrawalSmsAndMailContent(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowedUserVerifyEmailFormAdmin(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function allowStoreMerchantProductPriceWhileTransaction(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

	public static function enableCustomerNumberDisplayOnWalletPanelReceipts(): bool
	{
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}

	public static function disableDepositMethodForKYC0User(): bool
	{
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}
	public static function allowRegisterUserDeposit(): bool
	{
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}


	public static function allowTcknDisplayOnAdminPanel(): bool
	{
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}

    public static function isAllowImportExportMerchantPosCommission() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
	        config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
	public static function isNewFlowIntegratorCommission(): bool
	{
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.SP'),
			config('constants.BRAND_NAME_CODE_LIST.VP'),
			config('constants.BRAND_NAME_CODE_LIST.PC'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}

	public static function allowChangePhoneNumberFlashMessage(): bool
	{
		$brand_code = config('brand.name_code');
		$brand_list = [
			config('constants.BRAND_NAME_CODE_LIST.FL'),
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
		];
		return Arr::isAMemberOf($brand_code, $brand_list);
	}

    public static function isAllowedForInstallmentLimitationsForCommercialCards(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }


    public static function call($classMethodMap, ...$args)
    {
        return call_user_func_array([$classMethodMap[0], $classMethodMap[1]], $args);

    }

    public static function isAllowForMerchantInfoMapping(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isApiResponseLocalize()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
           config('constants.BRAND_NAME_CODE_LIST.PB')
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }


}
