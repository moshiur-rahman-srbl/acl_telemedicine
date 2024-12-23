<?php
	
	namespace common\integration\Sms\Providers;
	
	use common\integration\InformationMasking;
	use common\integration\ManageLogging;
	use common\integration\Utility\Arr;
	use common\integration\Utility\Curl;
	use common\integration\Utility\Json;
	use common\integration\Utility\Str;
	
	class JetSms
	{
		
		public $sms_sending_url = 'https://ws.jetsms.com.tr/api/sendsms';
		
		public $user_name = "";
		public $password = "";
		
		public $otp_from_name = "";
		public $is_check_international_no = false;
		
		public $is_multiple_gsm_no = false;
		
		
		public function __construct($user_name, $password, $otp_from_name, $is_check_international_no)
		{

			$this->otp_from_name = $otp_from_name;
			$this->is_check_international_no = $is_check_international_no;
			
			if($this->is_check_international_no){
				$this->prepareCredentialsForInternationalNo($user_name, $password);
			}else{
				$this->user_name = $user_name;
				$this->password = $password;
			}
			
			if(!empty(config()->get('constants.OTP_SEND_URL'))){
				$this->sms_sending_url = config()->get('constants.OTP_SEND_URL');
			}
			
		}
		
		private function prepareHeader(): array
		{
			
			return [
				'Content-Type: application/json'
			];
			
		}
		
		private function prepareChannels(): array
		{
			
			$channels = [
				"TRKCO",
				"AVEAO",
				"VFOR"
			];
			
			if($this->is_check_international_no){
				$channels = [
					"TRKCYD"
				];
			}
			
			return $channels;
		}
		
		
		public function checkIsMultipleGsmNo(): bool
		{
			return $this->is_multiple_gsm_no;
		}
		
		private function prepareCredentialsForInternationalNo($user_name, $password): void
		{
			$this->user_name = !empty(config('constants.OTP_API_KEY_2')) ? config('constants.OTP_API_KEY_2') :
				$user_name;
			$this->password = !empty(config('constants.OTP_API_SECRET_2')) ? config('constants.OTP_API_SECRET_2') :
				$password;
		}
		
		public function hideLogKeys($content): void
		{
			ManageLogging::hideKeys(
				[
					$this->user_name,
					$this->password,
					
					/*
					 * OTP six digit integer number should be masked
					 */
					
					InformationMasking::getOtpToMask($content)
				]
			);
			
		}
		
		public function sendSms(
			$is_enable_new_flow,
			$gsmNo,
			$message,
			$simcheckinday,
			$mnpcheckinday
		)
		{
		
			$this->hideLogKeys($message);
			
			$method_name = 'processTextWithOldFlow';
			
			if($is_enable_new_flow){
				$method_name = 'processTextWithNewFlow';
			}
			
			return $this->$method_name(
				$gsmNo,
				$message,
				$simcheckinday,
				$mnpcheckinday
			);
		}
		
		
		public function processTextWithNewFlow(
			$gsmNo,
			$message,
			$simcheckinday,
			$mnpcheckinday
		)
		{
			$request_param = [
				"user" => $this->user_name,
				"password" => $this->password,
				"originators" => $this->otp_from_name,
				"reference" =>  null,
				"startdate" =>  "",
				"expiredate" =>  "",
				"exclusionstarttime" => null,
				"exclusionexpiretime" => null,
				"broadcastmessage" => $message,
				"smsmessages" => $this->prepareSmsMessageBody($gsmNo, $message),
				"multichannels" =>  $this->prepareChannels(),
				"multioriginators"  => [
					$this->otp_from_name
				],
				"multichanneltype"  => "Ordered",
				"channel"  =>  "VF",
				"blacklistfilter" =>  0,
				"iysfilter" =>  0,
				"iyscode" => null,
				"brandcode" => null,
				"retailercode" => null,
				"recipienttype" => null,
				"simcheckinday" => $simcheckinday,
				"mnpcheckinday" => $mnpcheckinday,
			];
			
			return $this->manipulateOriginalResponse(
				$this->callCurl(
					Json::encode(
						$this->requestParamManipulation($request_param)
					)
				), $gsmNo
			);
			
	
			
		}
		
		
		/*
		 *
		 * $sms_send_response = json_encode(
				(object)[
					'ResponseCode' => 120021,
			    ]
			
		* );
		 *
		 */
		
		private function manipulateOriginalResponse($original_response, $gsmNo): null|string|object
		{
			$response = $original_response ? Json::decode($original_response) : (object)[];
			
			if(!$this->checkIsMultipleGsmNo() && Str::contains($original_response, ['ResponseCode', 'ResponseGroupIdArray', 'Status'])){
				
				$response->ResponseCode = collect($response->ResponseGroupIdArray)
					->where('GsmNumber', $gsmNo)
					?->first()
					?->Status;
			}
			
			return $response;
			
		}
		
		private function prepareSmsMessageBody($gsmNo, $message): array
		{
			$sms_body = [];
			
			if(Arr::isOfType($gsmNo)){
				
				$this->is_multiple_gsm_no = true;
				
				foreach($gsmNo as $key => $no){
					$gsmNo = Str::replace('+', '', $gsmNo);
					$sms_body[] = $this->smsBody($no, $message, $key);
				}
				
			}else{
				$sms_body[] = $this->smsBody($gsmNo, $message, 0);
			}
			
			return $sms_body;
			
		}
		
		private function smsBody($gsmNo, $message, $message_id): array
		{
			return [
				"messagetext" => $message,
				"receipent" => $gsmNo,
				"messageid" => $message_id,
			];
		}
		
		private function requestParamManipulation($request_param): array
		{
			if(!$this->checkIsMultipleGsmNo()){
				$request_param = Arr::merge($request_param, [
					"realtime" => 1
				]);
			}
			return $request_param;
		}
		
		public function callCurl($request_param)
		{
			return Curl::withAction
			(
				"JET_SMS_REQUEST_PARAM", true
			)::post(
				$this->sms_sending_url,
				$this->prepareHeader(),
				$request_param
			);
		}
		
		
		public function processTextWithOldFlow(
			$gsmNo,
			$message,
			$simcheckinday,
			$mnpcheckinday
		){
			
			$request_param = [
					"user" => $this->user_name,
					"password" => $this->password,
					"originators" => $this->otp_from_name,
					"reference" =>"",
					"startdate" =>"",
					"expiredate" =>"",
					"exclusionstarttime" =>"",
					"exclusionexpiretime" =>"",
					"broadcastmessage" => $message,
					"smsmessages" => $this->prepareSmsMessageBody($gsmNo, $message),
					"multichannels" => $this->prepareChannels(),
					"multioriginators" => [
						$this->otp_from_name
					],
					"multichanneltype" => "Force",
					"simcheckinday" => $simcheckinday,
					"mnpcheckinday" => $mnpcheckinday,
					"X" => 1
				];
			
			// $request_param = $this->requestParamManipulation($request_param);
			
			return $this->manipulateOriginalResponse(
				$this->callCurl(
					Json::encode(
						$request_param
					)
				), $gsmNo
			);
			
		}
		
	}