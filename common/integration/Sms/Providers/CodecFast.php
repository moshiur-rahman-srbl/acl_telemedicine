<?php

namespace common\integration\Sms\Providers;
use common\integration\ApiService;
use common\integration\Utility\Curl;
use common\integration\Utility\Encode;
use common\integration\Utility\Json;

class CodecFast
{

    public $response = [];
    public $response_code = '';
    public $host_url = '';
    public $headers = [];

    const RESULT_SUCCESS_CODE = 0;
    const SUCCESS_CODE = 100;

    public function send($username, $password, $receiver, $sender, $content)
    {
        $payload =
          [
            'username' => $username,
            'password' => $password,
            'sender' => $sender,
            'headerCode' => null,
            'messageSubject' => '',
            'recipientType' => 'Msisdn',
            'recipientList' => [
              $receiver
            ],
            'contentList' => [
              $content
            ],
            'optionalParameters' => null,
            'iysBrandCode' => null,
            'iysRecipientType' => 'Bireysel',
            'iysMessageType' => 'BILGILENDIRME'

          ];

        $json_payload = Json::encode($payload, JSON_UNESCAPED_UNICODE);
        $this->response = Json::decode(Encode::toUtf8($raw = Curl::post($this->host_url, $this->headers(), $json_payload)), true);
        $this->response_code =  $this->isSuccessfulResponse($this->response) ? ApiService::API_SERVICE_HTTP_CODE_VALID_REQUEST : ApiService::API_SERVICE_FAILED_CODE;

    }

    public function isSuccessfulResponse($response)
    {
        $result_code = $response['detailedResultList'][0]['resultCode'] ?? null;
        $is_success = $response["isSuccess"] ?? false;

        if($is_success && ($result_code === self::RESULT_SUCCESS_CODE || $result_code === self::SUCCESS_CODE)){
            return true;
        }

        return false;

    }

    private function headers():array
    {
        return
          [
            'Content-Type: application/json',
          ];
    }

    public function hostUrl($host_url):string
    {
        return $this->host_url = $host_url;
    }
}