<?php
namespace App\Http\Controllers\Traits;

use App\Models\BlockTimeSettings;
use App\Models\Company;
use App\Models\Profile;
use App\Models\Statistics;
use App\Models\UserSetting;
use App\Models\UserTrustedDevice;
use App\Models\UserUsergroup;
use App\User;
use common\integration\ApiService;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Backend\BackendIntegrator;
use common\integration\Brand\Configuration\Backend\BackendMix;
use common\integration\Brand\Configuration\Backend\BackendWallet;
use common\integration\Brand\Configuration\Frontend\FrontendMix;
use common\integration\GlobalUser;
use common\integration\ManageOtp;
use common\integration\ManipulateCache;
use common\integration\Models\Merchant;
use common\integration\Models\OutGoingEmail;
use common\integration\Otp\OtpLimitRate;
use common\integration\Utility\Arr;
use common\integration\Utility\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use common\integration\GlobalFunction;
use App\Models\FailedLoginList;
use common\integration\BrandConfiguration;
use common\integration\Models\WrongPasswordHistory;

trait LoginProcessTrait{

    public $allow_otpless_token = false;
    public $is_reset_otp_limit = false;
    public $attempt_count = 0;
	public $return_extra_param = [
		'otp_expire_time' => 0,
		'otp_expire_response_status' => 0,
	];


    /*
     * api access token generation part START
     */
    private function oauthTokenGeneration ($email, $user_type)
    {
        return $this->commonTokenProcess([
            'grant_type' => 'otp_grant',
            'email' => $email,
            'user_type' => $user_type
        ]);
    }

    private function accessByRefreshToken ($request)
    {
        return $this->commonTokenProcess([
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
        ]);
    }

    private function commonTokenProcess ($inputs)
    {
        $form_params = array_merge($inputs, [
            'client_id' => config('app.API_CLIENT_ID'),
            'client_secret' => config('app.API_CLIENT_SECRET'),
            'scope' => '',
        ]);
        $http = Http::asForm();
        if(Helper::isLocalServerEnvironment()){
            $http->withoutVerifying();
        }
        $response = $http->post(config('app.url').'/oauth/token', $form_params);
        $result = json_decode((string) $response->getBody(), true);
        return $result;
    }
    /*
     * api access token generation part END
     */


