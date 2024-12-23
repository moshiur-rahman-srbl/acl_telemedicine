<?php
	
	namespace common\integration\Brand\Configuration\Backend;

	use common\integration\BrandConfiguration;
    use common\integration\Utility\Arr;
    use common\integration\Utility\Helper;

    class BackendMix
	{
        public static function isAllowCompanyNameConcatWithMerchantName(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                //config('constants.BRAND_NAME_CODE_LIST.FP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowedDepositForReceiveMoneyLimits (): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                // config('constants.BRAND_NAME_CODE_LIST.PL')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowedWithdrawForSendMoneyLimits (): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                // config('constants.BRAND_NAME_CODE_LIST.PL')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isCheckTransactionLimitsAsMonthly (): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                 config('constants.BRAND_NAME_CODE_LIST.PL'),
                 config('constants.BRAND_NAME_CODE_LIST.PP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
		
        public static function isAllowFixedOtpForFixedPhone (): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PP'),
                //config('constants.BRAND_NAME_CODE_LIST.FL'),
                config('constants.BRAND_NAME_CODE_LIST.SR'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowRemoteCustomerNumber (): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PL'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowedForEventBasedWalletUpdate()
        {
            return true;
            //return false;
            //return true;
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowCheckGraylogServerStatus (): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.YP'),
              config('constants.BRAND_NAME_CODE_LIST.PP'),
              config('constants.BRAND_NAME_CODE_LIST.SR'),
              config('constants.BRAND_NAME_CODE_LIST.FL'),
	            config('constants.BRAND_NAME_CODE_LIST.PM'),
	            config('constants.BRAND_NAME_CODE_LIST.VP'),
	            config('constants.BRAND_NAME_CODE_LIST.PC'),
	            config('constants.BRAND_NAME_CODE_LIST.PB'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldResetHostHeaderAsAppUrl(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PB'),
	            config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.VP'),
	            config('constants.BRAND_NAME_CODE_LIST.FP'),
	            config('constants.BRAND_NAME_CODE_LIST.PP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function hideKuveytBankCardProgram(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function hideCardProgramWhichHasNoLogo(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowMerchantGroup(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.FP'),
	            config('constants.BRAND_NAME_CODE_LIST.VP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowWalletLogExport(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        
        public static function showFinancializationReport(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowFullCompanyName(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowChargebackForApproveOrReject(): bool{
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowCustomerNumberInReceipt(): bool{
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function showCardProgramNameInsteadCode($is_api = false): bool
        {

			$status = true;
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PB'),
            ];
			
			if(Arr::isAMemberOf($brand_code, $brand_list) && $is_api){
				$status = false;
			}
			
			return $status;
			
        }

        public static function allowDeclineResponseCode(): bool{
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PB'),
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function maximumExecutionTimeForReporting(): int
        {
            $limit_in_sec = 1200;
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.HP'),
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];

            if(Arr::isAMemberOf($brand_code, $brand_list)){
                $limit_in_sec = 3600;
            }
            return $limit_in_sec;
        }

        public static function isShowRefundOriginalAmount(): bool{
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PB'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isCustomizeTrustedDeviceExpiry()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PP'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function customizeMerchantSettlementReport()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.YP'),
            ];
            return in_array($brand_code, $brand_list);
        }

        public static function allowMerchantClosingTime()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.YP'),
            ];
            return in_array($brand_code, $brand_list);
        }
        public static function allowRequestMoneyRateLimit(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PP'),
            ];
            return in_array($brand_code, $brand_list);
        }

        public static function isFirstNameAndSurnameRequiredForUser()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function determineBankSettlementDateDependOnBankClosingTime()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.YP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowResendWelcomeMail()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
	            config('constants.BRAND_NAME_CODE_LIST.PC'),
                config('constants.BRAND_NAME_CODE_LIST.PM')
            ];

            return in_array($brand_code, $brand_list);
        }

        public static function isAllowSupportTicketEmailForChatAndClose()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isMerchantApplicationPackageCustomizedWay (): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM')
            ];
            return in_array($brand_code, $brand_list);
        }
        public static function showCustomMerchantApplicationFields()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function hideCustomMerchantApplicationFields()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowMerchantNewStatus(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowMerchantApplicationDocuments()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowIframeMerchantApplication()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowModifyProviderErrorMapping(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function allowImportPavoTransactionByBkmSerialNo(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }        
        public static function allowToCreateMerchantTerminalOnlySelectedField(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
	    
	    public static function allowSentEmailNotification(){
		    $brand_code = config('brand.name_code');
		    $brand_list = [
			    config('constants.BRAND_NAME_CODE_LIST.HP')
		    ];
		    return Arr::isAMemberOf($brand_code, $brand_list);
	    }

        public static function isAllowedCheckGlobalMerchantStatus(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowBulkRefundRequest() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function changeTimeformatForBlockUser() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowCreateMerchantApplicationMCC(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowToSetBankInNewMerchantTerminal(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function hideYapiKrediBankCardProgram(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function hideTurkiyeFinanceBankCardProgram(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function hideIsBankCardProgram(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowDefaultOtpChannelAsSMS(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FL'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isallowToSendEmailForCreatingTerminal(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowImportedTransactionExtraField(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function shouldHideImportedTransactionHistory(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowDebitCardSettlementForBank(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];

            $status = Arr::isAMemberOf($brand_code, $brand_list);

            if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.PM') && Helper::isProdServerEnvironment()) {
                $status =  false;
            }

            return $status;
        }

        public static function isAllowDebitCardSettlementForMerchant(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];

            $status = Arr::isAMemberOf($brand_code, $brand_list);

            if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.PM') && Helper::isProdServerEnvironment()) {
                $status =  false;
            }

            return $status;
        }
        public static function isHideRefundCommission(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }


        public static function isAllowToCreateBulkTerminal(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowCashbackRuleTerminalAndNationalSwitchId(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isBrandForImmediateSettlementAndAutomaticWithdrawal(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowCustomCashback(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
//                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.PM') && !Helper::isProdServerEnvironment()){
                Arr::push($brand_list, config('constants.BRAND_NAME_CODE_LIST.PM'));
            }

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowPhysicalPosProviderErrorMapping(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
//                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.PM') && !Helper::isProdServerEnvironment()){
                Arr::push($brand_list, config('constants.BRAND_NAME_CODE_LIST.PM'));
            }
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isMerchantTerminalIdNumeric(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function disableRefundForImportedTransaction($panel_name = BrandConfiguration::PANEL_ALL): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = array();

            if($panel_name == BrandConfiguration::PANEL_MERCHANT) {
                $brand_list = [
                    config('constants.BRAND_NAME_CODE_LIST.SP'),
                    config('constants.BRAND_NAME_CODE_LIST.PM'),
                    config('constants.BRAND_NAME_CODE_LIST.FP'),
	                config('constants.BRAND_NAME_CODE_LIST.PIN'),
                ];
            } else if($panel_name == BrandConfiguration::PANEL_ADMIN) {
                $brand_list = [
                    config('constants.BRAND_NAME_CODE_LIST.PM'),
                    config('constants.BRAND_NAME_CODE_LIST.FP')
                ];
            }

            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowEditableMerchantOnboardingApiSection(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

		public static function enableAuditTrails()
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PM')
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

        public static function isRestrictedFinflowForManualWithdrawal(): bool {
            $brand_code = config('brand.name_code');
            $brand_list = [];
            //by adding any brand to this array, it will work for all environment

            if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.FP') && !Helper::isProdServerEnvironment()) {
                Arr::push($brand_list, config('constants.BRAND_NAME_CODE_LIST.FP'));
            }

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isMerchantPosCommissionInstallmentValueShowFromPackage(): bool {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowToSendEmailForChangeTerminalStatus(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowShowAccountManagerNameInMerchantanAlytics(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PC')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldAllowToShowPhysicalPosBankErrorCode() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldAllowTosetAuthAndRrnForPhysicalPosRefund() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isRestrictedFinflowForAmlApproval(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowedMerchantUserTransactionLimit(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowTurnoverPackage(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowPaymentReportInReporting(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
                config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
	}