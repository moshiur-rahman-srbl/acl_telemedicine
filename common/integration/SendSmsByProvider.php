<?php


namespace common\integration;


use App\Http\Controllers\Traits\CommonLogTrait;
use App\Models\Country;
use App\Models\Profile;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\Models\SmsArchive;
use common\integration\Models\UserBlockPhone;
use common\integration\Sms\Providers\CodecFast;
use common\integration\Sms\Providers\JetSms;
use common\integration\Sms\SmsProviderErrorCodeHandler;
use common\integration\Sms\Providers\CodecPlus;
use common\integration\Utility\Exception;
use common\integration\Utility\Json;
use common\integration\Utility\Phone;
use common\integration\Utility\Str;
use common\integration\Utility\Xml;
use common\integration\Utility\Curl;


class SendSmsByProvider
{
    use CommonLogTrait;
    private $user_name;
    private $password;
    private $header;
    private $message;
    private $phone;
    private $otp_from;
    private $provider;

    private $response;
    private $response_code;
    private $exception;

    public $error_message = '';

	public $sms_provider_original_response = '';
	public $is_sim_blocked = false;
	public $sim_card_block_message = null;

	public $block_type = null;
	public $enable_block_by_provider_check = false;

    public function __construct($username, $password, $header, $message, $phone, $otp_from_name, $provider)
    {
        $this->user_name = $username;
        $this->password = $password;
        $this->header = $header;

//	    if(BrandConfiguration::enableTurkishLatterCustomizedForSmsSending()){
//		    $message = Str::customCaseConversion($message, 'convertTurkishCharactersToEnglishAsItIs');
//		}


        $this->message = $message;
        $this->phone = $phone;
        $this->otp_from = $otp_from_name;
        $this->provider = $provider;

    }

    public function sendSms(){

        if ($this->provider == config('constants.SMS_PROVIDER_NAMES.MOBILISIM')){
            $this->sendByMOBILISIM();
        }elseif ($this->provider == config('constants.SMS_PROVIDER_NAMES.CODEC')){
            $this->sendByCODEC();
        }elseif ($this->provider == config('constants.SMS_PROVIDER_NAMES.ISOBIL')){
            $this->sendByISOBIL();
        }elseif($this->provider == config('constants.SMS_PROVIDER_NAMES.PISANO')){
            $this->sendByPisano();
        }elseif($this->provider == config('constants.SMS_PROVIDER_NAMES.JETSMS')) {
            $this->sendByJetSms();
        }elseif($this->provider == config('constants.SMS_PROVIDER_NAMES.VERIMOR')){
            $this->sendByVerimor();
        }elseif($this->provider == config('constants.SMS_PROVIDER_NAMES.POSTAGUVERCINI')){
            $this->sendByPostguvercini();
        }elseif($this->provider == config('constants.SMS_PROVIDER_NAMES.CODEC_PLUS')){
            $this->sendByCodecPlus();
        }elseif($this->provider == config('constants.SMS_PROVIDER_NAMES.ATPAY')){
			$this->sendByAtPay();
        }elseif($this->provider == config('constants.SMS_PROVIDER_NAMES.CODEC_FAST')){
            $this->sendByCodecFast();
        }

        $logData['action'] = 'SMS Sending';
        if ($this->response_code == 200) {
            $logData['status'] = 'Success';
            $this->error_message = '';
        } else {
            $logData['status'] = 'Failed';
            $this->error_message = isset($this->response) ? (!$this->response ? 'false' : $this->response) : 'Unknown error';
        }

        $logData['response'] = $this->response;
        $logData['mobile'] = $this->phone;
        $sms_provider = $this->provider;
        if(BrandConfiguration::sendSmsProviderNameChangeAsBrandName()){
            $sms_provider = Str::upperCase(config('brand.name'));
        }
        $logData['sms_provider'] = $sms_provider;

        if(BrandConfiguration::allowSmsLogs()){

            $userObj = GlobalUser::getUserByPhone($this->phone);
            $prepare_data = [
                'to_gsm' => $this->phone,
                'user_id' => @$userObj->id ?? 0,
                'user_type' => @$userObj->user_type,
                'provider' => $sms_provider,
                'content' => $this->message,
                'response' => $this->prepareSmsResponseData()
            ];

            (new SmsArchive())->saveData($prepare_data);
        }

        $logData = $this->_getCommonLogData($logData);
        $manageLog = new ManageLogging();
        $log_type = $manageLog::setLogType($this->_getCommonLogData($logData));
        $manageLog->createLog($logData, true, $log_type);

	    $this->is_sim_blocked = SmsProviderErrorCodeHandler::isSimBlocked($this->provider,
		    $this->sms_provider_original_response);

		$this->sim_card_block_message = SmsProviderErrorCodeHandler::simCardBlockedMessage($this->provider,
			$this->sms_provider_original_response);

		$this->block_type = SmsProviderErrorCodeHandler::getSimBlockType($this->provider,
			$this->sms_provider_original_response);

        //$this->createLog($this->_getCommonLogData($logData));

    }