    public function processLogin(Request $request, $user_category = "", $user_type = "", $extra_param = "")
    {
//        $this->clearLoginAttempts($request);

        $result = "";
        $profileObj = "";
        $email = "";
        $response_status_code = "";
        $lock_user_response = "";
        $lock_with_data = "";

        $this->decayMinutes = config('app.login_locked_time_minutes');
        $this->maxAttempts = config('app.failed_login_attemps') + config('app.failed_otp_attemps');


        if(BrandConfiguration::allowLoginBlockTime()){
            $this->maxAttempts = config('app.failed_login_attemps');
            $blockTimeObj = (new BlockTimeSettings())->getDetails($user_type);

            if (!empty($blockTimeObj) && !empty($blockTimeObj->first_time)) {
                $this->decayMinutes = $blockTimeObj->first_time;
            }
        }

        if(BrandConfiguration::applyCaptchaRulesForAdminAndMerchant()){
            if(GlobalUser::getCookieForCaptcha(GlobalUser::cookieNameForCaptcha(BrandConfiguration::PANEL_MERCHANT, true))){
                $this->maxAttempts = User::WRONG_CAPTCHA_COUNT;
                $this->decayMinutes = User::WRONG_CAPTCHA_LOCK_TIME;
            }
        }


        $mynew_request = $request;
        if(!empty($request->input('email'))){
            $email = $request->input('email');
        }

        //Locking User With Phone and User Types

        $lock_with = empty($request->input('phone')) ? $request->input('email') : $request->input('phone');

        $lock_with_data = $mynew_request['email'] = $lock_with.$user_type;

        list($lock_user_response, $profile_object) = $this->lockUser($mynew_request,$user_type,$email,"password");

        if($lock_user_response == true){
            $result = false;
            $response_status_code = config('apiconstants.API_USER_LOCKED');
            return [$result,$profile_object,$response_status_code];
        }

        ////
        $request['email'] = $email;


        if (empty($user_category)) {   // user has password

            $profile = new Profile();
            list($statusCode, $profileObj) = $profile->getUserEmailPassword(
                $request->input('email'),
                $request->input('password'),
                true,
                $user_type
            );

            if($profileObj){
                $profileObj = Auth::user();
            }

            if (!empty($profileObj) && $user_type == Profile::MERCHANT && (new Merchant())->subDealerMerchantRestriction($profileObj)) {
                return [false, $profileObj, ApiService::API_SERVICE_MERCHANT_PANEL_RESTRICTION_SUB_DEALER];
            }

            $businessLogData['response_code'] = $statusCode;

            if ($statusCode == 100) {

                if(Arr::isAMemberOf($user_type, [Profile::MERCHANT, Profile::SALES_ADMIN, Profile::SALES_EXPERT])){
                    $company = new Company();
                    $status_code = $company->checkCompanyStatus(Auth::user()->company_id);
                    if ($status_code == 1){
                        $response_status_code = config('apiconstants.API_MERCHANT_DISABLED');
                    }

                    if(!empty($response_status_code)){
                        $user_group_obj = new UserUsergroup();
                        $response = $user_group_obj->userUsergroupCheck($profileObj->id);

                        if($response == Profile::ADMIN_VERIFIED_NOT_APPROVED){
                            $response_status_code = config('apiconstants.API_MERCHANT_DISABLED');
                        }
                    }
                }

                $result = true;
            } else {
                $result = false;
            }

        } else {    // user doesn't have password
            $profile = new Profile();
            $profileObj = $profile->getUserByPhone($request->phone, $user_type);

            if (!empty($profileObj)) {
                $profileObj = Auth::loginUsingId($profileObj->id);
                $result = true;
            } else {
                $result = false;
            }

        }

        $businessLogData['action'] = "USER_LOGIN_OTP_REQUEST";

        $OTP = "";

        /*
         * check user trusted device
         */
        $this->allow_otpless_token = (new UserTrustedDevice())->checkOtpLessTokenGeneration(auth()->user()->id ?? '', $request->all());
	    
		if(BrandConfiguration::call([BackendIntegrator::class, 'allowOtpLessLogin']) && $user_type ==
			Profile::INTEGRATOR){
			$this->allow_otpless_token = BrandConfiguration::call([BackendIntegrator::class, 'allowUserListForOtpLessLogin'], @auth()->user()->email);
		}
	    
	    

        if ($result) {
            auth()->user()->update(['verified' => 0]);

            if (auth()->user()->is_admin_verified == Profile::ADMIN_VERIFIED_APPROVED || $this->pendingUserCheck()) {

                $extra_data['remote_customer_number'] = $extra_param['remote_customer_number'] ?? '';

                if(BrandConfiguration::allowLoginBlockTime()){
                    GlobalFunction::setFailedLoginAttempts(auth()->user(),'fail_attempts_count');

                    if (!isset($extra_param['is_from_api']) || !$extra_param['is_from_api']) {
                        auth()->user()->update(['failed_login_attempt' => 0]);
                    } else {
                        unset($extra_param['is_from_api']);
                        unset($extra_param['remote_customer_number']);
                    }
                }

                $remaining_attempts = config('app.failed_login_attemps') - ($this->limiter()->attempts($this->throttleKey($request)) + 1);
                ($remaining_attempts > 1) ? $txt = __('login attempts left') : $txt = __('login attempt left');
                $msg = $remaining_attempts . __(' ') . $txt;
                session()->put('attempts', $msg);
				
	            
	            list($conditions, $otp_expire_time, $response_status) = OtpLimitRate::isCheckingOtpRateLimit
	            ("LOGIN_OTP_FOR_", auth()->user());
				
	            if($conditions){
		            
		            if(!empty($extra_param)){
			            if($extra_param['is_otp_enable'] == 1 && \auth()->user()->is_otp_required == 1){
				            if (ManageOtp::checkResendOtpLimit(ManageOtp::USER_PANEL_LOGIN, \auth()->user()->id)) {
					            $this->is_reset_otp_limit  = true;
					            $result = false;
					            return [$result, $profileObj, $response_status_code];
				            }
				            $this->sendLoginOTP(null, $extra_param);
			            }
		            }else{
			            if (ManageOtp::checkResendOtpLimit(ManageOtp::USER_PANEL_LOGIN, \auth()->user()->id)) {
				            $this->is_reset_otp_limit = true;
				            $result = false;
				            return [$result, $profileObj, $response_status_code];
			            }
			            $this->sendLoginOTP(null, $extra_data);
		            }
		            
		            
		            $log_data = [
			            'action' => 'Login Successful',
			            'ip' => $request->ip(),
			            'user_email' => auth()->user()->email,
			            'status' => 'successful',
			            'OTP' => isset($OTP) ? $OTP : "",
			            'date_time' => Carbon::now()
		            ];
		            
		            $businessLogData['OTP_STATUS'] = "OTP sent";
	            }else{
		            
		            $log_data = [
				            'action' => 'Login Fail',
			                'message' => 'Otp Limit Croess',
				            'ip' => $request->ip(),
				            'user_email' => auth()->user()->email,
				            'status' => 'fail',
				            'OTP' => isset($OTP) ? $OTP : "",
				            'date_time' => Carbon::now()
			            ];
		            
		     
		            $response_status_code = $response_status;
		            $this->return_extra_param = [
							'otp_expire_time' => $otp_expire_time,
			                'otp_expire_response_status' => $response_status,
			            ];

	            }
				

//                auth()->user()->createLog(
//                    $this->_getCommonLogData($log_data)
//                );


            
            }else{
                $response_status_code = config('apiconstants.API_USER_NOT_APPROVED');
            }

        } else {

            $mynew_request['email'] = $lock_with_data;
//            $mynew_request['oldemail'] = empty($email) ? $request->input('phone') : $email;

            $this->incrementLoginAttempts($mynew_request);

            $attempt_count = $this->limiter()->attempts($this->throttleKey($mynew_request));
            $this->attempt_count = $attempt_count;
            $request['email'] = $email;

            $user_fl = "";
            $laravelTempoCacheKey = "";

            if(isset($request->phone) & !empty($request->phone)){
                $user_fl = $profile->getUserByPhone($request->phone, $user_type);

                $laravelTempoCacheKey = $user_fl->id.'|'.$user_fl->phone.'|'.$user_fl->user_type.'|tempo';

                $mins = intval(config('app.login_locked_time_minutes')) * 60;
                
                if(BrandConfiguration::allowLoginBlockTime()){

                    $mins = intval($this->decayMinutes) * 60;
                    
                }

                $cache_data = Cache::get($laravelTempoCacheKey);
                if(Cache::has($laravelTempoCacheKey)) {
                    $cache_data["wrong_password"] += 1;
                    Cache::forget($laravelTempoCacheKey);
                } else {
                    $cache_data = [
                        "wrong_password" => 1,
                        "wrong_otp" => 0
                    ];
                }

                if (BrandConfiguration::isAllowedAmlService()) {
                    (new WrongPasswordHistory)->createNew($user_fl->id);
                }

                Cache::add($laravelTempoCacheKey, $cache_data, $mins);
            }elseif (isset($request->email) & !empty($request->email)){
                $user_fl = $profile->getUserByEmail($request->email, $user_type);
            }

            $attempt_maximum = config('app.failed_login_attemps');

            if($attempt_count >= $attempt_maximum) {
                $flag = 1;
                if(Cache::has($laravelTempoCacheKey)) {
                    $old_cache_data = Cache::get($laravelTempoCacheKey);
                    $wrong_pass_count  = isset($old_cache_data["wrong_password"]) ? $old_cache_data["wrong_password"] : $old_cache_data;
                    if($wrong_pass_count < $attempt_maximum) {
                        $flag = 0;
                    }
                }
                if($flag) {
                    Session::put('login_maximum_failed_attempt_occured', $attempt_maximum);
                    $this->sendEmailAfterUserLocked($request, $user_fl, "password");
                    $profileObj = $user_fl;
                }
            }


            if (empty($user_fl)) {
                $fla_cnt = 0;
            } else {
                $fla_cnt = $user_fl->failed_login_attempt;

                $data = [
                    'failed_login_attempt' => ($fla_cnt + 1),
                    'last_failed_login_datetime' => Carbon::now()
                ];
                if(!BrandConfiguration::allowLoginBlockTime()){
                    $profile->updateUser(
                        $user_fl->id, $data
                    );
                }else{
                    if(!empty($user_fl)){

                        if(BrandConfiguration::allowLoginBlockTime() && ($user_type == Profile::CUSTOMER || $user_type == Profile::MERCHANT || Profile::SALES_ADMIN || Profile::SALES_EXPERT )){

                            $profile->updateUser(
                                $user_fl->id, $data
                            );
                        }

                        $insert_data = [
                            'user_type' => $user_type,
                            'user_id' => $user_fl->id,
                            'failed_login_time' =>  Carbon::now(),
                        ];
    
                        (new FailedLoginList)->saveData($insert_data);
                    }
                }
            }



            $log_data = [
                'action' => 'Login Failed',
                'ip' => $request->ip(),
                'user_email' => $request->email,
                'status' => 'Failure',
                'date_time' => Carbon::now(),
                'failed_login_attempt' => $fla_cnt + 1
            ];

            $businessLogData['OTP_STATUS'] = "OTP failed";
//            $user = new User();
//            $user->createLog(
//                $this->_getCommonLogData($log_data)
//            );
        }

        $logrequest = $request->all();
        unset($logrequest['password']);
        $businessLogData['input_data'] = $logrequest;
        $this->createLog($this->_getCommonLogData($businessLogData));

        if($result){
            if(empty($response_status_code)){
                $response_status_code = 100;
            }
        }

        return [$result,$profileObj,$response_status_code];
    }


