<?php
	
	namespace common\integration\Brand\Configuration\Frontend;
	
	use common\integration\Utility\Arr;
    use common\integration\Utility\Helper;

    class FrontendAdmin
	{

        public static function showOnlyTrendyolScheduleReport(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function hideFrequencyOnCronSettings(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.HP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function skipManualBankRefundCreatedAtValidaionCheck(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.HP'),
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

         public static function showOnlyBicenReport(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
		
        public static function showDebitCardPaymentOption() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function showInvoiceDescriptionInTransactionDetails() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function showOnlyYemeksepetiReport(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowRemoveRequiredValidation(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
                config('constants.BRAND_NAME_CODE_LIST.PM'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowNeighborhoodNotRequired(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }


        public static function disableRequiredValidation(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function showExtraFieldsInAwaitingRefundMakerChecker()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.HP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }


        public static function allowVKNFieldForMerchantIndividualType(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowReuploadAgreement() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowFilledAutomaticallyZipCode(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldDisableSiteUrlForIndividualMerchant(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowValidationForTcknAndVkn(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowSingleInstallmentForMaxInstallment(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PC'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldHideBillingInformation(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function showStatusOnMerchantApplicationList(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }        
        
        public static function hideOtpChannel(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FL'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowCreditCardSettlement(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowResetTerminalSettings(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowDateOfBirthFieldInMerchantBillingInformation(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowMccCodeFromFirst(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isPermissionWisePosCot(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowToSelectFutureDate(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowTransactionChannel(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP'),
                config('constants.BRAND_NAME_CODE_LIST.PB'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function shouldAllowCompactFraudManagement(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [];

            if($brand_code == config('constants.BRAND_NAME_CODE_LIST.SP') && Helper::isSpNginxServerEnvironment()) {
                $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.SP');
            }

            if($brand_code == config('constants.BRAND_NAME_CODE_LIST.PM') && Helper::isDevServerEnvironment()) {
                $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.PM');
            }

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowResetTabFlagOnAdminLogin(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function showWaitingPhysicalPosTransactionMenu(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function showAwaitingPhysicalPosTransactionPage(): bool
        {    
            $brand_code = config('brand.name_code');    
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];   
             
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowForeignCardSelectionInPos(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowFraudRuleLocalization(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PC'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldHideWalletPanelSpecificMenuInAdminPanel (): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PB'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldShowMerchantPosPFSettingsMenu(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldEnableSettlementDaysOnPosBulkEdit()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function enableStaticBins()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldShowCreatedAtForB2BAndB2C(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

	}