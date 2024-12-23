<?php
	
	namespace common\integration\Brand\Configuration\Backend;
	
	use common\integration\BrandConfiguration;
    use common\integration\MerchantWinningService;
    use common\integration\Utility\Arr;
    use common\integration\Utility\Helper;

    class BackendMerchant
	{
        public static function isAllowedShowAllUsersRecord(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PB'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
		
		public static function showInvoiceIdOnallTransaction(){
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.VP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
	    
	    public static function showUserEmailOnallTransaction(){
		    $brand_code = config('brand.name_code');
		    $brand_list = [
			    config('constants.BRAND_NAME_CODE_LIST.VP'),
		    ];
		    return Arr::isAMemberOf($brand_code, $brand_list);
	    }

        //shows card programs on merchant panel according to the pos assigned to the merchant
        public static function showCardProgramsAccordingToPos(){
		    $brand_code = config('brand.name_code');
		    $brand_list = [
			    config('constants.BRAND_NAME_CODE_LIST.FP'),
		    ];
		    return Arr::isAMemberOf($brand_code, $brand_list);
	    }



        public static function showReceiverMailInAllTransaction()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function showMoreBalanceInfoInWalletApi()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function allowDplInputFieldsCustomLength()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }



        public static function isAllowedGetMerchantInformation(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function checkMerchantWinningAPIPermission($api_name) : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM')
            ];
            $allow_api = false;
            if(Arr::isAMemberOf($brand_code, $brand_list)){
                if (Arr::isAMemberOf($api_name, MerchantWinningService::getAPIConstList())) {
                    $allow_api =  true;
                }
            }

            return $allow_api;
        }

        public static function isAllowToChangeMerchantPanelSecurityImage(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP'),
            ];
            return (BrandConfiguration::allowSecurityImage() && Arr::isAMemberOf($brand_code, $brand_list));
        }

        public static function isAllowExportPaymentReport(): bool
        {
//            if(Helper::isDevServerEnvironment() || Helper::isSpNginxServerEnvironment() || Helper::isLocalServerEnvironment()){
//                return true;
//            }
            return false;
        }
        public static function allowReportMailSendToParentMerchantForSwitchedMerchant(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowedAccountStatementReportXlsAsDefaultFormat() {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowToShowAllCardProgramsMerchantCommissionTableList(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowPaymentReport(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            $allowed_env = Helper::isSpNginxServerEnvironment() || Helper::isDevServerEnvironment() || Helper::isLocalServerEnvironment();
            return Arr::isAMemberOf($brand_code, $brand_list) && $allowed_env;
        }
		public static function enableSwapMerchantPanelAndResendEmailLocation()
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PM'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
        public static function isAllowAccountStatementSettlementDate(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowDashboardSummaryFromDailySaleReport(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
	}