    private function prepareSmsResponseData(){

        $message = '';

        try{

            if($this->provider == config('constants.SMS_PROVIDER_NAMES.PISANO')){

                if($this->response === false){

                    $message = 'Original Response = false message = Not able to connect with Wsdl server';

                }elseif(!empty($this->response)){

                    $response_array = Xml::toArr($this->response,['sendSMSPNReturn', 'smg.esb.mediation.exception.SMGException']);

                    if(!empty($response_array) && is_array($response_array)){

                        if(isset($response_array['responseId']) && Str::isString($response_array['responseId'])){
                            $message = 'responseId ='.$response_array['responseId'];
                        }

                        if(isset($response_array['responseMessage']) && Str::isString($response_array['responseMessage'])){
                            $message .= ' responseMessage ='.$response_array['responseMessage'];
                        }

                        if(isset($response_array['id']) && Str::isString($response_array['id'])){
                            $message .= ' responseId ='.$response_array['id'];
                        }

                        if(isset($response_array['message']) && Str::isString($response_array['message'])){
                            $message .= ' responseMessage ='.$response_array['message'];
                        }

                    }

                }

            }


        }catch(\Throwable $e){

            $log_data['action'] = 'SMS_RESPONSE_FORMATTER_EXCEPTION';
            $log_data['message'] = Exception::fullMessage($e);
            $log_data['response'] = $this->response;
            (new ManageLogging())->createLog($log_data);

            $message = $log_data['message'];
        }

        return $message;

    }


    private function sendByMOBILISIM()
    {

        $ch = curl_init();
        $request = array(
            CURLOPT_URL => 'http://gw2.mobilisim.com/api/json/reply/Submit',
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
            CURLOPT_POSTFIELDS => '{
            "Credential": {
            "Username": "' . $this->user_name . '",
            "Password": "' . $this->password . '"
            },
            "Header": {
            "From": "' . $this->otp_from . '",
            "ScheduledDeliveryTime": "",
            "ValidityPeriod": 0
            },
            "Message": "' . $this->message . '",
            "To": [
                "' . $this->phone . '"
            ],
            "DataCoding": "Default"
            }'

        );

