<?php

namespace common\integration\Utility;

use App\Models\MerchantSettings;
use common\integration\Models\Merchant;
use common\integration\Models\ServiceCredential;
use Carbon\Carbon;
use common\integration\Models\UserApiCredential;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Matrix\Exception;
use function PHPUnit\Framework\returnArgument;

class JsonWebToken
{
    const JWT_ACTIVITY_FASTPAY_WALLET_PAYMENT = 1;
    const JWT_ACTIVITY_INTERNAL_APPLICATION_SERVICE= 2;


    public $activity;
    private $payload;
    private $hash_algorithm;
    private $life_time;
    private $iat;
    private $exp;
    private $sub;
    private $scopes;
    private $nbf;
    private $aud;
    private $iss;
    private $name_id;
    private $hash_key_name;
    public $hash;
    public $token_payload;
    private $secret_key;
    private $jwt_algorithm;
    public $token;
    private $cid;
    private $service_client_id;
    private $service_id;

    private $user_api_credential;
    private $is_authenticable;

    public static $service_algs =
        [
            ServiceCredential::FASTPAY_WALLET_SERVICE_ID =>array('HS512','openssl','SHA512'),
            ServiceCredential::FEATURE_API_SERVICE_ID =>array('HS512','openssl','SHA512'),
        ];

    public function getUserApiCredential()
    {
        return $this->user_api_credential;
    }

    public function isAuthenticable()
    {
        return $this->is_authenticable;
    }

    public function set():self
    {
        return $this;
    }

    public function  sub($sub)
    {
        $this->sub = $sub;

        return $this;
    }

    public function scopes($scopes = [])
    {
        $this->scopes = $scopes;
        return $this;
    }

    public function activity($activity):self
    {
        $this->activity = $activity;
        return $this;
    }

    public function payload($payload):self
    {
        if(is_string($payload)){
            $this->payload = json_encode(json_decode($payload));
        }else{
            $this->payload = $payload;
        }

        return $this;
    }

    public function hashAlgorithm($hash_algorithm):self
    {
        $this->hash_algorithm = $hash_algorithm;

        return $this;
    }

    public function lifeTime($life_time):self
    {
        $this->life_time = $life_time;

        return $this;
    }


    public function timeStamps():self
    {

            $this->set()
                ->issuedAt()
                ->expiredTime()
                ->notBeforeTime();

        return $this;
    }

    public function issuedAt():self
    {
        $this->iat = Carbon::now()->timestamp;

        return $this;


    }

    public function expiredTime():self
    {
        $this->exp = Carbon::parse($this->iat)->addMinutes($this->life_time)->timestamp;

        return $this;
    }

    public function notBeforeTime():self
    {

        $this->nbf = Carbon::parse($this->iat)->subMinutes($this->life_time)->timestamp;

        return $this;

    }

    public function issuer($issuer):self
    {
        $this->iss = $issuer;

        return $this;
    }

    public function audience($audience):self
    {
        $this->aud = $audience;

        return $this;
    }

    public function nameId($name_id):self
    {
        $this->name_id = $name_id;

        return $this;
    }

    public function hashKeyName($hash_key_name):self
    {
        $this->hash_key_name = $hash_key_name;

        return $this;
    }

    public function secretKey($secret_key):self
    {
        $this->secret_key = $secret_key;

        return $this;
    }

    public function serviceClientId($service_client_id):self
    {
        $this->service_client_id = $service_client_id;

        return $this;
    }


    public function serviceId($service_id):self
    {
        $this->service_id = $service_id;

        return $this;
    }