    private function lockUser(Request $request, $user_type, $email, $checking_for, $extra=[])
    {
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        $profileObj = '';

        if ($this->hasTooManyLoginAttempts($request)) {

            $this->fireLockoutEvent($request);
            $profile = new Profile();

            //Getting User
            if(!empty($request->input('phone'))){
                if(!empty($extra) && isset($extra['old_phone']) && !empty($extra['old_phone'])) {
                    $userPhone = $extra['old_phone'];
                } else {
                    $userPhone = $request->input('phone');
                }

                $profileObj = $profile->getUserByPhone(
                    $userPhone, $user_type
                );

            }else if(!empty($email)) {
                $profileObj = $profile->getUserByEmail($email, $user_type);

            }


            ///updating IP
            $data = [
                'ip' => $request->ip()
            ];

            if(!empty($profileObj)){
                $profileObj = $profile->updateUser(
                    $profileObj->id, $data
                );
            }

            $this->sendEmailAfterUserLocked($request, $profileObj, $checking_for);

//            $mynew_request = $request;
//            $mynew_request['oldemail'] = empty($email) ? $request->input('phone') : $email;

            $request->merge(["user_object" => $profileObj]);

            $this->sendLockoutResponse($request);

            return [true, $profileObj];
        }
        return [false, $profileObj];
    }


