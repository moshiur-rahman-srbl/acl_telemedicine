<?php

namespace common\integration\Utility;

use common\integration\BrandConfiguration;
use common\integration\DataCipher;
use common\integration\GlobalFunction;
use common\integration\ManageLogging;

class Url{
    public static function parse($url, $component = -1): array
    {
        $parsed_url_query = parse_url($url, $component);
        $query_array = array();
        parse_str($parsed_url_query, $query_array);
        return $query_array;
    }

    public static function isNonSecureConnection(){

        return GlobalFunction::isLocalEnvironment() || BrandConfiguration::isDisableSSLverify();
    }

    public static function buildQuery($params){
        return http_build_query($params);
    }
    
    public static function isValid($url){
        return Str::contains($url, 'https://') || Str::contains($url, 'http://');
    }
    
    public static function baseName($url){
        return basename($url);
    }

    public static function removeSlashes($url){
        if(self::isValid($url)){
            $separator = '://';
            $explode = explode($separator,$url);
            if (Arr::isOfType($explode)){
                $url = $explode[0].$separator.preg_replace('/(\/+)/','/',$explode[1]);
            }
        }
        return $url;
    }
    public static  function hostName($url){
        $parsed_url = parse_url($url);
        return $parsed_url['host'] ?? null;
    }
	
	public static function getRouteParamEncryptionDecryption($parameters, $type)
	{
		
		$data_cipher_type = 'encrypt';
		if($type == 'decode'){
			$data_cipher_type = 'decrypt';
		}
		
		try{
		
			if(!empty($parameters) && Arr::isOfType($parameters)){
				foreach ($parameters as $key => $parameter){
					
					$parameters[$key] = DataCipher::customEncryptionDecryption(
						$parameter,
						config('app.brand_secret_key'),
						$data_cipher_type,
						true,
						config('constants.ENCRYPTION_FIXED_IV',),
						config('constants.ENCRYPTION_FIXED_SALT'),
						true
					);
					
				}
			
			}else{
				
				
				$parameters = DataCipher::customEncryptionDecryption(
					$parameters,
					config('app.brand_secret_key'),
					$data_cipher_type,
					true,
					config('constants.ENCRYPTION_FIXED_IV'),
					config('constants.ENCRYPTION_FIXED_SALT'),
					true
				);
				
				
			}

			
		}catch (\Throwable $e){
			(new ManageLogging())->createLog([
				'action' => 'URL_MANIPULATION_'.$type,
				'error' => Exception::fullMessage($e),
			]);
		}
		
		return $parameters;
		
	}
	
}