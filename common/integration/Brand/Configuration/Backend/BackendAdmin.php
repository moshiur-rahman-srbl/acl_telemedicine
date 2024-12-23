<?php
	
	namespace common\integration\Brand\Configuration\Backend;
	
	use App\Models\Statistics;
    use common\integration\BrandConfiguration;
    use common\integration\PackageService;
    use common\integration\Utility\Arr;
	use common\integration\Utility\Helper;
	
	class BackendAdmin
	{
		public static function allowCommissionColumnInUserDepositReport(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PP'),
				config('constants.BRAND_NAME_CODE_LIST.SR'),
				config('constants.BRAND_NAME_CODE_LIST.FL'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
		
		// ADMIN > MANAGEMENT > CUSTOMERS => dont show the blocked customers
		public static function notShowingTheBlockedCategoryCustomers(){
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.YP'),
				config('constants.BRAND_NAME_CODE_LIST.PC'),
				config('constants.BRAND_NAME_CODE_LIST.PP'),
				config('constants.BRAND_NAME_CODE_LIST.FL'),
				config('constants.BRAND_NAME_CODE_LIST.SR'),
				config('constants.BRAND_NAME_CODE_LIST.HP'),
				config('constants.BRAND_NAME_CODE_LIST.MOP'),
				config('constants.BRAND_NAME_CODE_LIST.QP'),
				config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
				config('constants.BRAND_NAME_CODE_LIST.DP'),
				config('constants.BRAND_NAME_CODE_LIST.PB'),
				config('constants.BRAND_NAME_CODE_LIST.SD'),
				config('constants.BRAND_NAME_CODE_LIST.PM'),
				config('constants.BRAND_NAME_CODE_LIST.PL'),
			];
			
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
		
		public static function enableProfileSettingsWallet(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PP'),
				config('constants.BRAND_NAME_CODE_LIST.FL'),
				config('constants.BRAND_NAME_CODE_LIST.SR'),
			];
			
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
		
		public static function enableIsOtpLoginForMerchantUser(){
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.MOP'),
				config('constants.BRAND_NAME_CODE_LIST.QP'),
				config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
				config('constants.BRAND_NAME_CODE_LIST.PM'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
        public static function isAllowedPaymentSourceInTransactionReport(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
		
		public static function showInstallmentInCompletedRefund(): bool 
		{
			$brand_code = config('brand.name_code');
            $brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PB'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
		}
		
		public static function merchantInformationPageDateFormat(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PP'),
				config('constants.BRAND_NAME_CODE_LIST.SR'),
				config('constants.BRAND_NAME_CODE_LIST.FL'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
		
		public static function posCommissionUpdateInformationEmail($emails): array | string
		{
			$brand_code = config('brand.name_code');
			$support_mail_addition_status = true;
			
			if(!Arr::isOfType($emails)){
				$emails = [
					$emails,
				];
			}
            if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.FP')) {
                $statistics = new Statistics();
                $emails = $statistics->findDataByColumn('merchant_information_change_emails');
                if (!empty($emails)) {
                    $emails = Arr::explode(',', $emails);
                } else {
                    $emails = [];
                }
                $support_mail_addition_status = false;
            }

//			if($brand_code == config('constants.BRAND_NAME_CODE_LIST.FP') && Helper::isProdServerEnvironment()){
//				$emails = [
//					'umran.Caglayan@denizbank.com',
//					'Levent.Alpteker@denizbank.com',
//					'gizem.kaya@denizbank.com',
//					'serkan.turan@denizbank.com'
//				];
//				$support_mail_addition_status = false;
//			}
			
			return [
				$emails,
				$support_mail_addition_status
			];
		}

        public static function allowManualCronProcess(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.HP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
		
		public static function merchantApplicationInputValidation(): bool {
			$brand_code = config('brand.name_code');
            $brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.HP'),
				config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
		}

        public static function allowCustomizedCostReturnMoneyOneTime(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.YP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
		
		public static function allowDisableWalletShowing(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PP'),
                config('constants.BRAND_NAME_CODE_LIST.SR'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

        public static function allowInactiveWalletShowing(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

		
		public static function hideAuthCodeOnReportView(): bool
		{
			
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PM'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
		
		public static function enableExistingProcessOnMerchantAnalyticsPage(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PB'),
				config('constants.BRAND_NAME_CODE_LIST.FP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

        public static function showMerchantIdInAmlReport(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PB'),
                config('constants.BRAND_NAME_CODE_LIST.PC'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function disableSuspectedUserAlertEmail(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PP'),
                config('constants.BRAND_NAME_CODE_LIST.SR'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
		
		public static function disableMerchantAnalyticsOldFlow(): bool
		{
			
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.VP'),
				config('constants.BRAND_NAME_CODE_LIST.PB'),
				config('constants.BRAND_NAME_CODE_LIST.SP'),
				config('constants.BRAND_NAME_CODE_LIST.PIN'),
			];
			
			$status = Arr::isAMemberOf($brand_code, $brand_list);
			
			if($status && config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.SP')
//				&& (Helper::isProvServerEnvironment() || Helper::isDevServerEnvironment() || Helper::isSpNginxServerEnvironment())
            ){
				
				$status = false;
				
			}
			
			return $status;
		}
		//installment wise payment related
		public static function isAllowSameDayMultipleInstalmentSettlement(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PB'),
				config('constants.BRAND_NAME_CODE_LIST.SP'),
				config('constants.BRAND_NAME_CODE_LIST.FP'),
				config('constants.BRAND_NAME_CODE_LIST.PIN'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

		public static function allowAllMerchantMaxTransactionMonthlyLimitAmountDownload(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.FP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

		public static function allowAllMerchantMaxTransactionDailyLimitAmountDownload(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.FP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}


		public static function allowAllMerchantMaxTransactionLimitAmountDownload(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.FP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

		public static function disabledEmailForBannedWallet(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PP'),
                config('constants.BRAND_NAME_CODE_LIST.SR'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}


		public static function allowDisableWalletShowingOnDailyReport(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PP'),
                config('constants.BRAND_NAME_CODE_LIST.SR'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

		public static function allowAccessRoleTurkishVersion(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PM'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

		public static function allowTcknVknBulkImportExport(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.FP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

        public static function showCostOfTransaction(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP'),
                ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
		
		public static function allowExcelFileUploadForAddMerchantTerminal(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.SP'),
				config('constants.BRAND_NAME_CODE_LIST.PIN'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

		public static function disableMerchantApplicationMerchantLogo(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.FP'),
			];
			return !Arr::isAMemberOf($brand_code, $brand_list);
		}
		
		
		public static function allowReasonOnBTOC(): bool
		{
            //as per requirement of DEN-482, now it is required for all brands
            return true;

			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.FP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
		public static function allowReasonOnBTOB(): bool
		{
            //as per requirement of DEN-482, now it is required for all brands
            return true;

			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.FP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

      public static function isAllowFilterFromBinResponses(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
	          config('constants.BRAND_NAME_CODE_LIST.FP'),
	          config('constants.BRAND_NAME_CODE_LIST.VP'),
	          config('constants.BRAND_NAME_CODE_LIST.PIN'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }
		
		public static function allowAllCurrencySettings(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PM'),
				config('constants.BRAND_NAME_CODE_LIST.FL'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

        public static function isAllowPosPfIntegration(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
                config('constants.BRAND_NAME_CODE_LIST.VP'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
                config('constants.BRAND_NAME_CODE_LIST.PM'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

		public static function merchantAnalyticsRemoveTestMerchant(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PB'),
				config('constants.BRAND_NAME_CODE_LIST.FP'),
				config('constants.BRAND_NAME_CODE_LIST.VP'),
				config('constants.BRAND_NAME_CODE_LIST.PC'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

		public static function merchantAnalyticsDisablePagination(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PB'),
				config('constants.BRAND_NAME_CODE_LIST.FP'),
				config('constants.BRAND_NAME_CODE_LIST.VP'),
				config('constants.BRAND_NAME_CODE_LIST.PC'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

		public static function allowDataTableOnMerchantAnalytics(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PB'),
				config('constants.BRAND_NAME_CODE_LIST.FP'),
				config('constants.BRAND_NAME_CODE_LIST.VP'),
				config('constants.BRAND_NAME_CODE_LIST.PC'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

		public static function disableTestMerchantOnRevenue(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PB'),
				config('constants.BRAND_NAME_CODE_LIST.FP'),
				config('constants.BRAND_NAME_CODE_LIST.VP'),
				config('constants.BRAND_NAME_CODE_LIST.PC'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

        public static function isAllowCommercialCardCommissionSetting(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                // config('constants.BRAND_NAME_CODE_LIST.PB'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowCashback(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                 config('constants.BRAND_NAME_CODE_LIST.PP'),
                 config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowPackageService(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        
        public static function hidePfRecordPhysicalPos(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function adminRemoteLogin(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowMerchantWiseSettlementReport()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.YP'),
            ];
            return in_array($brand_code, $brand_list);
        }

		public static function allowMerchantExportReportViaEmail(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.SP'),
				config('constants.BRAND_NAME_CODE_LIST.PIN'),
			];
			return in_array($brand_code, $brand_list);
		}


		public static function allowAllMerchantExportReportViaEmail(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.SP'),
				config('constants.BRAND_NAME_CODE_LIST.PIN'),
			];
			return in_array($brand_code, $brand_list);
		}
        public static function MerchantApplicationToCreatePosCommission()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return in_array($brand_code, $brand_list);
        }
		
		public static function isAllowMerchantAnalyticsExportReportViaEmail(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.SP'),
				config('constants.BRAND_NAME_CODE_LIST.PIN'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

        public static function isAllowMerchantInvoiceSummaryReport()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.YP'),
            ];
            return in_array($brand_code, $brand_list);
        }

        public static function allowBackupRestorationHistory()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return in_array($brand_code, $brand_list);
        }
        public static function allowExtraColumnForAccountStatementReport()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.YP'),
            ];
            return in_array($brand_code, $brand_list);
        }

        public static function isAllowPosCommissionSummaryReport()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.YP'),
            ];
            return in_array($brand_code, $brand_list);
        }

        public static function isAllowPosWiseSummaryReport()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.YP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isTerminalIdRequired()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isPosAccountRequired()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowedFreeRefund(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function shouldCallProcessAmcAfterReject(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function allowToUpdateLicenseTagByCityName(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function allowNonBankSettlementAmountInDailyBalanceReport(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.YP'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowAdminCustomRedirectDashboardUrl(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }        
        
        public static function isAllowToCreateNewMerchantBankAccountAndTerminal(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        // Package API and Package Related API Access
        public static function checkAPIPermission($api_name) : bool
        {
            $brand_code = config('brand.name_code');
            $api_list = [];

            if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.FP')) {
                $api_list = PackageService::getPackageAPIConstList();
            } else if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.PM')) {
                $api_list = [
                    PackageService::GET_PACKAGES_LIST,
                ];
            }

            if (Arr::isAMemberOf($api_name, $api_list)) {
                return true;
            }

            return false;
        }

        public static function showAllSaleSettlementValueInReport(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowedSettlementWalletLog(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowedDailyBalanceExtraRunTime()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code,$brand_list);
        }

        public static function allowPosTypeDefault(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowExtraPagesPermission(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowExtraAddDefaultMerchantSetting(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
                config('constants.BRAND_NAME_CODE_LIST.PM'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function allowMerchantBatchService(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowUserLoginAlertSettingsForBothStatus(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PC'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

    public static function isAllowMerchantTerminalSubMenu()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
    public static function isAllowUserLoginAlertSettings(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PC'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function isAllowPosTerminalBankCheck(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
	
		public static function allowNewTabOpeningLogoutOnAdminPanel()
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PM'),
				config('constants.BRAND_NAME_CODE_LIST.FL'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list) && !Helper::isLocalServerEnvironment();
		}

		public static function isValidatePhoneNoOnMerchantAdd()
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PM'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
        public static function shouldCallProcessMakerCheckerRecheckApprove(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }


		public static function isAllowAllTransaction()
		{
			$status = true;
			if(Helper::isNginxServerEnvironment() || Helper::isDevServerEnvironment()){
				$status = false;
			}
			return $status;
		}

		public static function isAllowPaymentTransaction()
		{
            return true;
//            return Helper::isSpNginxServerEnvironment() || Helper::isDevServerEnvironment()
//                || Helper::isProvServerEnvironment() || Helper::isSpProvServerEnvironment();
		}
        public static function isAllowReverseSettlementDaysValueShow(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowCityLicenseTag()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }


        public static function isAllowMerchantIksAutomation(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowedMakerCheckerMultiUpdate(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function hideAllTransaction(): bool
        {
            return false; // enable for dev(temporary)
//            return Helper::isSpNginxServerEnvironment() || Helper::isDevServerEnvironment();
        }

        public static function isAllowForwardSettlementDaysValueShow(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function allowTcknVknBulkImportExportAndPagination(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowDeletePasswordHistory(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowedPaxBatchService()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowBatchDetailsService()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }        
        
        public static function isAllowToChangeAdminPanelSecurityImage(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP'),
            ];
            return (BrandConfiguration::allowSecurityImage() && Arr::isAMemberOf($brand_code, $brand_list));
        }
        public static function isAllowCustomBulkCommission(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function MerchantUpdateInfoValidationRemoveForPhysicalPos()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowToChangeChargebackCancelState(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldShowRequestIdInMakerChecker(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isTurkishLanguageAllowed(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }


        public static function isAllowedEarlySettlement ()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowedPhysicalPOSPFDefinitionReports ()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowMerchantFastPosType(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowApiPackageService(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
                config('constants.BRAND_NAME_CODE_LIST.PM'),
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function allowCustomMerchantTerminalBrandCode()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function showMerchantStatusUpdatedByInformation():bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function removePackageListDefaultDateRange(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function readonlyApplicationAndActivationDate(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function showSupportTicketInformation(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function allowExtraFieldsOnAccountstatement(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
		
		public static function isAllowShowingIksRegistrationDate(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.SP'),
				config('constants.BRAND_NAME_CODE_LIST.PIN'),
			];
			return BrandConfiguration::isAllowedIKSMerchant() && Arr::isAMemberOf($brand_code, $brand_list);
		}
		
		public static function isDisableShowingIksDistrict(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.SP'),
				config('constants.BRAND_NAME_CODE_LIST.PIN'),
			];
			
			return !(BrandConfiguration::isAllowedIKSMerchant() && Arr::isAMemberOf($brand_code, $brand_list));
		}
		
		public static function isDisableShowingIksLicenseTag(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.SP'),
			];
			
			return !(BrandConfiguration::isAllowedIKSMerchant() && Arr::isAMemberOf($brand_code, $brand_list));
		}
		
		public static function isDisableShowingIksCountryCode(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.SP'),
			];
			
			return !(BrandConfiguration::isAllowedIKSMerchant() && Arr::isAMemberOf($brand_code, $brand_list));
		}
		
		
		public static function isAllowSearchFilterOnIksSettings(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.SP'),
			];
			return BrandConfiguration::isAllowedIKSMerchant() && Arr::isAMemberOf($brand_code, $brand_list);
		}

        public static function isAllowUpdateMerchantTerminalBySerialNo(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
		
		public static function enableOtpChannelForIndividualMerchant(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.SP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

        public static function isAllowTcknOrVknForIksTaxNo(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowApiPaxService(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldEnableAutoWithdrawalByMerchantAppApprove(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldAddIbanAndBankNameInAllMerchantReport(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldAddTerminalInfoInAllMerchantInfo(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function isAllowCardTypeAllTransactionReport(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldSearchLogsViaNewFlow(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);

        }

        public static function isDisableAutoCalculationSingleInstallmentForUI(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowPosAndBankAccountsInactiveForPassiveMerchant(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isTransactionTypeWiseVoidOrRefund(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
		
		public static function allowDirectlyLoginToAdminPanel()
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.SP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list) && (
					Helper::isProvServerEnvironment() ||
					Helper::isSpNginxServerEnvironment() ||
					Helper::isLocalServerEnvironment()
				);
			
		}
		
		public static function allowUserListToLoginAdminPanel($email)
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.SP'),
			];
			
			$sp_email_list = [
				"murat.kurucay@softrobotics.com.tr",
				"yakup.kurucay@softrobotics.com.tr",
				"yakupkurucay1996@gmail.com",
				"furkan.d@windowslive.com",
				"kurucaymurat77@gmail.com",
				"merchant6testteam@outlook.com",
				"merchant7testteam@outlook.com",
				"merchant8testteam@outlook.com",
				"muratkurucay58@outlook.com",
                "api.test1@softrobotics.com.tr",
                "api.test2@softrobotics.com.tr",
                "api.test3@softrobotics.com.tr",
                "api.test4@softrobotics.com.tr",
                "yakup.kurucay@sipay.com.tr",
                "furkan.dincer@sipay.com.tr",
                "murat.kurucay@softglobal.com"
			];
			
			return Arr::isAMemberOf($brand_code, $brand_list) && Arr::isAMemberOf($email, $sp_email_list);
		}

		public static function allowFirstMerchantStatusChangeToActive()
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PM'),
				config('constants.BRAND_NAME_CODE_LIST.FP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);

		}

        public static function shouldAddMerchantPosPFAsTerminalInfoInAllMerchantReport(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowProcessBackupSaleMonths(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function shouldCheckBankAccountAvailability() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldEnableMerchantBulkCommissionSettlementDay()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function hideAllTransactionDuplicateList()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldEnableAddingIntegratorToAnnouncementPanel()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function customMinValidationLengthOfUserPhone() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PB'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function shouldAllowToEditOrDeleteDepositMethodWithMakerChecker()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldAllowToEditOrDeleteTicketCategoryWithMakerChecker()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
    }