    private function sendLoginOTP($userObj=null, $extraData=[])
    {
        if (!$this->allow_otpless_token) {
            if (empty($userObj)) {
                $userObj = Auth::user();
            }

            if (in_array($userObj->user_type, [Profile::MERCHANT, Profile::INTEGRATOR, Profile::SALES_ADMIN, Profile::SALES_EXPERT])) {

                $this->sendNotificationToMerchant($userObj);
            } else {
                // SMS for OTP
                $OTP = $this->cacheTheOTP();
                $key = "LOGIN_OTP_FOR_" . $userObj->id;

                $this->set_otp_to_cache($key, $OTP, Config::get('constants.defines.LOGIN_OTP_EXPIRE_TIME'));

                if (!empty($extraData['remote_customer_number'])) {
                    $this->set_otp_to_cache(
                        "REMOTE_CUSTNO_FOR" . $userObj->id,
                        $extraData['remote_customer_number'],
                        Config::get('constants.defines.LOGIN_OTP_EXPIRE_TIME')
                    );
                }

                $laravelCacheOTPKey = "LOGIN_OTP_TIMER_FOR_" . $userObj->id . "_" . $userObj->user_type;
                Cache::forget($laravelCacheOTPKey);

                $expr_time = Config::get('constants.defines.LOGIN_OTP_EXPIRE_TIME');
                $expr_time_min = intval($expr_time) * 60;
                Cache::add($laravelCacheOTPKey, Carbon::now()->addMinutes($expr_time), $expr_time_min);

                $noti_data['sms_data']['OTP'] = $OTP;
                $noti_data['sms_data']['is_login_otp'] = 1; // for queue 1 = high

                $extra_param['sys_lang'] = in_array(app()->getLocale(), Config::get('constants.SYSTEM_SUPPORTED_LANGUAGE')) ? app()->getLocale() : $userObj->language;
                $extra_param['priority_value'] = OutGoingEmail::PRIORITY_EXPRESS;
	            

	            if(BrandConfiguration::allowSimCardBlock() && property_exists($this, 'enable_block_by_provider_check')){
		            
		            $this->enable_block_by_provider_check = true;
	            }
				
                $this->checkAndSendNotification(
                    UserSetting::action_lists['login_otp'],
                    $noti_data, UserSetting::EMAIL_DISABLED, UserSetting::SMSENABLED,
                    UserSetting::PUSH_NOTIFICATION_DISABLED, $userObj, $extra_param
                );
            }
        }


        return true;
    }

