<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Traits\OTPTrait;
use App\Models\Profile;
use App\Models\Statistics;
use App\Models\UserLoginAlertSetting;
use common\integration\ApiService;
use common\integration\AppRequestValidation;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Backend\BackendAdmin;
use common\integration\Brand\Configuration\Backend\BackendMix;
use common\integration\ManageLogging;
use common\integration\ManageOtp;
use common\integration\ManipulateDate;
use common\integration\Models\OutGoingEmail;
use common\integration\RememberMeTrait;
use common\integration\Traits\HttpServiceInfoTrait;
use common\integration\Utility\Arr;
use common\integration\Utility\Captcha;
use common\integration\Utility\Helper;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\User;
use App\Models\UserUsergroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Traits\PermissionUpdateTreait;
use App\Models\RevokeTokens;
use common\integration\GlobalFunction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Lang;
use Validator;
use App\Models\BlockTimeSettings;
use App\Models\FailedLoginList;
use common\integration\BrandConfiguration;
use common\integration\GlobalUser;
use App\Http\Controllers\Traits\ApiResponseTrait;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers, PermissionUpdateTreait, RememberMeTrait, ApiResponseTrait, HttpServiceInfoTrait;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */

    private $is_reset_otp_limit = false;

    public function showLoginForm(Request $request)
    {
        // if ($request->cookie($this->getRememberMeTokenKey(User::ADMIN))) {
        //     return redirect()->route('home');
        // }

        $businessLogData['action'] = "ADMIN_VISIT_LOGIN_PAGE";
        $this->createLog($this->_getCommonLogData($businessLogData));
        $locale = Cookie::get('locale');
        if (isset($locale)) {
            app()->setLocale($locale);
        }

        if(BrandConfiguration::allowLoginBlockTime() && GlobalFunction::hasBrandSession(BrandConfiguration::FORGET_PASSWORD)){

            GlobalFunction::unsetBrandSession(BrandConfiguration::FORGET_PASSWORD);
            return redirect()->route('password.request');
        }

        $logoutOtherDeviceWarning = GlobalFunction::getLogoutOtherDeviceWarning();


        $apply_captcha = false;
        if(BrandConfiguration::applyCaptchaRulesForAdminAndMerchant()){
            $cookie_name = GlobalUser::cookieNameForCaptcha(BrandConfiguration::PANEL_ADMIN);
            $cache_cookie = GlobalUser::getCookieForCaptcha($cookie_name);
            if(empty($cache_cookie)){
                $apply_captcha = true;
            }else{
                if(!ManipulateDate::isToday($cache_cookie)){
                    $apply_captcha = true;
                    GlobalUser::deleteCookieForCaptcha($cookie_name);
                }else{
                    $apply_captcha = GlobalUser::setCookieForCaptcha($cookie_name);
                }
            }
        }else if(BrandConfiguration::isAllowLoginCaptcha()){
            $apply_captcha = true;
        }
        if(BrandConfiguration::call([Mix::class, 'isDisabledGoogleCaptcha'])){
            $apply_captcha = false;
        }

        return view('auth.login', compact('logoutOtherDeviceWarning', 'apply_captcha'));
    }

    protected function authenticated(Request $request, $user)
    {
        if (BrandConfiguration::call([BackendAdmin::class, 'isAllowAdminCustomRedirectDashboardUrl'])) {
            $userGroupObj =  GlobalUser::findUserGroupDataByUserId($user->id);
            if(!empty($userGroupObj) && !empty($userGroupObj->dashboard_url)){
            return redirect("/".config('constants.defines.ADMIN_URL_SLUG')."/".$userGroupObj->dashboard_url);
            }
        }
        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        if(BrandConfiguration::allowLoginSessions()){
            Auth::user()->login_at = Null;
            Auth::user()->save();
        }

        $this->guard()->logout();

        $request->session()->invalidate();

        return $this->loggedOut($request) ?: redirect(route('home'));
    }
    //protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->decayMinutes = config('app.login_locked_time_minutes');
        $this->maxAttempts = config('app.failed_login_attemps');

        $this->middleware('guest')->except('logout');
        $this->middleware('TwoFA')->except('logout');

        $this->notAdmin = 0;

    }

    /* Overrides */

    public function maxAttempts()
    {
        if(BrandConfiguration::applyCaptchaRulesForAdminAndMerchant()){
            if(GlobalUser::getCookieForCaptcha(GlobalUser::cookieNameForCaptcha(BrandConfiguration::PANEL_ADMIN, true))){
                $this->maxAttempts = User::WRONG_CAPTCHA_COUNT;
            }
        }
        return property_exists($this, 'maxAttempts') ? $this->maxAttempts : 5;
    }

    public function decayMinutes()
    {
        if(BrandConfiguration::applyCaptchaRulesForAdminAndMerchant()){
            if(GlobalUser::getCookieForCaptcha(GlobalUser::cookieNameForCaptcha(BrandConfiguration::PANEL_ADMIN, true))){
                $this->decayMinutes = User::WRONG_CAPTCHA_LOCK_TIME;
            }
        }
        return property_exists($this, 'decayMinutes') ? $this->decayMinutes : 2;
    }

    /**/

    /**
     * @param Request $request
     * @return Factory|View|RedirectResponse|Response|mixed
     */
    public function login(Request $request)
    {
        ManageLogging::queryLog("login");
        $request['remote_login'] = $remoteLogin = BrandConfiguration::call([BackendAdmin::class, 'adminRemoteLogin']) && session()->has('REMOTE_LOGIN') ? true : false;
        $rules_and_message = (new AppRequestValidation())->validateLogin($request);

        $validation = Validator::make($request->all(),$rules_and_message['rules'],$rules_and_message['message']);

        if($validation->fails()) {
            if(!$remoteLogin && BrandConfiguration::applyCaptchaRulesForAdminAndMerchant()){
                if($validation->errors()->has('captcha')){
                    $cookie_name = GlobalUser::cookieNameForCaptcha(BrandConfiguration::PANEL_ADMIN, true);

                    $wrong_captcha_counter = GlobalUser::getCookieForCaptcha($cookie_name);

                    if($wrong_captcha_counter == null || $wrong_captcha_counter == User::WRONG_CAPTCHA_COUNT){
                        $wrong_captcha_counter = 0;
                    }
                    $wrong_captcha_counter = $wrong_captcha_counter + 1;

                    GlobalUser::setCookieForCaptcha($cookie_name,$wrong_captcha_counter);

                    $this->decayMinutes = User::WRONG_CAPTCHA_LOCK_TIME;
                    $this->maxAttempts = User::WRONG_CAPTCHA_COUNT;

                    $this->limiter()->hit(
                        $this->throttleKey($request), $this->decayMinutes * 60, $request
                    );
                    if(!empty($wrong_captcha_counter) && $wrong_captcha_counter >= User::WRONG_CAPTCHA_COUNT){
                        $this->customLockUser(User::ADMIN, $request);
                    }

                    return redirect()->back()->withInput($request->all())->withErrors($validation->errors());
                }
            }
            return redirect()->back()->withInput($request->all())->withErrors($validation->errors());
        }


        $user = User::where('email', $request->email)->where('user_type', User::ADMIN)->first();

        if (!empty($user) && GlobalUser::isAdminVerifiedChecker($user)) {

            if($user->is_admin_verified == Profile::LOCK_USER){
                flash(__('Your account is inactive, please contact your administrator'), 'danger');
                return redirect()->route('password.request')->withInput($request->only('email'));
            }

            if($user->is_admin_verified == Profile::ADMIN_VERIFIED_NOT_APPROVED){
                Session::flash('not-admin-error',__('Your account is inactive. Please contact us via :company_support',['company_support' => config('app.SUPPORT_EMAIL_ADDRESS')]));
                return redirect()->route('login')->withInput($request->only('email'));
            }

            if($user->is_admin_verified == Profile::ADMIN_VERIFIED_PENDING && BrandConfiguration::disablePendingStatusUsersLogin()){
                Session::flash('not-admin-error', __('The user must be set to active status.'));
                return redirect()->route('login')->withInput($request->only('email'));
            }

        }

        if($remoteLogin && !empty($user) && BrandConfiguration::call([BackendAdmin::class, 'adminRemoteLogin'])) {
            $ipValidation = (new GlobalUser())->ipValidation();
            if($ipValidation['status_code'] != ApiService::API_SERVICE_SUCCESS_CODE){
                Session::flash('not-admin-error', $ipValidation['status_description']);
                return redirect()->route('login')->withInput($request->only('email'));
            }
        }

        if ($this->hasTooManyLoginAttempts($request)) {
            GlobalUser::deleteCookieForCaptcha(GlobalUser::cookieNameForCaptcha(BrandConfiguration::PANEL_ADMIN, true));
            $this->loginFailedLockAttempts($request,$user);
        }

        if (!empty($user) && $user->is_admin_verified != Profile::ADMIN_VERIFIED_APPROVED){
            Session::flash('not-admin-error', __('The user must be set to active status.'));
            return redirect()->route('login')->withInput($request->only('email'));
        }
        if ($this->attemptLogin($request)) {
            GlobalUser::setCookieForCaptcha(GlobalUser::cookieNameForCaptcha(BrandConfiguration::PANEL_ADMIN));
            GlobalUser::deleteCookieForCaptcha(GlobalUser::cookieNameForCaptcha(BrandConfiguration::PANEL_ADMIN, true));
            return $this->sendLoginResponse($request);
        }

        if ($this->is_reset_otp_limit) {
            flash(__(ManageOtp::getMaximumOtpLimitExceedMessage()), 'danger');
            Auth::Logout();
            return redirect()->route('login');
        }

        if ($this->notAdmin) {
            Auth::logout();
            Session::flash('not-admin-error', __('These credentials do not match with any admin record'));
            return view('auth.login');
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        $login_failed_attemps = 1;

        $failedCacheKey = GlobalFunction::createSessionKey($user);

        if(GlobalFunction::hasBrandCache($failedCacheKey)){

            $login_failed_attemps = GlobalFunction::getBrandCache($failedCacheKey) + 1;

        }

        GlobalFunction::setBrandCache($failedCacheKey, $login_failed_attemps, ($this->decayMinutes * 60));

        if(GlobalFunction::getBrandCache($failedCacheKey) == $this->maxAttempts){
            GlobalUser::deleteCookieForCaptcha(GlobalUser::cookieNameForCaptcha(BrandConfiguration::PANEL_ADMIN));
            GlobalFunction::unsetBrandCache($failedCacheKey);
            $this->loginFailedLockAttempts($request,$user);

        }

        return $this->sendFailedLoginResponse($request);
    }

    protected function loginFailedLockAttempts($request,$user){

        $this->fireLockoutEvent($request);
        User::where('email', $request->email)->where('user_type', User::ADMIN)->update(['ip' => $request->ip()]);
        $from = Config('app.SYSTEM_NO_REPLY_ADDRESS');
        $data_email = '';
        $adminEmail = Config('app.ADMIN_EMAIL');
        $message['lock_time'] = date('d F, Y h:i:s');
        $message['name'] = $user->name ?? "";
        $message['email'] = isset($request->email) ? $request->email : "";
        $message['phone'] = isset($user->phone) ? $user->phone : "";
        $message['ip'] = $request->ip();
        $message['language'] = Config('app.ADMIN_LANGUAGE');
        $message['user_obj'] = $user;
        $emailTemplate = "account_blocked.admin_account_blocked";
        if(BrandConfiguration::allowIncorrectLoginAttemptsNotification()){
            $adminEmail = $data_email = (new Statistics())->incorrectLoginEmail(Statistics::INCORRECT_LOGIN_NOTIFICATION_EMAIL);
            $emailTemplate = 'account_blocked.'.GlobalFunction::brandFileNameConvention(true).'admin_account_blocked';
        }

        //out_going_email
        $this->setGNPriority(OutGoingEmail::PRIORITY_MEDIUM);
        $this->sendEmail($message, "account_blocked_admin", $from, $adminEmail,
            "", $emailTemplate, $message['language']);
        $data_arr = [];
        if (!empty($user)) {
            $data_arr['isfor'] = 'Account Lock';
            $data_arr['name'] = $user->name ?? "";
            $data_arr['email'] = $request->email;
            $data_arr['language'] = empty($user->language) ? 'tr' : $user->language;
            $data_arr['user_obj'] = $user;
            $emailTemplate = "account_blocked.user_account_blocked";
            $subject_label = "account_blocked_user";
            if(BrandConfiguration::emailContentChanges()){
                $emailTemplate = 'account_blocked.'.GlobalFunction::brandFileNameConvention(true).'user_account_blocked';
            }
            if(BrandConfiguration::allowIncorrectLoginAttemptsNotification()){
                $data_arr['email'] = !empty($data_email) ? $data_email : (new Statistics())->incorrectLoginEmail(Statistics::INCORRECT_LOGIN_NOTIFICATION_EMAIL);
                $emailTemplate = 'account_blocked.'.GlobalFunction::brandFileNameConvention(true).'user_account_blocked';
            }
            //out_going_email
            $this->setGNPriority(OutGoingEmail::PRIORITY_EXPRESS);
            $this->sendEmail($data_arr, $subject_label, $from, $data_arr['email'],
                "", $emailTemplate, $data_arr['language']);
        }
        $businessLogData['action'] = "ADMIN_ACCOUNT_LOCKED";
        $logData = $request->all();
        unset($logData['password']);
        $businessLogData['input_data'] = $logData;
        $businessLogData['model_data'] = $data_arr;
        $this->createLog($this->_getCommonLogData($businessLogData));
        return $this->sendLockoutResponse($request);
    }


    protected function attemptLogin(Request $request)
    {
        $isOTPEnable = config('app.is_otp_enable');
        $isRememberMeEnable = $request->get('remember') === 'on';
        $allCredintials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'user_type' => Config::get('constants.defines.ADMIN_USER_TYPE')
        ];

        $result = Auth::attempt($allCredintials);
        if ($result) {
            $authUser = auth()->user();

            if(BrandConfiguration::call([BackendAdmin::class, 'adminRemoteLogin']) && config('app.is_otp_enable') == 1){
                if( $authUser->is_otp_required == 1){
                    $isOTPEnable = true;
                    Session::put('isOTP', 1);
                }else{
                    $isOTPEnable = false;
                }
            }elseif (
                BrandConfiguration::call([BackendAdmin::class, 'allowDirectlyLoginToAdminPanel']) &&
                config('app.is_otp_enable') == 1 &&
                BrandConfiguration::call([BackendAdmin::class, 'allowUserListToLoginAdminPanel'],@$authUser->email)
            ){

                $isOTPEnable = false;

            } else {
                $isOTPEnable == true ? Session::put('isOTP', 1) : '';
            }
            //$isOTPEnable =  $authUser->is_otp_required ?? config('app.is_otp_enable');
//            $request->remote_login == true ?: Session::put('isOTP', 1);
            if ($this->userUsergroupCheck(auth()->user()->id)) {

                // $authUser = auth()->user();

                if(\common\integration\BrandConfiguration::allowLoginBlockTime()){

                    GlobalFunction::setFailedLoginAttempts($authUser,'fail_attempts_count');
                    $authUser->update(['failed_login_attempt' => 0]);

                }

                //added to check last changed password date
                $LastChangedPasswordSKey = 'AdminChangedPasswordStatus' . $authUser->id;
                Session::forget($LastChangedPasswordSKey);
                $lpc_duration = \config('constants.PASSWORD_CHANGE_AFTER_MONTHS');

                if ($lpc_duration > 0) {
                    $lpc_months = (new Profile())->checkPasswordChange($authUser->updated_password_at);

                    if ($lpc_months >= $lpc_duration) {
                        Session::put($LastChangedPasswordSKey, 1);
                    }
                }

                $this->getPermissionList($authUser->id);


                if ($isOTPEnable) {
                    if (ManageOtp::checkResendOtpLimit(ManageOtp::ADMIN_PANEL_LOGIN, $authUser->id)) {
                        $this->is_reset_otp_limit = true;
                        ManageOtp::clearOTP($request);
                        return false;
                    }

                    $data = array();
                    $language = $this->getLang($authUser);
                    $OTP = $this->cacheTheOTP();
                    $header = "";
                    $message = view('OTP.login.login_' . $language, compact('OTP'))->render();
                    $data['name'] = $authUser->name;
                    $data['otp'] = $OTP;
                    $template = 'login.otp_' . $language;
                    $attachment = "";
                    $from = config('app.SYSTEM_NO_REPLY_ADDRESS');
                    $phone = $authUser->phone;
                    $userInfo = [
                        'email' => $request->email,
                        'id' => $authUser->id,
                        'phone' => $phone,
                        'remember' => $isRememberMeEnable
                    ];
                    $to = $request->email;
                    $request->session()->put('login_info', $userInfo);
                    $otp_key = 'login_otp_' . $authUser->id;
                    $otp_expire_time = Config::get('constants.defines.LOGIN_OTP_EXPIRE_TIME');
                    $this->set_otp_to_cache($otp_key, $OTP, $otp_expire_time);
                    //Cache::put(['login_otp_' . auth()->user()->id => $OTP], now()->addSeconds(60 * 5));

                    $laravelCacheOTPKey = (GlobalUser::getUserCacheOTPKey($authUser));
                    GlobalFunction::unsetBrandCache($laravelCacheOTPKey);
                    $expr_time_min = intval($otp_expire_time) * 60;
                    GlobalFunction::setBrandCache($laravelCacheOTPKey, Carbon::now()->addMinutes($otp_expire_time), $expr_time_min);

                    $priority = 1; // Queue 1= high
                    $this->setGNPriority(OutGoingEmail::PRIORITY_EXPRESS);
                    // otp_channel 0 => Otp enable for all chanel, 1 => only sms, 2 => only email
                    if($authUser->otp_channel == User::OTP_CHANNEL_ALL){
                        if(BrandConfiguration::allowSendSmsAndEmailSerially()){
                            $this->sendSMS($header, $message, $phone, $priority);
                            //out_going_email
                            $this->sendEmail($data, "LOGIN_OTP", $from, $to, $attachment, "login.otp", $language, "", $priority);
                        }else{
                            //out_going_email
                            $this->sendEmail($data, "LOGIN_OTP", $from, $to, $attachment, "login.otp", $language, "", $priority);
                            $this->sendSMS($header, $message, $phone, $priority);
                        }
                    }elseif($authUser->otp_channel == User::OTP_CHANNEL_SMS){
                        $this->sendSMS($header, $message, $phone, $priority);
                    }elseif($authUser->otp_channel == User::OTP_CHANNEL_EMAIL){
                        $this->sendEmail($data, "LOGIN_OTP", $from, $to, $attachment, "login.otp", $language, "", $priority);
                    }

                    $businessLogData['action'] = "ADMIN_LOGIN_OTP_REQUEST";
                    $logData = $request->all();
                    unset($logData['password']);
                    $businessLogData['otp_status'] = "OTP Sent";
                    $businessLogData['input_data'] = $logData;
                    $this->createLog($this->_getCommonLogData($businessLogData));
                } else {

                    if ($isRememberMeEnable) {
                        $this->setupRememberMe(User::ADMIN);
                    }
                }
            } else {
                $this->notAdmin = 1;
                return false;
            }
        } else {

            $user = new User();
            // $user_fl = $user->select('failed_login_attempt')->where('email', $request->email)->first();
            $user_fl = $user->select('id','failed_login_attempt','last_failed_login_datetime')
                            ->where('email',  $request->email)
                            ->where('user_type', User::ADMIN)
                            ->first();
            if (is_null($user_fl)) {
                $fla_cnt = 0;
            } else {
                $fla_cnt = $user_fl->failed_login_attempt;
            }
            if(!\common\integration\BrandConfiguration::allowLoginBlockTime()){

                if(!empty($user_fl)){
                    $user_fl->update([
                        'failed_login_attempt' => ($fla_cnt + 1),
                        'last_failed_login_datetime' => Carbon::now()
                    ]);
                }


                // $user->where('email', $request->email)->update(['failed_login_attempt' => ($fla_cnt + 1), 'last_failed_login_datetime' => Carbon::now()]);
            }else{

                if(!empty($user_fl)){

                    $insert_data = [
                        'user_type' => User::ADMIN,
                        'user_id' => $user_fl->id,
                        'failed_login_time' =>  Carbon::now(),
                    ];

                    (new FailedLoginList)->saveData($insert_data);
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

            $businessLogData['action'] = "ADMIN_LOGIN_REQUEST_FAILED";
            $logData = $request->all();
            unset($logData['password']);
            $businessLogData['input_data'] = $logData;
            $businessLogData['model_data'] = $log_data;
            $this->createLog($this->_getCommonLogData($businessLogData));
        }
        //echo "sdsdsd";exit;

        return $result;
    }

    public function resetUserSessionId()
    {
        Log::info(json_encode("LOGOUT::Called"));
        $previous_session = Auth::User()->session_id;
        $jsonPrevSession = [];
        if ($previous_session) {
            $jsonPrevSession = explode(",", $previous_session);
            foreach ($jsonPrevSession as $jsonKey => $sessionValue) {
                if (Session::getId() == $sessionValue) {
                    unset($jsonPrevSession[$jsonKey]);
                }
            }
        }
        if(BrandConfiguration::allowLoginSessions()){
            GlobalFunction::setBrandSession(GlobalUser::activitySessionKey(), Carbon::now());
            Auth::user()->last_activity_datetime = Carbon::now();
        }

        Auth::user()->session_id = implode(",", $jsonPrevSession);
        Auth::user()->save();
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $revokeTokens = new RevokeTokens();
        $revokeTokens->logoutPastLogin(Auth::User()->id);

        if(BrandConfiguration::allowLoginSessions()){
            GlobalFunction::setBrandSession(GlobalUser::activitySessionKey(), Carbon::now());
            GlobalFunction::setBrandSession("session_user_sent_email", "0");
            Auth::user()->last_activity_datetime = Carbon::now();
            Auth::user()->login_at = Carbon::now();
        }


        Auth::user()->session_id = Session::getId();
        Auth::user()->save();

        if(BrandConfiguration::userLoginAlertSettings()){
            $loginAlert  = new UserLoginAlertSetting();
            $loginAlertObj = $loginAlert->findData(User::ADMIN,true);

            if(!empty($loginAlertObj) && isset($loginAlertObj->from_time) && isset($loginAlertObj->to_time)){

                $now = Carbon::now();
                $from_time = Carbon::createFromTimeString($loginAlertObj->from_time);
                $to_time = Carbon::createFromTimeString($loginAlertObj->to_time);

                if($now->between($from_time, $to_time)){

                    $this->loginAlertNotification(Auth::user(),$loginAlertObj);
                }


            }
        }
        $this->clearLoginAttempts($request);
        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended($this->redirectPath());
    }

    public function userUsergroupCheck($id)
    {
        $admin_check = UserUsergroup::where('user_id', $id)->first();
        if (empty($admin_check)) {
            return false;
        } else {
            return true;
        }
    }

    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        if (isset($request['user_object']) && !empty($request['user_object'])) {
            $laravelCacheKey = $request['user_object']['id'] . '|' . $request['user_object']['email'] . '|' . $request['user_object']['user_type'];
            if (Cache::has($laravelCacheKey)) {
                $seconds = Cache::get($laravelCacheKey)->diffInSeconds(Carbon::now());
            }
        }
        if(BrandConfiguration::call([BackendMix::class, 'changeTimeformatForBlockUser'])){
            $seconds =  ManipulateDate::convertSecondsToTime($seconds, 'H:i:s');
        }else{
            $seconds = ManipulateDate::convertSecondsToTime($seconds, 'i:s');
        }



        // $seconds = round($seconds);
        // if(isset($request->oldemail) && !empty($request->oldemail)) {
        //     $request['email'] = $request->oldemail;
        // }

        throw ValidationException::withMessages([
            $this->username() => [Lang::get('auth.throttle', ['seconds' => $seconds])],
        ])->status(429);

    }

    // private function failedCacheKey($user, $failed_login_attempts = 'failed_login_attempts')
    // {
    //     if(!empty($user)){
    //         return $failed_login_attempts . '_' . $user->id . '_' . $user->email . '_' . $user->user_type;
    //     }

    //     return "";
    // }
    protected function incrementLoginAttempts(Request $request)
    {

        list($attempt, $decayMinutes, $maxAttempts, $user_object)  = GlobalFunction::loginBlockTimeIncrement(
            [
                User::ADMIN,
            ],
            1,
            $request->email,
            $this->maxAttempts,
            $this->decayMinutes
        );

        $this->decayMinutes = $decayMinutes;
        $this->maxAttempts = $maxAttempts;

        $this->limiter()->hit(
            $this->throttleKey($request), $this->decayMinutes * 60, $request
        );

        if(!empty($user_object) && BrandConfiguration::allowLoginBlockTime() && $user_object->is_admin_verified == Profile::LOCK_USER){
            $this->clearLoginAttempts($request);
        }
    }


    public function loginAlertNotification($userObj,$loginAlertObj){
        $data = array();
        $language = $this->getLang($userObj);
        $header = "";
        $subject_label = 'unexpected_login_detected';
        $data['first_name'] = $userObj->first_name;
        $data['last_name'] = $userObj->last_name;
        $data['phone'] = $userObj->phone;
        $data['login_time'] = $userObj->login_at;
        $template = 'user_login_alert.content';
        $attachment = "";
        $from = config('app.SYSTEM_NO_REPLY_ADDRESS');
        $to = empty($loginAlertObj->email_addresses) ? [] : array_map("trim", explode(',', $loginAlertObj->email_addresses));
        $phones = empty($loginAlertObj->sms_numbers) ? [] : array_map("trim", explode(',', $loginAlertObj->sms_numbers));
        $message = view('OTP.user_login_alert.content_' . $language, compact('data'))->render();

        $email_sent = $sms_sent = '';
        if(isset($loginAlertObj->is_notification_type_email) && $loginAlertObj->is_notification_type_email == 1 && count($to) > 0 ){
            $email_sent = true;

            //out_going_email
            $this->setGNPriority(OutGoingEmail::PRIORITY_HIGH);
            $this->sendEmail($data, $subject_label, $from, $to, $attachment, $template, $language);
        }

        if(isset($loginAlertObj->is_notification_type_sms) && $loginAlertObj->is_notification_type_sms == 1 && count($phones) > 0 ){
            $sms_sent = true;
            $this->setGNPriority(OutGoingEmail::PRIORITY_MEDIUM);
            foreach ($phones as $phone){
                $this->sendSMS($header, $message, $phone);
            }
        }

        $businessAdminLogData['action'] = "USER_LOGIN_ALERT_RESPONSE";
        $businessAdminLogData['email_sent'] = $email_sent;
        $businessAdminLogData['sms_sent'] = $sms_sent;
        $businessAdminLogData['data'] = $loginAlertObj;
        (new ManageLogging())->createLog($businessAdminLogData);
    }

    public function customLockUser($user_type, $request){

        $user = (new User())->getUserByPhoneAndEmail($user_type, $request->email);
        $this->loginFailedLockAttempts($request,$user);
    }
}
