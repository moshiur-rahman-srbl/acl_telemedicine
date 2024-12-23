<?php
	
	namespace common\integration\Sms;
	
    use common\integration\Models\UserBlockPhone;
	
	class SmsProviderErrorCodeHandler
	{
		
		const SIM_CARD_BLOCK_ERROR_CODE_JET_SMS_SIM_CARD_CHANGE = "120021";
		const SIM_CARD_BLOCK_ERROR_CODE_JET_SMS_OPERATOR_CHANGE = "120031";
        const SIM_CARD_BLOCK_ERROR_CODE_CODEC_PLUS_SIM_CARD_CHANGE = "SimCardChangeDetected";
        const SIM_CARD_BLOCK_ERROR_CODE_CODEC_PLUS_OPERATOR_CHANGE = "OperatorChangeDetected";
        const SIM_CARD_BLOCK_ERROR_CODE_CODEC_PLUS_BLOCKED_BY_BLACKLIST = "BlockedByBlacklist";
		public static function isSimBlocked($provider, $response)
		{
			$response_code = @$response->ResponseCode ?? $response;
			$status = false;
            
			if($provider == config('constants.SMS_PROVIDER_NAMES.JETSMS')){
				
				$status =  match($response_code){
					self::SIM_CARD_BLOCK_ERROR_CODE_JET_SMS_SIM_CARD_CHANGE => true,
					self::SIM_CARD_BLOCK_ERROR_CODE_JET_SMS_OPERATOR_CHANGE => true,
					
					default => false,
				};
			}

            if($provider == config('constants.SMS_PROVIDER_NAMES.CODEC_PLUS')){

                $status =  match($response_code){
                    self::SIM_CARD_BLOCK_ERROR_CODE_CODEC_PLUS_SIM_CARD_CHANGE, self::SIM_CARD_BLOCK_ERROR_CODE_CODEC_PLUS_OPERATOR_CHANGE,self::SIM_CARD_BLOCK_ERROR_CODE_CODEC_PLUS_BLOCKED_BY_BLACKLIST => true,
                    default => false,
                };
            }
            
			return $status;
			
			
			/*
			 * TESTING
			 */
			
			// return match($provider){
			// 	config('constants.SMS_PROVIDER_NAMES.JETSMS') => true,
			// 	default => false,
			// };
		}
		
		public static function simCardBlockedMessage($provider, $response)
		{
			
			$original_response_code = @$response->ResponseCode ?? $response;
			$message = '';
			
			if($provider == config('constants.SMS_PROVIDER_NAMES.JETSMS')){
				
				$message =  match($original_response_code){
					self::SIM_CARD_BLOCK_ERROR_CODE_JET_SMS_SIM_CARD_CHANGE => __('SMS was not sent due to SIM card change. Please contact us at :support_email_address', ['support_email_address' => config('app.SUPPORT_EMAIL_ADDRESS')]),
					
					self::SIM_CARD_BLOCK_ERROR_CODE_JET_SMS_OPERATOR_CHANGE => __('SMS was not sent due to SIM card change. Please contact us at :support_email_address', ['support_email_address' => config('app.SUPPORT_EMAIL_ADDRESS')]),
					
					default => false,
				};
			}
            if($provider == config('constants.SMS_PROVIDER_NAMES.CODEC_PLUS')){

                $message =  match($original_response_code){
                    self::SIM_CARD_BLOCK_ERROR_CODE_CODEC_PLUS_SIM_CARD_CHANGE, self::SIM_CARD_BLOCK_ERROR_CODE_CODEC_PLUS_OPERATOR_CHANGE,self::SIM_CARD_BLOCK_ERROR_CODE_CODEC_PLUS_BLOCKED_BY_BLACKLIST => __('SMS was not sent due to SIM card change. Please contact us at :support_email_address', ['support_email_address' => config('app.SUPPORT_EMAIL_ADDRESS')]),

                    default => false,
                };
            }
			
			return $message;
			
		}
		
		public static function getSimBlockType($provider, $response){
			
			$response_code = @$response->ResponseCode ?? $response;
			$status = false;
			
			if($provider == config('constants.SMS_PROVIDER_NAMES.JETSMS')){
				
				$status =  match($response_code){
					self::SIM_CARD_BLOCK_ERROR_CODE_JET_SMS_SIM_CARD_CHANGE  => UserBlockPhone::SIM_BLOCK,
					self::SIM_CARD_BLOCK_ERROR_CODE_JET_SMS_OPERATOR_CHANGE  => UserBlockPhone::SIM_CHANGE,
					
					default => false,
				};
			}

            if($provider == config('constants.SMS_PROVIDER_NAMES.CODEC_PLUS')){

                $status =  match($response_code){
                    self::SIM_CARD_BLOCK_ERROR_CODE_CODEC_PLUS_SIM_CARD_CHANGE => UserBlockPhone::SIM_CHANGE,
                    self::SIM_CARD_BLOCK_ERROR_CODE_CODEC_PLUS_OPERATOR_CHANGE, self::SIM_CARD_BLOCK_ERROR_CODE_CODEC_PLUS_BLOCKED_BY_BLACKLIST  => UserBlockPhone::SIM_BLOCK,

                    default => false,
                };
            }
			
			return $status;
			
		}
	}