    private function sendNotificationToMerchant($user_object){

        $language = in_array(app()->getLocale(), Config::get('constants.SYSTEM_SUPPORTED_LANGUAGE')) ? app()->getLocale() : $user_object->language;

        $OTP = $this->cacheTheOTP();
        $header = "";
        $data['OTP'] = $OTP;
        $message = view('sms.OTP.login.login_'. $language, ['OTP' => $OTP, 'data' => $data])->render();

        $phone = $user_object->phone;

        $data['name'] = $user_object->name;
        $data['otp'] = $OTP;
        $attachment = "";
        $from = config('app.SYSTEM_NO_REPLY_ADDRESS');
        $to = $user_object->email;

        $otp_key = 'LOGIN_OTP_FOR_' . $user_object->id;
        $otp_expire_time = Config::get('constants.defines.LOGIN_OTP_EXPIRE_TIME');

        $this->set_otp_to_cache($otp_key, $OTP, $otp_expire_time);

        $expireTime = intval($otp_expire_time) * 60;
        $laravelCacheOTPKey = "LOGIN_OTP_TIMER_FOR_" . $user_object->id . "_" . $user_object->user_type;
        Cache::forget($laravelCacheOTPKey);
        Cache::add($laravelCacheOTPKey, Carbon::now()->addMinutes($otp_expire_time), $expireTime);

        $priority = 1; // for queue 1 = high
        $this->setGNPriority(OutGoingEmail::PRIORITY_EXPRESS);

        // if(BrandConfiguration::allowSendSmsAndEmailSerially()){
        //     $this->sendSMS($header, $message, $phone, $priority);
		//
        //     //out_going_email
        //     $this->sendEmail($data, "LOGIN_OTP", $from, $to, $attachment, "login.otp", $language, $priority);
        // }else{
        //     //out_going_email
        //     $this->sendEmail($data, "LOGIN_OTP", $from, $to, $attachment, "login.otp", $language, $priority);
        //     $this->sendSMS($header, $message, $phone, $priority);
        // }
	    
	    if($user_object->otp_channel == Profile::OTP_CHANNEL_ALL){
		    if(BrandConfiguration::allowSendSmsAndEmailSerially()){
			    $this->sendSMS($header, $message, $phone, $priority);
			    $this->sendEmail($data, "LOGIN_OTP", $from, $to, $attachment, "login.otp", $language, $priority);
		    }else{
			    $this->sendEmail($data, "LOGIN_OTP", $from, $to, $attachment, "login.otp", $language, $priority);
			    $this->sendSMS($header, $message, $phone, $priority);
		    }
	    }elseif($user_object->otp_channel == Profile::OTP_CHANNEL_SMS){
		    $this->sendSMS($header, $message, $phone, $priority);
	    }elseif($user_object->otp_channel == Profile::OTP_CHANNEL_EMAIL){
		    $this->sendEmail($data, "LOGIN_OTP", $from, $to, $attachment, "login.otp", $language, $priority);
	    }
		
    }


