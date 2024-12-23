<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CommonLogTrait;
use App\Models\SecurityImage;
use App\Models\Usergroup;
use App\Models\UserUsergroup;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Backend\BackendAdmin;
use common\integration\GlobalUser;
use common\integration\ManageLogging;
use common\integration\ManageOtp;
use common\integration\Models\OutGoingEmail;
use common\integration\RememberMeTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Traits\OTPTrait;
use Illuminate\Support\Facades\Cache;
use App\User;
use Illuminate\Support\Facades\Config;
use Session;
use Auth;
use common\integration\BrandConfiguration;
use common\integration\GlobalFunction;
use Carbon\Carbon;

class OTPController extends Controller
{
    use OTPTrait, CommonLogTrait, RememberMeTrait;

    public function index()
    {
        $cmsInfo = [
            'moduleTitle' => "",
            'subModuleTitle' => ""
        ];
        $data = [];
        if (BrandConfiguration::call([Mix::class, 'allowSecurityImageForAdminOtpPage'])) {

            $data['check_security_image'] = GlobalUser::checkSecurityImage(auth()->user());
            if ($data['check_security_image']) {
                $data['security_image_url'] = (new SecurityImage())->getSecurityImageUrl(auth()->user()->security_image_id);
            }
        }
        return view('OTP.index', compact('cmsInfo', 'data'));
    }

    public function verify(Request $request)
    {
        ManageLogging::queryLog("tanvir");
        if (request()->ajax() && BrandConfiguration::allowWrongOTPAttempt() && request()->type == 'secret_question_password_checker') {
            $data = [
                'question' => request()->secrect_question_id,
                'answer' => request()->answer,
                'password' => request()->password,
            ];
            return response()->json([
                'status' => (new GlobalFunction)->checkSequerityQuestionAndPassword($data),
            ]);

        }


        $this->validate($request, [
            'otp' => 'required'
        ]);
        $loginInfo = $request->session()->get('login_info');
        $OTP = (!empty($loginInfo) && isset($loginInfo['id'])) ? Cache::get('login_otp_' . $loginInfo['id']) : '';

        if (!empty($request->otp) && $request->otp == $OTP) {
            ManageOtp::clearOtpLimitCache(ManageOtp::ADMIN_PANEL_LOGIN, \auth()->user()->id);
            ManageOtp::clearOtpLimitCache(ManageOtp::ADMIN_PANEL_LOGIN_OTP_SUBMIT, \auth()->user()->id);
            $request->session()->put('APP_ADMIN_LOGIN_IP', $this->getClientIp());
            Session::forget('isOTP');
            $request->session()->forget('login_info');
            $userUsergoup = new UserUsergroup();
            $userGroup = new Usergroup();

            if (BrandConfiguration::call([BackendAdmin::class, 'isAllowAdminCustomRedirectDashboardUrl'])) {
                $userGroupObj = GlobalUser::findUserGroupDataByUserId(auth()->id());
            } else {
                $userUsergoupObj = $userUsergoup->getFirstUserUsergoupByUserId(auth()->id());
                $userGroupObj = $userGroup->getUsergroupId($userUsergoupObj->usergroup_id);
            }
            $businessLogData['action'] = "ADMIN_LOGIN_OTP_SUCCESS";
            if (!BrandConfiguration::allowLoginSessions() && !empty(config('app.is_otp_enable'))) {
                $update_data['login_at'] = Carbon::now();
                (new User)->updateUser($loginInfo['id'], $update_data);
            }

            if ($loginInfo['remember'] === TRUE) {
                $this->setupRememberMe(User::ADMIN);
            }

            $logData = $request->all();
            unset($logData['password']);
            $businessLogData['otp_status'] = "OTP matched";
            $businessLogData['input_data'] = $logData;
            $this->createLog($this->_getCommonLogData($businessLogData));
            return redirect(route('home'));
            if (!empty($userGroupObj->dashboard_url)) {
                return redirect("/" . config('constants.defines.ADMIN_URL_SLUG') . "/" . $userGroupObj->dashboard_url);
            }
            return redirect(route('home'));
        }

        if (BrandConfiguration::allowWrongOTPAttempt() && isset($loginInfo['id'])) {
            GlobalFunction::setOtpAttemptCounter($loginInfo['id']);
        }

        if (ManageOtp::checkResendOtpLimit(ManageOtp::ADMIN_PANEL_LOGIN_OTP_SUBMIT, auth()->user()->id, true)) {
            flash(__(ManageOtp::getMaximumOtpRequestMessage()), 'danger');
            return $this->clearOTP($request);
        }

        $businessLogData['action'] = "ADMIN_LOGIN_OTP_FAILED";
        $logData = $request->all();
        unset($logData['password']);
        $businessLogData['otp_status'] = "OTP is expired or invalid";
        $businessLogData['input_data'] = $logData;
        $this->createLog($this->_getCommonLogData($businessLogData));
        flash(__('OTP is expired or invalid'), 'danger');
        return back();
    }

