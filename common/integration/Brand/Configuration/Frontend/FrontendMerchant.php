<?php
	
	namespace common\integration\Brand\Configuration\Frontend;
	
	use common\integration\Utility\Arr;
    use common\integration\Utility\Helper;

    class FrontendMerchant
	{
        public static function showProductDescriptionOnPaymentReceipt() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowVpColorChangeDesign(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowRedirectLoginForSessionExpired()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PB'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
		
		public static function disableProcessImageForMoneyTransfer() :bool
		{
			
			$brand_code = config('brand.name_code');
			$brand_list = [
				//config('constants.BRAND_NAME_CODE_LIST.PM'),
			];
			return !Arr::isAMemberOf($brand_code, $brand_list);
		
		}

		public static function isCustomGeneratedTransactionReceipt() :bool
		{

			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.HP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);

		}
		
		public static function allowDailyTransactionCountOnDashboard() :bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PB'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
		
		public static function allowAmountWithdrawnOnDashboard() :bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PB'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
	    
	    public static function allowCommissionDeductionDashboard() :bool
	    {
		    $brand_code = config('brand.name_code');
		    $brand_list = [
			    config('constants.BRAND_NAME_CODE_LIST.PB'),
		    ];
		    return Arr::isAMemberOf($brand_code, $brand_list);
	    }
	    
	    public static function allowSettlementPaymentOnDashboard() :bool
	    {
		    $brand_code = config('brand.name_code');
		    $brand_list = [
			    config('constants.BRAND_NAME_CODE_LIST.PB'),
		    ];
		    return Arr::isAMemberOf($brand_code, $brand_list);
	    }
		
		public static function allowSingleInstallmentCommissionChecking(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PB'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
		
		public static function disableProfilePictureChangeToOtherUser(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
        public static function showCustomerBillingPhoneFromSale() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        // temporary method to hide new account and finalization report button in merchant production
        public static function shouldShowNewAccountAndFinalizationReportBtn(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list) && !Helper::isProdServerEnvironment();
        }

        public static function shouldDisableAddNewBankAccount(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list) && !Helper::isProdServerEnvironment();
        }

        public static function shouldShowAgreementPage(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }



    }