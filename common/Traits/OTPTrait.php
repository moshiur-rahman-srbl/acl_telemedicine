<?php

namespace App\Http\Controllers\Traits;

use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Backend\BackendMix;
use common\integration\BrandConfiguration;
use common\integration\InformationMasking;
use common\integration\ManageLogging;
use common\integration\Models\OutGoingEmail;
use common\integration\Models\OutGoingSMS;
use common\integration\Sms\OtpManipulation;
use common\integration\Utility\Arr;
use common\integration\Utility\Helper;
use common\integration\Utility\Str;
use Illuminate\Support\Facades\Cache;
use common\integration\ManageFile;
use common\integration\SendSmsByProvider;

trait OTPTrait {

	private $fixed_otp = 123456;

	public $otp_length = 6;
    public array $fixed_otp_for_phone_number = [];

	public  $enable_block_by_provider_check = false;

    public function sendSMS ($header, $message, $phones, $priority=OutGoingEmail::PRIORITY_NULL, $extras=[])
    {

        if (!isset($extras['from_cronjob']) || !$extras['from_cronjob']) {
            $priority = $this->getGNPriority();
        }
        $phones = trim($phones);

        if (BrandConfiguration::sendEmailAndSmsViaCron() && $priority != OutGoingEmail::PRIORITY_NULL) {
            $inputData = [
                "phone" => $phones,
                "message" => $message,
                "priority" => $priority,
                "header" => $header,
                "status" => OutGoingEmail::STATUS_PENDING
            ];
            $result = (new OutGoingSMS())->saveData($inputData);
            /*(new ManageLogging())->createLog([
                'action' => 'OUT_GOING_SMS',
                'phone' => $phones,
                'priority' => $priority,
                // 'inputs' => $inputData,
                'result' => !empty($result)
            ]);*/
        } else {
            $this->sendDirectSMS($header, $message, $phones);
        }

        return true;
    }

    public function sendDirectSMS ($header, $message, $phones)
    {
        $phones = trim($phones);
        //For converting Trukish letter to english letter
        if(BrandConfiguration::call([Mix::class, 'enableTurkishLatterCustomizedForSmsSending'])){
            $message = Str::customCaseConversion($message, 'convertTurkishCharactersToEnglishAsItIs');
        }
        (new ManageFile())->otpWrite($message);

        $username = config('app.OTP_API_KEY');
        $password = config('app.OTP_API_SECRET');
        $otp_gateway_name = config('app.OTP_GATEWAY_NAME');
        $otp_from_name = config('app.OTP_FROM_NAME');
        $is_otp_enable = config('app.is_otp_enable');

        if (!empty($is_otp_enable) && $this->getFixedOtpChecker($message)){

            $smsProvider = (new SendSmsByProvider($username, $password, $header, $message, $phones, $otp_from_name, $otp_gateway_name));

	        if (property_exists($smsProvider, 'enable_block_by_provider_check') && $this->enable_block_by_provider_check) {

		        $smsProvider->enable_block_by_provider_check = $this->enable_block_by_provider_check;
	        }

            $smsProvider->sendSms();

            if (property_exists($this, 'exception_msg')) {
                $this->exception_msg = $smsProvider->error_message;
            }

	        if (property_exists($this, 'is_sim_blocked')) {
		        $this->is_sim_blocked  = $smsProvider->is_sim_blocked;
	        }

	        if (property_exists($this, 'sim_card_block_message')) {
		        $this->sim_card_block_message = $smsProvider->sim_card_block_message;
	        }

	        if (property_exists($this, 'block_type')) {
		        $this->block_type = $smsProvider->block_type;
	        }

        }

    }

    public function cacheTheOTP()
    {

		return $this->makeOTP();

        // $OTP = rand(100000, 999999);
        // return $OTP;
    }

    public function makeOTP()
    {
        $OTP = rand(100000, 999999);

		$otpManipulation = new OtpManipulation();
		if(auth()->check() && $otpManipulation->setStaticOtp())
		{
		    $user = auth()->user();
			$this->fixed_otp_for_phone_number = $otpManipulation->fixed_otp_for_phone_number;
		    if (!empty($user->phone) && Arr::keyExists($user->phone, $this->fixed_otp_for_phone_number)){
			    $OTP = $this->fixed_otp_for_phone_number[$user->phone];
		    }
		}

	    if ($this->allowFixedOtpValue()) {
		    $OTP = $this->fixed_otp;
	    }

        return $OTP;
    }

    public function set_otp_to_cache($key, $opt, $time)
    {
        Cache::put([$key => $opt], now()->addSeconds(60 * $time));
    }

    public function get_otp_from_cache($key)
    {
        return Cache::get($key);
    }

    public function cacheOTP($key, $otp, $time){
        Cache::put([$key => $otp], now()->addSeconds(60 * $time));
    }

    public function forget_otp_from_cache($key)
    {
        return Cache::forget($key);
    }


	private function allowFixedOtpValue(){

		$status = false;

		if(BrandConfiguration::fixedWalletPanelApiOtp()){

			$except_urls = [
				'api/individualLogin',
				'api/setting/changepassword',
				'api/setting/changeemail',
				'api/individualRegister'
			];

			$status = config('constants.defines.PANEL') == BrandConfiguration::PANEL_USER
				&& !request()->is($except_urls)
				&& request()->is('api/*')
			;

		}

		return $status;

	}

	public function getFixedOtpChecker($content){

		$status = true;

		if($this->allowFixedOtpValue()){

			$content = InformationMasking::getOtpToMask($content);

			if($content == $this->fixed_otp){
				$status = false;
			}
		}

		return $status;

	}


}
