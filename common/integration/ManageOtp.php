<?php

namespace common\integration;

use App\Http\Controllers\Traits\NotificationTrait;
use App\Http\Controllers\Traits\OTPTrait;
use App\Http\Controllers\Traits\SendEmailTrait;
use App\Models\Profile;
use App\Models\UserSetting;
use common\integration\Models\OutGoingEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ManageOtp
{
    use OTPTrait, SendEmailTrait, NotificationTrait;

    const OTP_TIMER = 600; // in second
    const COUNT_NUMBER_OF_OTP_SEND = 1;
    
    //admin panel
    const ADMIN_PANEL_LOGIN = 'admin_panel_login';
    const ADMIN_PANEL_LOGIN_OTP_SUBMIT = 'admin_panel_login_otp_submit';

    const ADMIN_PANEL_USER_EDIT = 'admin_panel_user_edit';
    const ADMIN_PANEL_USER_EDIT_OTP_SUBMIT = 'admin_panel_user_edit_otp_submit';

    // Merchant panel
    const MERCHANT_PANEL_LOGIN = 'merchant_panel_login';
    const MERCHANT_PANEL_LOGIN_OTP_SUBMIT = 'merchant_panel_login_otp_submit';

    const MERCHANT_PANEL_USER_EDIT = 'merchant_panel_user_edit';
    const MERCHANT_PANEL_USER_EDIT_OTP_SUBMIT = 'merchant_panel_user_edit_otp_submit';

    const MERCHANT_PANEL_CASH_OUT = 'merchant_panel_cash_out';
    const MERCHANT_PANEL_USER_CASH_OUT_SUBMIT = 'merchant_panel_cash_out_otp_submit';

    const MERCHANT_PANEL_WITHDRAWAL_REQUEST = 'merchant_panel_withdrawal_request';
    const MERCHANT_PANEL_WITHDRAWAL_REQUEST_OTP_SUBMIT = 'merchant_panel_withdrawal_request_otp_submit';

    const MERCHANT_PANEL_B_TO_B = 'merchant_panel_b_to_b_request';
    const MERCHANT_PANEL_B_TO_B_OTP_SUBMIT = 'merchant_panel_b_to_b_otp_submit';
    //user Panel

    const USER_PANEL_LOGIN = 'user_panel_login';
    const USER_PANEL_LOGIN_OTP_SUBMIT = 'user_panel_login_otp_submit';

    const USER_PANEL_CHANGE_PASSWORD = 'user_panel_change_password';
    const USER_PANEL_CHANGE_PASSWORD_OTP_SUBMIT = 'user_panel_change_password_otp_submit';

    const USER_PANEL_CHANGE_SECRET_QUESTION = 'user_panel_change_secret_question';
    const USER_PANEL_CHANGE_SECRET_QUESTION_OTP_SUBMIT = 'user_panel_change_secret_question_otp_submit';

    const USER_PANEL_CHANGE_EMAIL = 'user_panel_change_email';
    const USER_PANEL_CHANGE_EMAIL_OTP_SUBMIT = 'user_panel_change_email_otp_submit';

    const USER_PANEL_CHANGE_PHONE = 'user_panel_change_phone';
    const USER_PANEL_CHANGE_PHONE_OTP_SUBMIT = 'user_panel_change_phone_otp_submit';

    // User Send Money
    const USER_PANEL_SEND_MONEY = 'user_panel_send_money';
    const USER_PANEL_SEND_MONEY_OTP_SUBMIT = 'user_panel_send_money_otp_submit';

    const USER_PANEL_WITHDRAWAL_REQUEST = 'user_panel_withdrawal_request';
    const USER_PANEL_WITHDRAWAL_REQUEST_OTP_SUBMIT = 'user_panel_withdrawal_request_otp_submit';


    const USER_PANEL_EMAIL_VERIFY = 'user_panel_email_verify';
    const USER_PANEL_EMAIL_VERIFY_OTP_SUBMIT = 'user_panel_email_verify_otp_submit';

    public $is_reset_otp_limit = false;

    public static function getMaximumOtpLimitExceedMessage()
    {
        return "Maximum " . config('constants.MAX_OTP_RESEND_LIMIT') . " otp limit exceeded. Please wait for " . self::OTP_TIMER . " seconds. thank you!";
    }

    public static function getMaximumOtpRequestMessage()
    {
        return "Maximum OTP " . config('constants.MAX_OTP_FAILED_ATTEMPT') . " requests can be made. Please wait for " . self::OTP_TIMER . " seconds. thank you";
    }

    public static function clearOTP($request)
    {
        Session::forget('isOTP');
        $request->session()->forget('login_info');
    }

    public static function checkResendOtpLimit($event_name, $auth_id, $is_failed_attempt = false, $is_from_api = false)
    {
        return false; // this feature is disabled for all brands
        $status = false;

        if (BrandConfiguration::otpLimitEnable()
            && (!($is_from_api && BrandConfiguration::allowFinancialTransactionWithPassApi())
                && !BrandConfiguration::allowFinancialTransactionWithPassword())
        ) {
            $key = $event_name . '_' . $auth_id;
            $max_limit = config('constants.MAX_OTP_RESEND_LIMIT');
            if ($is_failed_attempt) {
                $max_limit = config('constants.MAX_OTP_FAILED_ATTEMPT');
            }
            $count_number_of_send = self::COUNT_NUMBER_OF_OTP_SEND;
            if (GlobalFunction::hasBrandCache($key)) {
                $count_number_of_send = GlobalFunction::getBrandCache($key) + 1;
            }
            GlobalFunction::setBrandCache($key, $count_number_of_send, self::OTP_TIMER);
            if (GlobalFunction::getBrandCache($key) > $max_limit) {
                $status = true;
            }
        }
        return $status;
    }

    public static function clearOtpLimitCache($event_name, $auth_id)
    {
        $key = $event_name . '_' . $auth_id;
        GlobalFunction::unsetBrandCache($key);
    }

    public static function otpCacheClearByActionName($action, $auth_id)
    {
        if ($action == "change-password") {
            ManageOtp::clearOtpLimitCache(ManageOtp::USER_PANEL_CHANGE_PASSWORD, $auth_id);
            ManageOtp::clearOtpLimitCache(ManageOtp::USER_PANEL_CHANGE_PASSWORD_OTP_SUBMIT, $auth_id);
        } elseif ($action == "change-email") {
            ManageOtp::clearOtpLimitCache(ManageOtp::USER_PANEL_CHANGE_EMAIL, $auth_id);
            ManageOtp::clearOtpLimitCache(ManageOtp::USER_PANEL_CHANGE_EMAIL_OTP_SUBMIT, $auth_id);
        } elseif ($action == "change-phone") {
            ManageOtp::clearOtpLimitCache(ManageOtp::USER_PANEL_CHANGE_PHONE, $auth_id);
            ManageOtp::clearOtpLimitCache(ManageOtp::USER_PANEL_CHANGE_PHONE_OTP_SUBMIT, $auth_id);
        } elseif ($action == "change-secret-question") {
            ManageOtp::clearOtpLimitCache(ManageOtp::USER_PANEL_CHANGE_SECRET_QUESTION, $auth_id);
            ManageOtp::clearOtpLimitCache(ManageOtp::USER_PANEL_CHANGE_SECRET_QUESTION_OTP_SUBMIT, $auth_id);
        } elseif ($action == "verify-email") {
            ManageOtp::clearOtpLimitCache(ManageOtp::USER_PANEL_EMAIL_VERIFY, $auth_id);
            ManageOtp::clearOtpLimitCache(ManageOtp::USER_PANEL_EMAIL_VERIFY_OTP_SUBMIT, $auth_id);
        }
    }


    /*
     * generate transaction OTP code to send via SMS and EMAIL
     */
    public function generateOtpCode ($auth_user, $extras, $is_from_api = false)
    {
        if (self::checkResendOtpLimit($extras['event_name'], $auth_user->id, false, $is_from_api)) {
            return [ApiService::API_SERVICE_FAILED_CODE, self::getMaximumOtpLimitExceedMessage()];
        }

        $data['OTP'] = $data['data']['OTP'] = $this->cacheTheOTP();
        $this->set_otp_to_cache($extras['otp_key'] . $auth_user->id, $data['OTP'], config('constants.TRANSACTION_OTP_TIME_OUT'));

        $sms_data = (isset($extras['sms_disabled']) && $extras['sms_disabled'])
            ? []
            : [
                'header' => null,
                'message' => view($extras['sms_template'] . "_" . $auth_user->language, $data)->render(),
                'phone' => $auth_user->phone
            ];
        $email_data = (isset($extras['email_disabled']) && $extras['email_disabled'])
            ? []
            : [
                'content' => [
                    'otp' => $data['OTP']
                ],
                'subject' => $extras['email_subject'],
                'from' => config('app.SYSTEM_NO_REPLY_ADDRESS'),
                'to' => $auth_user->email,
                'attachment' => null,
                'template' => $extras['email_template'],
                'language' => $auth_user->language
            ];

        $this->sendOtpViaSmsAndEmail($sms_data, $email_data, $is_from_api);

        if ($is_from_api) {
            $message_flag = BrandConfiguration::allowFinancialTransactionWithPassApi();
        } else {
            $message_flag = BrandConfiguration::allowFinancialTransactionWithPassword();
        }

        // this condition has added to skip PPARA-152 temporarily
        if (BrandConfiguration::fixedWalletPanelApiOtp()) {
            $message_flag = false;
        }

        return [ApiService::API_SERVICE_SUCCESS_CODE, $message_flag ? 'Continue the process with password' : 'OTP has been sent successfully'];
    }

    /*
     * send OTP via SMS and EMAIL if allow from BrandConfiguration
     */
    public function sendOtpViaSmsAndEmail ($sms_data, $email_data, $is_from_api = false, $priority = OutGoingEmail::PRIORITY_EXPRESS): void
    {
        $allow_password = $is_from_api
            ? BrandConfiguration::allowFinancialTransactionWithPassApi()
            : BrandConfiguration::allowFinancialTransactionWithPassword();

        if (!$allow_password) {
            $this->setGNPriority($priority);

            if (!empty($sms_data)) {
                $this->sendSMS(
                    $sms_data['header'],
                    $sms_data['message'],
                    $sms_data['phone'],
                    $priority
                );
            }

            if (!empty($email_data)) {
                $this->sendEmail(
                    $email_data['content'],
                    $email_data['subject'],
                    $email_data['from'],
                    $email_data['to'],
                    $email_data['attachment'],
                    $email_data['template'],
                    $email_data['language'],
                    $priority
                );
            }
        }
    }

    /*
     * check and match the OTP or PASSWORD according to BrandConfiguration
     */
    public function matchOtpOrPassword ($otp_key, $otp_value, $auth_user, $is_from_api = false): int
    {
        $status_code = ApiService::API_SERVICE_UNAUTHENTICATED;
        $cached_otp = $this->get_otp_from_cache($otp_key);

        $allow_password = $is_from_api
            ? BrandConfiguration::allowFinancialTransactionWithPassApi()
            : BrandConfiguration::allowFinancialTransactionWithPassword();

        // this condition has added to skip PPARA-152 temporarily
        if (BrandConfiguration::fixedWalletPanelApiOtp()) {
            $allow_password = false;
        }

        if (!empty($cached_otp) && $allow_password) {

            $status_code = (new Profile())->ValidatePasswordOnly($otp_value, $auth_user->password);

        } elseif (!empty($cached_otp) && !$allow_password && ($cached_otp == $otp_value)) {

            $status_code = ApiService::API_SERVICE_SUCCESS_CODE;

        }

        return $status_code;
    }


    public function sendEmailVerificationOTP($userObj, $phone, $email, $language)
    {
        //cashing the otp
        $OTP = $code = $this->cacheTheOTP();
        $this->set_otp_to_cache("EMAIL_VERIFICATION_OTP_FOR_" . $userObj->id, $code, 3);

        $globalUser = new GlobalUser();
        $type = $globalUser::ACTION_EMAIL_VERIFICATION;
        [$is_enabled_send_otp_sms, $is_enabled_send_otp_email] = $globalUser->checkSMSorEmailEnabledForChangeEmailOTP($type);


        $noti_data['sms_data']['OTP'] = $OTP;
        $noti_data['email_data']['OTP'] = $OTP;
        $extra_param['priority_value'] = OutGoingEmail::PRIORITY_EXPRESS;
        $extra_param['receiver_emails'] = $email;

        $this->checkAndSendNotification(
          UserSetting::action_lists['change_email_otp'],
          $noti_data,
          $is_enabled_send_otp_email,
          $is_enabled_send_otp_sms,
          UserSetting::PUSH_NOTIFICATION_DISABLED,
          $userObj,
          $extra_param
        );


        return true;
    }
}