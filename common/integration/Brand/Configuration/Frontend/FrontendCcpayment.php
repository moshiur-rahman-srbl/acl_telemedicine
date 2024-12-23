<?php
	
	namespace common\integration\Brand\Configuration\Frontend;
	
	use common\integration\Utility\Arr;

    class FrontendCcpayment
	{
        public static function isAllowCustomPaymentFailedText(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
		
		public static function allowCustomCcpaymentLoginImageDesign()
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PM'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
	}