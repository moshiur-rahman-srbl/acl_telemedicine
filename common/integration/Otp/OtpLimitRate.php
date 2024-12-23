<?php
	
	namespace common\integration\Otp;
	
	use common\integration\ApiService;
	use common\integration\Brand\Configuration\Frontend\FrontendMix;
	use common\integration\BrandConfiguration;
	use common\integration\ManageLogging;
	use common\integration\ManipulateCache;
	use common\integration\ManipulateDate;
	use common\integration\Utility\Exception;
	
	class OtpLimitRate
	{
		const OTP_LIMIT_ERROR_MESSAGE = 'Otp Limit Rate';
		
		
		public static function prepareOtpLimitMessage($otp_time): string
		{
			
			return __(ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_OTP_RATE_LIMIT_TIME],
				[
					'time' => ManipulateDate::getDateFormat(
						$otp_time,
						'i:s'
					)
				]
			);
			
		}
		
		public static function isCheckingOtpRateLimit($cache_prefix, $userObj = null, $concat_prefix = true): array
		{
			$conditions = true;
			$cache_key = $cache_prefix;
			$response_status = ApiService::API_SERVICE_SUCCESS_CODE;
			
			$response = [];
			
			try{
				
				if($userObj && $concat_prefix){
					$cache_key .= $userObj?->id;
				}
				
				if(BrandConfiguration::call([FrontendMix::class, 'disableOtpResendBtnWithTimeLimit']) &&
					!ManipulateCache::isCacheKeyDestroyed($cache_key)){
					
					$conditions = false;
					
				}
				
				if(!$conditions){
					$response_status = ApiService::API_SERVICE_OTP_RATE_LIMIT_TIME;
				}
				
				$response = [
					$conditions,
					ManipulateCache::getCacheExpireTime($cache_key),
					$response_status,
				];
				
			}catch (\Throwable $e){
				
				(new ManageLogging())->createLog([
					'action' => 'OTP_LIMIT_RATE_EXCEPTION',
					'message' => Exception::fullMessage($e, true),
				]);
				
				$response = [
					true,
					0,
					ApiService::API_SERVICE_SUCCESS_CODE
				];
			}
			
			return $response;
			
		}
		
	}