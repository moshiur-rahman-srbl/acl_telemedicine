<?php
	
	namespace common\integration\Sms;
	
	use App\Http\Controllers\Traits\OTPTrait;
	use common\integration\Brand\Configuration\Backend\BackendMix;
	use common\integration\BrandConfiguration;
	use common\integration\ManipulateDate;
	use common\integration\Utility\Helper;
	
	class OtpManipulation
	{
		use OTPTrait;
		
		const OTP_EXPIRED_TIME = 3*60; // 3 minutes
		
		public function setStaticOtp() : bool
		{
			$status = $this->getContidionsStatus();
			
			if($status){
			
				if(Helper::isProdServerEnvironment()){
					$this->fixed_otp_for_phone_number = [
						'+905333367280' => '231818',
						'+905334171654' => '125780',
						'+905455230494' => '125780',
					];
				}
				
				if(Helper::isProvServerEnvironment()){
					$this->fixed_otp_for_phone_number = [
						'+905398554245' => '123456',
						'+905379159675' => '123456',
						'+905327406995' => '123456',
					];
				}
				
			}
			
			
			return $status;
		}
		
		private function getContidionsStatus() : bool
		{
			return (
				BrandConfiguration::call([BackendMix::class, "isAllowFixedOtpForFixedPhone"])
			) && property_exists($this, 'fixed_otp_for_phone_number');
		}
		
		public static function getOtpTimer()
		{
			return ManipulateDate::getDateFormat(self::OTP_EXPIRED_TIME, 'i:s');
		}
		
		
	}