<?php

namespace common\integration\Utility;

use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Backend\BackendCcpayment;
use common\integration\BrandConfiguration;
use common\integration\GlobalFunction;

class Language{



    public static function getSystemLanguage(){
        return app()->getLocale();
    }

    public static function setSystemLanguage($language = ''){

        if(empty($language)){
            $language = self::getSystemLanguage();
        }

        app()->setLocale($language);

    }
	
	public static function getBrowserLanguage(){
		
		$language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null;
		
		if(!empty($language)){
			$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
			
			if(!empty(request()->lang)){
				$lang = request()->lang;
			}
			
			if(GlobalFunction::hasBrandSession('locale')){
				$lang = GlobalFunction::getBrandSession('locale');
			}
			
			return app()->setLocale($lang);
		}
		if(BrandConfiguration::allowSpanishLanguage()){
			return app()->setLocale(app()->getLocale());
		}else{
			return config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.MP') ? app()->setLocale('lt')  : app()->setLocale('tr');
		}
		
		
	}

    public static function isLocalize($message, $variable = [], bool $is_err_response=false): string
    {
        if(Arr::isOfType($variable)) {
            if(BrandConfiguration::isApiResponseLocalize()) {
                return __($message, $variable);
            }

            if(BrandConfiguration::call([BackendCcpayment::class, 'allowLocalizationForApiError']) && $is_err_response) {
                return __($message, $variable);
            }
    
            if(!empty($variable)) {
                foreach($variable as $key => $value) {
                    $message = Str::replace(':' . $key, $value, $message);
                }
            }
        }
        return $message;
    }

}