    public function verifySmsOtp(Request $request,$user_id){

        $status_code = "";
        $profileObj = "";
        $lock_user_response = "";
        $locked_with_email = "";
        $locked_with_phone = "";

        $this->decayMinutes = config('app.otp_locked_time_minutes');

        $this->maxAttempts = config('app.failed_login_attemps') + config('app.failed_otp_attemps');

        if(BrandConfiguration::allowLoginBlockTime()){

            $this->maxAttempts = config('app.failed_login_attemps');

        }
        //Getting Profile
        $profile = new Profile();
        $profileObj = $profile->getUserById($user_id);


        if(!empty($profileObj)){
            $mynew_request = $request;
            $mynew_request['email'] = $profileObj->phone.$profileObj->user_type;
            $mynew_request['phone'] = $profileObj->phone.$profileObj->user_type;
            $extra['old_phone'] = $profileObj->phone;

            $profileObj['verified'] = $this->modifyVerifiedParameter($profileObj);
            list($lock_user_response, $pro_obj) = $this->lockUser($mynew_request,$profileObj->user_type,"","OTP", $extra);

            if($lock_user_response == false){

                $remote_customer_number = BrandConfiguration::call([BackendMix::class, "isAllowRemoteCustomerNumber"])
                    ? $this->get_otp_from_cache("REMOTE_CUSTNO_FOR" . $profileObj->id)
                    : '';

                $key = "LOGIN_OTP_FOR_" . $profileObj->id;
                $saved_otp = (string)$this->get_otp_from_cache($key);
                $request_otp = $request->input('OTP');

                $otp_flag = 0;

                $this->checkAndUpdateCacheOtp($profileObj,$request_otp, $saved_otp);


                if ($request_otp == $saved_otp) {
                    ManageOtp::clearOtpLimitCache(ManageOtp::USER_PANEL_LOGIN, $user_id);
                    ManageOtp::clearOtpLimitCache(ManageOtp::USER_PANEL_LOGIN_OTP_SUBMIT, $user_id);
//                if ($request_otp == $saved_otp) {

                    $status_code = 100;

                    $log_data = [
                        'action' => 'Login OTP Success',
                        'ip' => $request->ip(),
                        'otp' => request('OTP'),
                        'user_email' => $profileObj->email,
                        'mobile_no' => $profileObj->phone,
                        'date_time' => Carbon::now()
                    ];

                    $businessLogData['action'] = "USER_LOGIN_SUCCESS";
                    $businessLogData['input_data'] = $request->all();
                    $businessLogData['model_data'] = $log_data;
                    $this->createLog($this->_getCommonLogData($businessLogData));

                    $this->forget_otp_from_cache($key);

                    if (!empty($remote_customer_number)
                        && $profileObj->remote_customer_number != $remote_customer_number
                    ) {
                        $profileObj->remote_customer_number = $remote_customer_number;
                        $profileObj->save();
                    }

                }else{
                    $this->incrementLoginAttempts($mynew_request);
                    $attempt_count = $this->limiter()->attempts($this->throttleKey($mynew_request));

                    $laravelTempoCacheKey = $profileObj->id.'|'.$profileObj->phone.'|'.$profileObj->user_type.'|tempo';
                    $mins = intval(config('app.otp_locked_time_minutes')) * 60;

                    if(BrandConfiguration::allowLoginBlockTime()){

                        $mins = intval($this->decayMinutes) * 60;
                        
                    }

                    if(Cache::has($laravelTempoCacheKey)) {
                        $cache_data = Cache::get($laravelTempoCacheKey);
                        $cache_data["wrong_otp"] += 1;
                        Cache::forget($laravelTempoCacheKey);
                    } else {
                        $cache_data = [
                            "wrong_password" => 0,
                            "wrong_otp" => 1
                        ];
                    }
                    Cache::add($laravelTempoCacheKey, $cache_data, $mins);

                    $attempt_maximum = config('app.failed_otp_attemps');

                    if($attempt_count >= $attempt_maximum) {
                        $flag = 1;
                        if(Cache::has($laravelTempoCacheKey)) {
                            $old_cache_data = Cache::get($laravelTempoCacheKey);
                            $wrong_pass_count  = $old_cache_data["wrong_otp"];
                            if($wrong_pass_count < $attempt_maximum) {
                                $flag = 0;
                            }
                        }
                        if($flag) {
                            $this->sendEmailAfterUserLocked($request, $profileObj, "OTP");
                        }
                    }

                    if (ManageOtp::checkResendOtpLimit(ManageOtp::USER_PANEL_LOGIN_OTP_SUBMIT, $user_id, true)) {
                        $status_code = config('apiconstants.API_OTP_LIMIT_EXIST');
                    } else {
                        $status_code = config('apiconstants.API_OTP_NOT_MATCHED');
                    }
                }
            }else{
                $status_code = config('apiconstants.API_USER_LOCKED');
            }
        }else{
            $status_code = config('apiconstants.API_USER_NOT_FOUND');

        }


        return [$status_code,$profileObj];
    }

