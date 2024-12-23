<?php
	
	namespace common\integration\Brand\Configuration\Backend;
	
	use common\integration\BrandConfiguration;
    use common\integration\Utility\Arr;
    use common\integration\Utility\Helper;

    class BackendWallet
	{
		public static function allowPhoneNumberChangeOtpSendToNewPhoneNumber(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PP'),
				config('constants.BRAND_NAME_CODE_LIST.FL'),
				config('constants.BRAND_NAME_CODE_LIST.SR'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
        public static function isDepositCreateMailOffForEftAutomation(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PL'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function showSendMoneyNetAmountOnEmailAndSms(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PL'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function setSenderReceiverRequestMoney() {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.YP'),
	            config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        
        public static function isShowCurrencySymbolCode() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PP'),
                config('constants.BRAND_NAME_CODE_LIST.SR'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isDeactivatedCardList() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PP'),
                config('constants.BRAND_NAME_CODE_LIST.SR'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowAddNationalityFromKPSAPIInLog(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PP'),
                config('constants.BRAND_NAME_CODE_LIST.SR'),
            ];
            $status =  Arr::isAMemberOf($brand_code, $brand_list);
            if($brand_code == config('constants.BRAND_NAME_CODE_LIST.PP') && Helper::isProdServerEnvironment()){
                $status = false;
            }elseif($brand_code == config('constants.BRAND_NAME_CODE_LIST.SR') && Helper::isProdServerEnvironment()){
                $status = false;
            }
            return $status;
        }
	    
	    public static function allowAutomaticBalanceTopUp(): bool
	    {
		    $brand_code = config('brand.name_code');
		    $brand_list = [
			    config('constants.BRAND_NAME_CODE_LIST.PP'),
		    ];
		    
		    return Arr::isAMemberOf($brand_code, $brand_list);
	    }
		
		public static function isAllowSendEmailForAutomaticBalanceTopUp(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PP'),
			];
			
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

        public static function isAllowToChangeWalletPanelSecurityImage(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP'),
            ];
            return (BrandConfiguration::allowSecurityImage() && Arr::isAMemberOf($brand_code, $brand_list));
        }

        public static function wrongUserOrPasswordMessageWalletPanel(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FL'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isOverwriteInvoiceDescriptionReceipt(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowNameSurnameInIndividualLoginAPIResponse(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowVerifiedParameterModification(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

	}