    public function tokenPayload():self
    {
        if($this->activity == self::JWT_ACTIVITY_FASTPAY_WALLET_PAYMENT) {
            $this->token_payload =
                [
                    $this->hash_key_name => strtoupper($this->hash),
                    'nameid' => $this->name_id,
                    'nbf' => $this->nbf,
                    'exp' => $this->exp,
                    'iat' => $this->iat,
                    'iss' => $this->iss,
                    'aud' => $this->aud
                ];

        }
        elseif($this->activity == self::JWT_ACTIVITY_INTERNAL_APPLICATION_SERVICE){
            $this->token_payload =
                [
                    $this->hash_key_name => $this->hash,
                    'aud' => $this->aud,
                    'sub' => $this->sub,
                    'cid' => $this->cid,
                    'nbf' => $this->nbf,
                    'exp' => $this->exp,
                    'iat' => $this->iat,
                    'scopes' => $this->scopes

                ];

        }

        return $this;
    }

    public function jwtAlgorithm($jwt_algorithm):self
    {
        $this->jwt_algorithm = $jwt_algorithm;

        return $this;
    }

    public function clientId($client_id):self
    {

        $this->cid = $client_id;

        return $this;

    }

    public function credentials($issuer=null,
                                $audience=null,
                                $hash_key_name=null,
                                $name_id=null,
                                $client_id=null,
                                $sub = null, $scopes = []):self
    {
        if($this->activity == self::JWT_ACTIVITY_FASTPAY_WALLET_PAYMENT) {
            $this->set()
                ->issuer($issuer)
                ->audience($audience)
                ->hashKeyName($hash_key_name)
                ->nameId($name_id);
        }
        elseif($this->activity == self::JWT_ACTIVITY_INTERNAL_APPLICATION_SERVICE) {
            $this->set()
                ->hashKeyName($hash_key_name)
                ->audience($audience)
                ->clientId($client_id)
                ->sub($sub)
                ->scopes($scopes);

        }


        return $this;
    }



    public function create():string
    {
        switch($this->activity) {
            case self::JWT_ACTIVITY_FASTPAY_WALLET_PAYMENT:
                $this->hash = hash($this->hash_algorithm, $this->payload);
                break;
            case self::JWT_ACTIVITY_INTERNAL_APPLICATION_SERVICE:
                $this->hash = hash_hmac($this->hash_algorithm, $this->payload, $this->secret_key, false);
                break;
        }

        $this->set()
            ->tokenPayload();

        $jwt = JWT::encode($this->token_payload, $this->secret_key, $this->jwt_algorithm);

        return $this->token = $jwt;

    }

    public function token ($token):self
    {
        $this->token = $token;

        return $this;
    }



    public function valid($content, $token, $merchantSettings = null)
    {
        $token_parts = explode('.', $token);

        if(count($token_parts) != 3){
            throw new Exception('Invalid Token.');
        }

        $payload = json_decode(base64_decode($token_parts[1]))??null;

        if(empty($payload)){
            throw new \Exception('Payload is null.');
        }

        $sub = $payload->sub?? null; // merchant id for MERCHANT_API_CREDENTIAL_SERVICE_ID service
        $service_id = $payload->aud??null;
        $service_client_id = $payload->cid??null; //admin id for MERCHANT_API_CREDENTIAL_SERVICE_ID service


        if(empty($service_id) || empty($service_client_id)){
            throw new \Exception('Malformed payload.');
        }


        if ($service_id == ServiceCredential::MERCHANT_AUTHENTICATION_SERVICE_ID && empty($merchantSettings)) {
            $merchantSettings = (new MerchantSettings())->findById($sub);
        }

        if ($service_id == ServiceCredential::USER_API_CREDENTIAL_SERVICE_ID) {
            $service_client_secret = $this->resolveUserApiCredentials($service_client_id);
        }

        if ($service_id == ServiceCredential::MERCHANT_API_CREDENTIAL_SERVICE_ID) {
            $service_client_secret = $this->resolveMerchantApiCredentials($content,$service_client_id,$sub);
        }
        if ($service_id == ServiceCredential::PAX_API_CREDENTIAL_SERVICE_ID) {
            $service_client_secret = $this->resolvePaxApiCredentials($service_client_id);
        }

        if (!empty($merchantSettings)) {
            $service_client_secret = $merchantSettings->app_secret;
        }
        if (empty($service_client_secret)) {
            $service_client_secret = (new ServiceCredential())->getServiceClientSecret($service_id, $service_client_id);
        }

        if(empty($service_client_secret)){
            throw new \Exception('Invalid claim credentials.');
        }


        if($service_id == ServiceCredential::MERCHANT_AUTHENTICATION_SERVICE_ID){
            $payload_hash = $payload->jti;
        }else {
            $payload_hash = $payload->hash;
        }

        list($alg, $function, $hash_alg) = self::$service_algs[$service_id] ?? null;

        if(empty($alg)){
            $alg = 'HS512';
        }
        if(empty($function)){
            $function = 'openssl';
        }

        if(empty($hash_alg)){
            $hash_alg = 'SHA512';
        }

        if(empty($alg) || empty($function) || empty($hash_alg)){
            throw new \Exception('Invalid service id.');
        }



        $is_valid_hash = false;

        switch ($function) {
            case 'hash_hmac':
            default:
                $hash = \hash_hmac($hash_alg, $content, $service_client_secret, false);
                if (\function_exists('hash_equals')) {
                    $is_valid_hash = \hash_equals($payload_hash, $hash);
                }

        }




        if(!$is_valid_hash){
            throw new \Exception('Hash mismatched.');
        }



        return JWT::decode($token, new Key($service_client_secret, $alg));


    }