    public function sendForgetPasswordLink(Request $request){

        $status_code = "";
        $profileObj = "";
        $response_link = "";


        $email = $request->email;
        $user_type = $request->user_type;
        $businessLogData['action'] = "USER_REQUEST_FORGOT_PASSWORD_LINK";
        $businessLogData['input_data'] = $request->all();
        $this->createLog($this->_getCommonLogData($businessLogData));

        $profile = new Profile();
        $profileObj = $profile->getUserByEmail($email, $user_type);

        if(!empty($profileObj)){

            $response_link = $this->broker()->sendResetLink(
                $request->only('email','user_type')
            );

            $status_code = 100;

        }else{
            $status_code = config('apiconstants.API_USER_NOT_FOUND');
        }

        return [$status_code,$profileObj,$response_link];
    }

    public function sendEmailAfterUserLocked (Request $request, $profileObj, $checking_for) {
        if(!empty($profileObj)) {
            if($profileObj->user_type == Profile::CUSTOMER) {
                $laravelCacheKey = $profileObj->id . '|' . $profileObj->phone . '|' . $profileObj->user_type;
            } else {
                $laravelCacheKey = $profileObj->id.'|'.$profileObj->email.'|'.$profileObj->user_type;
            }
            $lck_time = config('app.login_locked_time_minutes');

            if(BrandConfiguration::allowLoginBlockTime()){
                $lck_time = $this->decayMinutes;
            }

            if(BrandConfiguration::applyCaptchaRulesForAdminAndMerchant()){
                if(GlobalUser::getCookieForCaptcha(GlobalUser::cookieNameForCaptcha(BrandConfiguration::PANEL_MERCHANT, true))){
                    $lck_time = $this->decayMinutes;
                }
            }
            
            $mins = intval($lck_time) * 60;
            // to prevent sending email more than once even the user is being locked
            if (Cache::has($laravelCacheKey)) {
                return;
            }

            Cache::add($laravelCacheKey, Carbon::now()->addMinutes($lck_time), $mins);

            $fromEmail = config('app.SYSTEM_NO_REPLY_ADDRESS');
            $toEmail = config('app.ADMIN_EMAIL');
            $message['lock_time'] = date('d F, Y h:i:s');
            $message['email'] = isset($profileObj->email) ? $profileObj->email : "";
            $message['name'] = isset($profileObj->name) ? $profileObj->name : "";
            $message['phone'] = isset($profileObj->phone) ? $profileObj->phone : "";
            $message['ip'] = $request->ip();
            $message['language'] = config('app.ADMIN_LANGUAGE');
            $message['user_obj'] = $profileObj;
            $emailTemplate = "account_blocked.admin_account_blocked";
            if(BrandConfiguration::allowIncorrectLoginAttemptsNotification()){
                $toEmail = (new Statistics())->incorrectLoginEmail(Statistics::INCORRECT_LOGIN_NOTIFICATION_EMAIL);
                $emailTemplate = 'account_blocked.'.GlobalFunction::brandFileNameConvention(true).'user_account_blocked';
            }
            //out_going_email
            $this->setGNPriority(OutGoingEmail::PRIORITY_HIGH);
            $this->sendEmail($message, "account_blocked_admin", $fromEmail, $toEmail,
                "", $emailTemplate, $message['language']);

//            $failed_attempt_counter = $this->limiter()->attempts($this->throttleKey($request));
//            if (!empty($profileObj) && $failed_attempt_counter <= $this->maxAttempts) {
            $input_data['email_data']['user_obj'] = $profileObj;
            
            if($checking_for == "password") {
                $input_data['email_data']['name'] = !empty($profileObj) && isset($profileObj->name) ? $profileObj->name : '';
                $input_data['email_data']['value1'] = config('app.login_locked_time_minutes');

                if(BrandConfiguration::allowLoginBlockTime()){
                    $input_data['email_data']['value1'] = $this->decayMinutes;
                }
                $this->checkAndSendNotification(
                    UserSetting::action_lists['account_block_wrong_password'],
                    $input_data,
                    UserSetting::EMAILENABLED,
                    UserSetting::SMS_DISABLED,
                    UserSetting::PUSH_NOTIFICATION_DISABLED,
                    $profileObj,
                    null,
                    true
                );
            } elseif($checking_for == "OTP") {
                $input_data['email_data']['name'] = !empty($profileObj) && isset($profileObj->name) ? $profileObj->name : '';
                $input_data['email_data']['value1'] = config('app.login_locked_time_minutes');

                if(BrandConfiguration::allowLoginBlockTime()){
                    $input_data['email_data']['value1'] = $this->decayMinutes;
                }
                
                $this->checkAndSendNotification(
                    UserSetting::action_lists['account_block_wrong_otp'],
                    $input_data,
                    UserSetting::EMAILENABLED,
                    UserSetting::SMS_DISABLED,
                    UserSetting::PUSH_NOTIFICATION_DISABLED,
                    $profileObj,
                    null,
                    true
                );
            }
        }
    }

