<?php

namespace common\integration\Utility;

use App\Http\Controllers\Traits\ResourceContainerTrait;
use App\Http\Middleware\Authenticate;
use App\Models\MerchantSettings;
use common\integration\ApiService;
use common\integration\BrandConfiguration;
use common\integration\DTOs\TokenValidationResult;
use common\integration\Models\ServiceCredential;

class Token
{
    use ResourceContainerTrait;

    const RESOURCE_KEY = 'ALLOW_TOKENLESS_AUTH';

    public static function enableTokenless(){
        return (new self)->setResource(self::RESOURCE_KEY, 1);
    }

    public static function isAllowTokenless(){
        return (new self)->getResource(self::RESOURCE_KEY);
    }


    public function createAccessToken($merchantSettings)
    {
        $content = $this->authContent($merchantSettings);

        $jsonWebToken = new JsonWebToken();

        $service_id = ServiceCredential::MERCHANT_AUTHENTICATION_SERVICE_ID;

        $service_client_secret = $merchantSettings->app_secret;

        $token = $jsonWebToken
            ->set()
            ->activity(JsonWebToken::JWT_ACTIVITY_INTERNAL_APPLICATION_SERVICE)
            ->payload($content)
            ->hashAlgorithm('SHA512')
            ->lifeTime(120)
            ->timeStamps()
            ->credentials(null,$service_id,'jti','',$merchantSettings->merchant_id, $merchantSettings->id)
            ->jwtAlgorithm('HS512')
            ->secretKey($service_client_secret)
            ->create();

        return $jsonWebToken;
    }

    public function authenticateMerchant($request, $guards)
    {
        $tokenValidationResult = new TokenValidationResult();

        if ((Arr::isOfType($guards) && Arr::isAMemberOf('api', $guards))
            || (Str::isString($guards) && $guards == 'api'))
            {
                $token = JsonWebToken::getToken($request);
                $payload = JsonWebToken::getPayload($token);
                $sub = JsonWebToken::getSub($payload);
                $serviceId = JsonWebToken::getServiceId($payload);


                if(!empty($sub) && $serviceId == ServiceCredential::MERCHANT_AUTHENTICATION_SERVICE_ID){
                    $merchantSettings = (new MerchantSettings())->findById($sub);
                }

                if(!empty($merchantSettings)) {
                    $content = (new Token())->authContent($merchantSettings);
                    $tokenValidationResult->wentThrough = true;
                    try {
                        $jsonWebToken = new JsonWebToken();
                        $valid = $jsonWebToken->valid($content, $token, $merchantSettings);

                        if($valid) {
                            $tokenValidationResult->isSuccessful = $valid;
                        }

                    } catch (\Throwable $e) {
                        Exception::log($e, "AUTH_EXCEPTION");
                        $tokenValidationResult->isSuccessful = false;
                    }
                }
            }

        return [$tokenValidationResult, $merchantSettings ?? null];
    }

    public function authContent($merchantSettings)
    {
         $data = [
             "app_id" => $merchantSettings->app_id,
             "app_secret" => $merchantSettings->app_secret
         ];

         $content = Json::encode($data);

         return $content;
    }

}

