<?php
	
	namespace common\integration\Brand\Configuration\Frontend;
	
	use common\integration\Utility\Arr;
	
	class FrontendMix
	{
		public static function enableOfficialWebsiteOnApplicationDown(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PL'),
				config('constants.BRAND_NAME_CODE_LIST.PP'),
				config('constants.BRAND_NAME_CODE_LIST.SR'),
				config('constants.BRAND_NAME_CODE_LIST.FL'),
				config('constants.BRAND_NAME_CODE_LIST.PM'),
				config('constants.BRAND_NAME_CODE_LIST.PC'),
				config('constants.BRAND_NAME_CODE_LIST.PB'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
        public static function isAllowChangeDPLPagePhoneTitle(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PP'),
                config('constants.BRAND_NAME_CODE_LIST.SR'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowIconForPasswordResetEmail(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }


      public static function isAllowVerifyEmailByOTP(){
          $brand_code = config('brand.name_code');
          $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
        public static function isSinglePageLogin(){
          $brand_code = config('brand.name_code');
          $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
	  
	  public static function disableOtpResendBtnWithTimeLimit(): bool
	  {
		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.PP'),
		  ];

		  return Arr::isAMemberOf($brand_code, $brand_list);
	  }

        public static function updateDplPosOption($dpl_pos_option, $merchant_setting_option)
        {
            $brand_code = config('brand.name_code');
            /*
             * SMP-2406 - we need to disable this feature for sipay
            */
            $brand_list = [
//                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];

            if(Arr::isAMemberOf($brand_code, $brand_list)){
                return $merchant_setting_option;
            }else{
                return $dpl_pos_option;
            }
        }

        public static function isShowCardNoOnTransactionReceipt(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowDatepickerDplDynamicFields(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowCardNumberMaskingInCardPlate()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PC'),
                config('constants.BRAND_NAME_CODE_LIST.PB'),
                config('constants.BRAND_NAME_CODE_LIST.PM'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
                config('constants.BRAND_NAME_CODE_LIST.VP'),
                config('constants.BRAND_NAME_CODE_LIST.FL'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowCardHolderNameMaskingInCardPlate()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PC'),
                config('constants.BRAND_NAME_CODE_LIST.PB'),
                config('constants.BRAND_NAME_CODE_LIST.PM'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
                config('constants.BRAND_NAME_CODE_LIST.VP'),
                config('constants.BRAND_NAME_CODE_LIST.FL'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowCustomFooterInWelcomeMail(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowAuthCode() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function disableRefundForImportedTransactionFrontend()
        {
            $brand_code = config('brand.name_code');

            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isNotAllowCaptcha(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowExportForMerchantWithdrawalAndDeposit(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowedDistanceSaleContractAsFile(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isBrand_WhichDoesntWantCopyPasteForPassword()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowCustomMailHeaderLogoCustomization(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }
	}