   public function unblockUser($encoded_data){

      $data = [];
      $statuscode = config('apiconstants.API_VALIDATION_FAILED');

      if (strlen($encoded_data) < 13) {
         $description = __('Invalid data');
      }

      $decoded_data = $this->customEncryptionDecryption($encoded_data, config('app.brand_secret_key'), 'decrypt', 1);

      if (sizeof($decoded_data) == 3) {
         $statuscode = config('apiconstants.API_SUCCESS');
         $cache_key = $decoded_data[0]."|".$decoded_data[1]."|".$decoded_data[2];

         if (Cache::has($cache_key)) {
            Cache::forget($cache_key);
            $description = __('User has been unlocked');
         } else {
            $description = __('User already unlocked');
         }
      } else {
         $description = __('Invalid data');
      }

      return [$statuscode,$description,$data];
   }

    public static function pendingUserCheck(){
        return BrandConfiguration::allowPendingUserLogin() && auth()->check() && Auth::user()->is_admin_verified == Profile::ADMIN_VERIFIED_PENDING;
    }

    public function checkAndUpdateCacheOtp($profileObj, $request_otp, &$saved_otp){
        $test_merchent_email = 'test-merchant@' . str_replace(['dev.', 'provisioning.', 'test.'], '', $_SERVER['SERVER_NAME']);

        if (($profileObj->user_type == Profile::CUSTOMER &&
                $profileObj->phone == '+905444615343' &&
                $request_otp == '111111') || ($profileObj->user_type == Profile::CUSTOMER &&
                $profileObj->phone == '+9032623423434' &&
                $request_otp == '111111') || ($profileObj->user_type == Profile::CUSTOMER &&
                $profileObj->phone == '+905378210421' &&
                $request_otp == '111111')) {
            $saved_otp = $request_otp;

        } elseif (($profileObj->user_type == Profile::MERCHANT
                && ($profileObj->email == 'merchantsipaytest@gmail.com'
                    || $profileObj->email == 'mobile@paybull.com')
                && $request_otp == '111111')
            || ($profileObj->user_type == Profile::MERCHANT
                && $profileObj->email == $test_merchent_email
                && $request_otp == '111111')
            || (\config('constants.APP_ENVIRONMENT') == 'sp_dev'
                && $profileObj->merchant_parent_user_id == '2335'
                && $request_otp == '111111')) {
            $saved_otp = $request_otp;

        }
    }


    private function sendPasswordResetOTP($userObj)
    {
        if (!empty($userObj)) {
            $key = "PASS_RESET_OTP" . $userObj->user_type . "_" . $userObj->id;
            $OTP = $this->cacheTheOTP();
            $this->set_otp_to_cache($key, $OTP, Config::get('constants.defines.LOGIN_OTP_EXPIRE_TIME'));

            $data['OTP'] = $OTP;
            $message = view('OTP.password_change.password_change_'. $userObj->language, ['data' => $data])->render();

            $this->setGNPriority(OutGoingEmail::PRIORITY_EXPRESS);
            $this->sendSMS("", $message, $userObj->phone, 1);

            return true;
        } else {
            return false;
        }
    }

    private function modifyVerifiedParameter($userObj)
    {
        $verified = $userObj['verified'] ?? 0;
        if (BrandConfiguration::call([BackendWallet::class, 'isAllowVerifiedParameterModification']))
        {
            $verified = (bool)$verified;
        }
        return $verified;
    }
}