    public function resend(Request $request)
    {
        // dd($request);
        if ($request->resend_otp == 'resend') {
            //$user = User::
            $authUser = auth()->user();

            if (ManageOtp::checkResendOtpLimit(ManageOtp::ADMIN_PANEL_LOGIN, $authUser->id)) {
                flash(__(ManageOtp::getMaximumOtpLimitExceedMessage()), 'danger');
                return $this->clearOTP($request);
            };

            $this->setGNPriority(OutGoingEmail::PRIORITY_EXPRESS);
            $priority = 1;
            $OTP = $this->cacheTheOTP();
            $header = "";
            $message = __('Your login OTP is') . ' ' . $OTP;
            $loginInfo = $request->session()->get('login_info');
            $otp_key = 'login_otp_' . $authUser->id;
            $otp_expire_time = Config::get('constants.defines.LOGIN_OTP_EXPIRE_TIME');
            $this->set_otp_to_cache($otp_key, $OTP, $otp_expire_time);

            $laravelCacheOTPKey = (GlobalUser::getUserCacheOTPKey($authUser));
            GlobalFunction::unsetBrandCache($laravelCacheOTPKey);
            $expr_time_min = intval($otp_expire_time) * 60;
            GlobalFunction::setBrandCache($laravelCacheOTPKey, Carbon::now()->addMinutes($otp_expire_time), $expr_time_min);

            $phone = $loginInfo['phone'] ?? auth()->user()->phone; //auth()->user()->phone;

            $data['otp'] = $OTP;
            $data['name'] = auth()->user()->name;
            $to_mail = auth()->user()->email;
            $language = $this->getLang(auth()->user());
            $from = config('app.SYSTEM_NO_REPLY_ADDRESS');
            $emailTemplate = "login.otp";
            if ($authUser->otp_channel == User::OTP_CHANNEL_ALL) {
                $this->sendEmail($data, "Resend_Login_OTP", $from, $to_mail, "", $emailTemplate, $language, '', $priority);
                $this->sendSMS($header, $message, $phone, $priority);
            } elseif ($authUser->otp_channel == User::OTP_CHANNEL_SMS) {
                $this->sendSMS($header, $message, $phone, $priority);
            } elseif ($authUser->otp_channel == User::OTP_CHANNEL_EMAIL) {
                $this->sendEmail($data, "Resend_Login_OTP", $from, $to_mail, "", $emailTemplate, $language, '', $priority);
            }


            flash(__('Your new OTP is sent, please check'), 'success');
            $businessLogData['action'] = "OTP RESENT";
            $logData = $request->all();
            unset($logData['password']);
            $businessLogData['otp_status'] = "Your new OTP is sent, please check";
            $businessLogData['input_data'] = $logData;
            $this->createLog($this->_getCommonLogData($businessLogData));
            return redirect()->back()->with('Message', __('Your new OTP is sent, please check'));
        }
    }

    public function clearOTP(Request $request)
    {
        ManageOtp::clearOTP($request);
        $businessLogData['action'] = "OTP VERIFY PAGE CANCELLED";
        $businessLogData['user'] = auth()->user();
        $this->createLog($this->_getCommonLogData($businessLogData));
        Auth::Logout();
        return redirect()->route('login');
    }
}
