<?php

namespace App\Http\Controllers\Traits;

use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Backend\BackendMix;
use common\integration\BrandConfiguration;
use common\integration\ManageFile;
use common\integration\ManageLogging;
use common\integration\Models\OutGoingEmail;
use common\integration\Models\OutGoingSMS;
use common\integration\Sms\OtpManipulation;
use common\integration\Utility\Arr;
use common\integration\Utility\Helper;
use common\integration\Utility\Str;
use Illuminate\Support\Facades\Cache;
use common\integration\SendSmsByProvider;

trait OTPTrait
{
    public array $fixed_otp_for_phone_number = [];


    public $otp_length = 6;

    public function sendSMS($header, $message, $phones, $priority = OutGoingEmail::PRIORITY_NULL, $extras = [])
    {

        if (!isset($extras['from_cronjob']) || !$extras['from_cronjob']) {
            $priority = $this->getGNPriority();
        }
        $phones = trim($phones);


        $this->sendDirectSMS($header, $message, $phones);
        return true;
    }

    public function sendDirectSMS($header, $message, $phones)
    {
        $phones = trim($phones);

        if (BrandConfiguration::call([Mix::class, 'enableTurkishLatterCustomizedForSmsSending'])) {
            $message = Str::customCaseConversion($message, 'convertTurkishCharactersToEnglishAsItIs');
        }

        (new ManageFile())->otpWrite($message);

        $username = config('app.OTP_API_KEY');
        $password = config('app.OTP_API_SECRET');
        $otp_gateway_name = config('app.OTP_GATEWAY_NAME');
        $otp_from_name = config('app.OTP_FROM_NAME');
        $is_otp_enable = config('app.is_otp_enable');

        if (!empty($is_otp_enable)) {
            $smsProvider = (new SendSmsByProvider($username, $password, $header, $message, $phones, $otp_from_name, $otp_gateway_name));
            $smsProvider->sendSms();

            if (property_exists($this, 'exception_msg')) {
                $this->exception_msg = $smsProvider->error_message;
            }
        }
    }

    public function cacheTheOTP()
    {
        $OTP = rand(100000, 999999);

        $otpManipulation = new OtpManipulation();
        if (auth()->check() && $otpManipulation->setStaticOtp()) {
            $user = auth()->user();
            $this->fixed_otp_for_phone_number = $otpManipulation->fixed_otp_for_phone_number;
            if (!empty($user->phone) && Arr::keyExists($user->phone, $this->fixed_otp_for_phone_number)) {
                $OTP = $this->fixed_otp_for_phone_number[$user->phone];
            }
        }

        return $OTP;
    }

    public function set_otp_to_cache($key, $opt, $time)
    {
        Cache::put([$key => $opt], now()->addSeconds(60 * $time));
    }

}
