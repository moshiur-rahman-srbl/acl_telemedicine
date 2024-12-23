<?php

namespace common\integration\Sms\Providers;

use common\integration\Brand\Configuration\Backend\BackendMix;
use common\integration\BrandConfiguration;
use common\integration\InformationMasking;
use common\integration\ManageLogging;
use common\integration\ManipulateDate;
use common\integration\Utility\Arr;
use common\integration\Utility\Cache;
use common\integration\Utility\Curl;
use common\integration\Utility\Encode;
use common\integration\Utility\Json;
use common\integration\Utility\Number;
use common\integration\Utility\Str;
use Illuminate\Support\Carbon;

class CodecPlus
{
    public $life_time = 10*60;
    public $host_url = '';

    const TRUST_LEVEL_MAKER = 1;
    const TRUST_LEVEL_CHECKER =2;

    public function __construct()
    {
        $this->host_url = config('constants.OTP_SEND_URL');
    }

    public function auth($username, $password)
    {
		
        ManageLogging::hideKeys($password);

        $url = $this->host_url."/Authentication";

        $key = "CODEC_PLUS";

        $reference = $username.$password;

        if(!empty($tmp = Cache::get($key, $reference))){
            $response = $tmp;
        }else{
            $payload =
                [
                    "username" => $username,
                    "password" => $password
                ];

            $json_payload = Json::encode($payload, JSON_UNESCAPED_UNICODE);

            $response = Json::decode(Encode::toUtf8($raw = Curl::withAction("CODEC_PLUS_AUTH",true)::post($url, $this->headers(), $json_payload)), true);

            if ($this->isSuccessfulResponse($response)) {
                Cache::add($key, $response, $this->life_time, $reference);
            }

        }

        return $response;

    }


    public function send($username, $password, $receiver, $sender, $content)
    {
        $url = $this->host_url."/SendOtp";
        $auth = $this->auth($username, $password);
		
	    ManageLogging::hideKeys(
			InformationMasking::getOtpToMask($content)
	    );

        $payload =
            [
                "authentication" =>
                [
                    "sessionId" => $this->sessionId($auth),
                    "contextId" => $this->contextId($auth)
                ],
                "recipient" => $receiver,
                "messageContent" => $content,
                "iysBrandCode" => "string",
                "sender" => $sender,
                "iysRecipientType" => "Bireysel",
                "iysMessageType" => "Bilgilendirme",
                "trustDate" => ManipulateDate::toIso8601ZuluString(ManipulateDate::toNow()),
                "useSimOtp" => false
            ];
        
        $json_payload = Json::encode($payload, JSON_UNESCAPED_UNICODE);

        $response = Json::decode(Encode::toUtf8($raw = Curl::withAction("CODEC_PLUS_SEND",true)::post($url, $this->headers(), $json_payload)), true);

        return $response;

    }

    public function getTrustBlacklistItem($username, $password, $receiver,$message): bool
    {

        $url = $this->host_url."/TrustBlacklistItem";
        $auth = $this->auth($username, $password);

        $payload =
            [
                "authentication" =>
                    [
                        "sessionId" => $this->sessionId($auth),
                        "contextId" => $this->contextId($auth)
                    ],
                "recipient" => $receiver,
                "trustDate" => NULL,
                "trustLevel" => self::TRUST_LEVEL_MAKER,
                "trustMessage" => $message,

            ];

        $json_payload = Json::encode($payload, JSON_UNESCAPED_UNICODE);

        $response = Json::decode(Encode::toUtf8($raw = Curl::withAction("CODEC_PLUS_GET_TRUST_BLACKLIST",true)::post($url, $this->headers(), $json_payload)), true);

        return self::isSuccessfulResponse($response);
    }


    public function isSuccessfulResponse($response)
    {
        $result_code = $response["resultCode"] ?? null;
        $is_success = $response["isSuccess"] ?? false;

        if($is_success && ($result_code === 0 || $result_code === 100)){
            return true;
        }

        return false;

    }


    public function sessionId($response)
    {
        return $response["authData"]["sessionId"] ?? "";
    }

    public function contextId($response)
    {
        return $response["authData"]["contextId"] ?? "";
    }

    private function headers():array
    {
        return
            [
                'Content-Type: application/json',
            ];
    }

    public function unsetCodecLogResponseData($data){
        return InformationMasking::unsetKeys($data, false, [
            'requestId', 'messageId',
        ]);
    }

}