    public static function getToken($request){
        $token = $request->header('Authorization');
        if (substr($token, 0, 7) == 'Bearer ') {
            $token = substr($token, 7);
        }

        return $token;

    }

    public static function getPayload($token)
    {
        $token_parts = explode('.', $token);

        $payload = json_decode(base64_decode($token_parts[1] ?? null))??null;

        return $payload;

    }

    public static function getSub($payload){
        return $payload->sub ?? null;
    }

    public static function getCid($payload){
        return $payload->cid ?? null;
    }

    public static function getServiceId($payload)
    {
        return $payload->aud??null;

    }

    public function expiredAt()
    {
        return Carbon::parse($this->exp)->setTimezone(date_default_timezone_get())->toDateTimeString();
    }

    private function resolveMerchantApiCredentials($content,$service_client_id,$sub)
    {

        $service_client_secret = null;
        $merchant_key = null;

        if (!empty($service_client_id) && !empty($sub) && !empty($request_merchant_id = Json::decode($content)->merchant_id) && $request_merchant_id != $sub) {
            throw new \Exception('Merchant id not match.');
        }
        if (!empty($service_client_id) && !empty($sub)) {
            $merchantObj = (new Merchant())->getMerchantByMerchantId($sub);
            if (!empty($merchantObj)) {
                $merchant_key = $merchantObj->merchant_key ?? '';
            }
        }
        $this->is_authenticable = true;
        $this->user_api_credential = (new UserApiCredential())->findByUserId($service_client_id);
        if (!empty($this->user_api_credential) && !empty($merchant_key)) {
            $service_client_secret = !empty($this->user_api_credential->client_secret) ? $this->user_api_credential->client_secret.$merchant_key : '' ;
        }

        return $service_client_secret;

    }

    private function resolveUserApiCredentials($service_client_id)
    {
            $service_client_secret = null;
            $this->is_authenticable = true;
            $this->user_api_credential = (new UserApiCredential())->findByClientId($service_client_id);
            if (!empty($this->user_api_credential)){
                $service_client_secret = $this->user_api_credential->client_secret;
            }

            return $service_client_secret;

    }
    private function resolvePaxApiCredentials($service_client_id)
    {
        $service_client_secret = null;
        if(!empty($service_client_id)){
            $this->is_authenticable = true;
            $this->user_api_credential = (new UserApiCredential())->findByClientId($service_client_id);
            if (!empty($this->user_api_credential)) {
                $service_client_secret = !empty($this->user_api_credential->client_secret) ? $this->user_api_credential->client_secret: '' ;
            }
        }

        return $service_client_secret;

    }



}