        curl_setopt_array($ch, $request);
        $this->response = curl_exec($ch);
        $this->response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }

    private function sendByCODEC()
    {
        $phones = str_replace('+', '00', $this->phone);
        $ch = curl_init();
        $fields = [
            'userName' => $this->user_name,
            'password' => $this->password,
            'sender' => $this->otp_from,
            'phone' => $phones,
            'messageContent' => $this->message,
            'msgSpecialId' => '',
            'isOtn' => 'false',
            'responseType' => 3,
            'headerCode' => '',
            'optionalParameters' => '',
            'iysBrandCode' => '',
            'iysRecipientType' => '',
            'iysMessageType' => 'BILGILENDIRME'

        ];
        $fields_string = http_build_query($fields);
        $headers = [
            'Content-Type: application/x-www-form-urlencoded'
        ];

		$post_url = 'https://fastsms-api.codec.com.tr/Soap.asmx/SendSms';

		if(!empty(config('constants.OTP_SEND_URL'))){
			$post_url = config('constants.OTP_SEND_URL');
		}

        $curl_request_parameters = [
            CURLOPT_URL => $post_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_HTTPHEADER => $headers,
        ];


        if(BrandConfiguration::curlRequestVerifyFalseForCodec()){
            $curl_request_parameters = $curl_request_parameters + [
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_SSL_VERIFYHOST => 2
                ];
        }

        curl_setopt_array($ch, $curl_request_parameters);
        $this->response = curl_exec($ch);
        $this->response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

    }

    public function sendByCodecPlus()
    {
        $receiver = str_replace('+', '00', $this->phone);
        $response = ($codecPlus = new CodecPlus())->send($this->user_name, $this->password, $receiver, $this->otp_from, $this->message);
        $this->response = $codecPlus->unsetCodecLogResponseData($response);

        if($codecPlus->isSuccessfulResponse($response)){
            $this->response_code = 200;
        }else{
            $this->sms_provider_original_response = $response['resultStatus'] ?? null;
            $this->response = $response ?? null;
            $this->response_code = $response['resultCode'] ?? null;
        }
    }

    private function sendByISOBIL()
    {
        $phones = str_replace('+', '', $this->phone);
        $field_data =
            [
                'Credential' =>
                    [
                        'Username' => $this->user_name,
                        'Password' => $this->password,
                        'SystemCode' => 1000,
                    ],
                'MessageType' => 'BILGILENDIRME',
                'Message' => $this->message,
                'DataCoding' => 'Default',
                'BlackListControl' => 'false',
                'Header' =>
                    [
                        'ServiceID' => 18,
                        'From' => $this->otp_from,
                    ],
                'To' =>
                    [$phones]
            ];

        $field_json_data = json_encode($field_data);

        $url = 'https://api.isobil.com.tr/v5/isobil_API_JSON.asmx?op=Submit';

        $headers =
            [
                'content-type: text/xml'
            ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '<?xml version="1.0" encoding="utf-8"?>
                                            <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                                              <soap:Body>
                                                <Submit xmlns="https://api.isobil.com.tr">
                                                  <data><![CDATA[' . $field_json_data . ']]></data>
                                                </Submit>
                                              </soap:Body>
                                            </soap:Envelope>',
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $this->response = curl_exec($ch);
        $this->response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }

    private function sendByVerimor()
    {
        $phones = str_replace('+', '00', $this->phone);
        $sms_msg = array(
            "username" => $this->user_name, // https://oim.verimor.com.tr/sms_settings/edit adresinden öğrenebilirsiniz.
            "password" => $this->password, // https://oim.verimor.com.tr/sms_settings/edit adresinden belirlemeniz gerekir.
            "source_addr" => $this->header, // Gönderici başlığı, https://oim.verimor.com.tr/headers adresinde onaylanmış olmalı, değilse 400 hatası alırsınız.
            //            "custom_id" => "1424441160.9331344",
            "messages" => array(
                array(
                    "msg" => $this->message,
                    "dest" => $phones
                )
            )
        );
        if(config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.FP')){
            $sms_msg["valid_for"] = "00:03";
        }
        //search for turkish character VEP-598
        if(BrandConfiguration::allowSmsDataCoding() && preg_match("/[ŞşĞğçıİ]/im",$this->message)){
            $sms_msg["datacoding"] = "1";
        }

        $url = 'https://sms.verimor.com.tr/v2/send.json';

        if(BrandConfiguration::curlRequestVerifyFalseForVerimore()){
            $url = 'http://sms.verimor.com.tr/v2/send.json';
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_POSTFIELDS => json_encode($sms_msg),
        ));
        $this->response = curl_exec($ch);
        $this->response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

    }

    public function sendByPisano(){

        /* $actual_phone_no = Str::replace('+', '', $this->phone);
        $country_code = Str::sartEndSubStr($actual_phone_no, 0, 2);
        $phone_no = Str::removeFromFirst($actual_phone_no, 2); */
        list($country_code, $phone_no, $actual_phone_no) = Phone::getCountryPhoneCode($this->phone);

        $header = [
            'Content-Transfer-Encoding: text/xml',
            'Content-Type: application/xml;charset=UTF-8',
        ];

        $url = config('constants.OTP_SEND_URL');

        // if(BrandConfiguration::checkProductionUrlForSmsAndEmail()){
        //     $url = 'https://fbextranet.finansbank.com.tr/FBWS/esbDispatcher/esbDispatcher';
        // }else{
        //     $url = 'https://fbextranettest.finansbank.com.tr/FBWS/esbDispatcher/esbDispatcher';
        // }

        $request_setup = '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:obj="http://objects.delivery.smg.ibtech.com">
        <soapenv:Header/>
        <soapenv:Body>
            <obj:sendSMSPN soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
                <in0 xsi:type="int:BasicWebRequest" xmlns:int="http://interfaces.webservices.esb.smg">
                    <password xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.$this->password.'</password>
                    <serviceName xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/"></serviceName>
                    <userName xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.$this->user_name.'</userName>
                </in0>
                <in1 xsi:type="obj:SMSPNRequest">
                    <application xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.$this->otp_from.'</application>
                    <countryCode xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.$country_code.'</countryCode>
                    <formCode xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.config('constants.OTP_FROM_CODE').'</formCode>
                    <message xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.$this->message.'</message>
                    <to xsi:type="soapenc:string" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/">'.$phone_no.'</to>
                </in1>
            </obj:sendSMSPN>
        </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>$request_setup,
            CURLOPT_HTTPHEADER => $header,
        ));

        $response = curl_exec($curl);

        $this->response = $response;
        $this->response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

    }

    public function sendByJetSms()
    {
        $username = $this->user_name;
        $password = $this->password;
        $message = $this->message;
	    $gsmNo = Str::replace('+', '', $this->phone);

	    $simcheckinday = $mnpcheckinday = "";

	    $userObj = auth()->check() ? auth()->user() : GlobalUser::getUserByPhone($this->phone);

	    if (BrandConfiguration::allowSimCardBlock() && $userObj && $userObj->user_type == Profile::CUSTOMER) {

		    list($simcheckinday, $mnpcheckinday) = (new UserBlockPhone())->getSimCheckingsDays($userObj->id);

	    }
	    // $is_check_international_no = self::isInternationalPhoneNumber($gsmNo);

	    $is_enable_new_flow = BrandConfiguration::call([Mix::class, 'enableJetSmsNewFlow']);
	    $jetSms = new JetSms($username, $password, $this->otp_from, $is_check_international_no = false);

	    $response = $jetSms->sendSms(
		    $is_enable_new_flow,
		    $gsmNo,
		    $message,
		    $simcheckinday,
		    $mnpcheckinday
	    );

	    $this->sms_provider_response = $this->sms_provider_original_response = $response;
	    $this->response = @$response->ResponseCode;
	    $this->response_code = Curl::$http_code;


    }

    public function sendByPostguvercini()
    {

        $actual_phone_no = Str::replace('+','',$this->phone);
        $country_code = Str::sartEndSubStr($actual_phone_no,0,2);
        $phone_no = Str::removeFromFirst($actual_phone_no, 2);


        $prepare_xml = '<?xml version="1.0" encoding="iso-8859-9"?>
                    <SMS-InsRequest>
                        <CLIENT user="'.$this->user_name.'" pwd="'.$this->password.'"/>
                        <INSERTMSG text="'.$this->message.'">
                            <TO>'.$phone_no.'</TO>
                        </INSERTMSG>
                    </SMS-InsRequest>';

        $header = [
            'Content-Type: application/xml',
        ];

        //$url = https://sr-app.yemekpay.com/ccpayment/api/token
        $url = config('constants.OTP_SEND_URL');

        $this->response = Curl::post($url, $header, $prepare_xml);
        $this->response_code = Curl::$http_code;

    }

	private static function isInternationalPhoneNumber($number){
		$status = false;

		$country_code = Str::sartEndSubStr($number, 0, 2);
		if($country_code != Country::TUR_COUNTRY_CODE){
			$status = true;
		}

		return $status;
	}

	public function sendByAtPay(){

		$request_params = [
			'GsmNumber' => Str::replace('+','',$this->phone),
			'NotificationType' => 59,
			'Parameters' => [
				'MessageContent' => $this->message
			],
		];

		$this->response = $this->sms_provider_response = Curl::post(
			config('constants.OTP_SEND_URL'),
			['Content-Type: application/json'],
			Json::encode($request_params)
		);

		$this->response_code = Curl::$http_code;

	}

    private function sendByCodecFast()
    {
        $receiver = str_replace('+', '00', $this->phone);
        $host_url = 'https://smsgateway.codec.com.tr/api/Fast/SendSms';
        if(!empty(config('constants.OTP_SEND_URL'))){
            $host_url = config('constants.OTP_SEND_URL');
        }

        $codecFast = new CodecFast();
        $codecFast->hostUrl($host_url);
        $codecFast->send($this->user_name, $this->password, $receiver, $this->otp_from, $this->message);
        $this->response = $codecFast->response;
        $this->response_code = $codecFast->response_code;

    }
}
