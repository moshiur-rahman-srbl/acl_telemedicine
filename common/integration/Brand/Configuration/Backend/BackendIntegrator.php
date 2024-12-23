<?php
	
	namespace common\integration\Brand\Configuration\Backend;
	
	use common\integration\Utility\Arr;
	use common\integration\Utility\Helper;
	
	class BackendIntegrator
	{
		public static function allowOtpLessLogin(){
			$brand_code = config('brand.name_code');
			$brand_list = [];
			
			if($brand_code == config('constants.BRAND_NAME_CODE_LIST.SP') && !Helper::isProdServerEnvironment()){
				$brand_list = Arr::merge($brand_list, [
					config('constants.BRAND_NAME_CODE_LIST.SP')
				]);
			}
			
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
		
		public static function allowUserListForOtpLessLogin($email)
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.SP'),
			];
			
			$email_list = [];
			if($brand_code == config('constants.BRAND_NAME_CODE_LIST.SP')){
				$email_list = Arr::merge($email_list, [
					"murat.kurucay@softrobotics.com.tr",
				]);
			}
			
			return Arr::isAMemberOf($brand_code, $brand_list) && Arr::isAMemberOf($email, $email_list);
		}
	}