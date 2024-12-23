<?php
	
	namespace common\integration\Brand\Configuration\Backend;
	
	use common\integration\ApiService;
    use common\integration\BrandConfiguration;
    use common\integration\Utility\Arr;
    use common\integration\Utility\Helper;

    class BackendCcpayment
	{
		public static function isAllowRecalculationAmountAfterRefund(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.VP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

        public static function isAllowRecalculationMerchantSettlementAmount(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PL'),
                config('constants.BRAND_NAME_CODE_LIST.VP'),
                config('constants.BRAND_NAME_CODE_LIST.PB'),
                config('constants.BRAND_NAME_CODE_LIST.YP'),
                config('constants.BRAND_NAME_CODE_LIST.PM'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowTotalCommissionWithoutEndUserCommission(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP'),
                config('constants.BRAND_NAME_CODE_LIST.SP'),
                config('constants.BRAND_NAME_CODE_LIST.PB'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

      public static function validateBrandForFeatureAPI(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      
      public static function isAllowedForProviderErrorMapping(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.VP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isNestpayHashV3Applicable()
      {
          return true;
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
	          config('constants.BRAND_NAME_CODE_LIST.PIN'),
	          config('constants.BRAND_NAME_CODE_LIST.PIN'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function changeBankOtpPageNameWithPfSettingsPageName()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.HP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

        public static function allowCCPaymentApiResponseLocalization(): bool
        {
            $panel = config('constants.defines.PANEL');
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PB'),
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list) && ($panel == BrandConfiguration::PANEL_CCPAYMENT);
        }

        public static function increaseCardNumberAndCvvValidation()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.HP'),
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

      public static function allowCustomDataInBrandedPaymentPage()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
            config('constants.BRAND_NAME_CODE_LIST.VP')
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      
      public static function isAllowBrandedCcBlockMessage(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.VP')
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowConvertFailedTransactionToSuccess()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP')
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      
      public static  function allowCCNoCheckStatusApi() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
              config('constants.BRAND_NAME_CODE_LIST.VP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static  function allowBankTransStatCheckForNestpay() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.HP')
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function disableApiThrottleLimit() : bool
      {
          return true;
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.HP')
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

        public static function throttleLimit(): int
        {
            $limitPerMin = 600;
            $brand_code = config('brand.name_code');
            if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.HP')) {
                $limitPerMin = 60000;
            }else if ($brand_code == (config('constants.BRAND_NAME_CODE_LIST.SP') || config('constants.BRAND_NAME_CODE_LIST.PIN'))) {
                $limitPerMin = 6000;
            }
            return $limitPerMin;
        }
	  
	  public static function allowProductPriceOnPurchaseNotification(): bool
	  {
		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.PB')
		  ];
		  return Arr::isAMemberOf($brand_code, $brand_list);
	  }
		public static function allowCustomEmailDataForSale(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PB')
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

        public static function allowAllTransactionApiResponseManipulation()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PB')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowOnlyEnglishAndTurkishLettersInName()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowLocalizationForApiError()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isBrandAllowedCommercialCardAndCardProgranInGetPosResponse()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowApiUrlManipulationForHugin(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
                config('constants.BRAND_NAME_CODE_LIST.SP'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowBinAssingnment()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowBinMapping()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldManipulateStatusCodeForPhysicalPos($status_code)
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];

            if(Arr::isAMemberOf($brand_code, $brand_list)) {
                if($status_code == ApiService::API_SERVICE_SUCCESS_CODE){
                    $status_code = ApiService::API_SERVICE_HTTP_CODE_VALID_REQUEST;
                }else{
                    $status_code = ApiService::API_SERVICE_HTTP_CODE_BAD_REQUEST;
                }

            }
            return $status_code;
        }

        public static function showSaleReportExtrasDataInCheckStatus()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.HP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowCustomizedPFRecordsForKuvyet()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.VP'),
                config('constants.BRAND_NAME_CODE_LIST.SP'),
                config('constants.BRAND_NAME_CODE_LIST.PC'),
                config('constants.BRAND_NAME_CODE_LIST.PB'),
                config('constants.BRAND_NAME_CODE_LIST.FP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function shouldManipulateHttpCodeForPhysicalPos()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowedToSendOldResponseForGetTransectionApi(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
//                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list) && Helper::isProdServerEnvironment();
        }

        public static function btransInitialBatchCounter()
        {
            $initialCounter = 0;
            if(config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.FP')){
                $initialCounter = 2000;
            }
            return $initialCounter;
        }

        public static function isAllowedSalePropertyStatusUpdateForSaleFraud() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP')
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowInstallmentWiseSettlementsInCheckStatusApi(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
                config('constants.BRAND_NAME_CODE_LIST.VP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowRefundInfoInCheckStatusApi(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static  function showCCNoInCheckStatusApiWithoutRequestParam() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isOnePageFailedRedirectAsOneAndMultiTime() {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static  function isDenizPttHashV2Applicable() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static  function shouldAddMerchantTerminalStatusFilter() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static  function showHuginImportTransactionRequestLog() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function allowPhysicalPosTerminalIdInBtrans() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function redirectFailedPaymentToDplReturnUrlForNonStaticDpls() {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isWalletBalanceCheckDisabledForPhysicalPos() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowPaxMerchantTerminalParametersApi() : bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowSetBillEmailNPhone()
        {

            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
                config('constants.BRAND_NAME_CODE_LIST.PB'),
                config('constants.BRAND_NAME_CODE_LIST.PC'),
                config('constants.BRAND_NAME_CODE_LIST.VP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];

            return Arr::isAMemberOf($brand_code, $brand_list);

        }

	    public static function totalDeleteValueForTmpObjectStorage()
	    {
			$value = 10000;
		    $brand_code = config('brand.name_code');
		    $brand_list = [
			    config('constants.BRAND_NAME_CODE_LIST.PB')
		    ];

			if(Arr::isAMemberOf($brand_code, $brand_list)){
				$value = 2000;
			}

			return $value;
	    }

        public static function isAllowedBrand_ToIncludeResponseParam_RemainingAmount(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.FP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowedBrand_ToDecreaseOxivoFetchingTime(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function isAllowedBlockedPosInstallmentWiseBankSettlementDates(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
	            config('constants.BRAND_NAME_CODE_LIST.PIN'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }
        public static function shouldDisplayTerminalSuccessMessage(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PM')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function showCardTypeInTransactionStatus(): bool {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP')
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function disableMerchantSettlementDateForForeignCard()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

        public static function disableSendingTransactionMail(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            $envs = Helper::isProvServerEnvironment();
            return Arr::isAMemberOf($brand_code, $brand_list) && $envs;
        }

	}