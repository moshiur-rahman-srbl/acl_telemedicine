<?php

	namespace common\integration\Brand\Configuration\All;

	use App\Models\BlockCC;
    use App\Models\Merchant;
    use common\integration\BrandConfiguration;
    use common\integration\Models\User;
    use common\integration\Utility\Arr;
  use common\integration\Utility\Helper;

  class Mix
	{
        /**
         * Used to control app versions of different operating systems
         */
        public static function allowAppVersionControl(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.PP'),
                config('constants.BRAND_NAME_CODE_LIST.FL'),
                config('constants.BRAND_NAME_CODE_LIST.SR'),
            ];
            return Arr::isAMemberOf($brand_code, $brand_list);
        }

      public static function allowBillPaymentNewDesign(): bool
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
               config('constants.BRAND_NAME_CODE_LIST.PL'),
            ];

            $status = Arr::isAMemberOf($brand_code, $brand_list);

            if(Helper::isProvServerEnvironment() || Helper::isProdServerEnvironment()){
                $status = false;
            }

            return $status;

        }

      public static function isAllowRmcEmailService() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list) && ( !Helper::isLocalServerEnvironment() && !Helper::isDevServerEnvironment());
      }

	  public static function enableTurkishLatterCustomizedForSmsSending(): bool
	  {
		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.PL'),
			  config('constants.BRAND_NAME_CODE_LIST.PP'),
			  config('constants.BRAND_NAME_CODE_LIST.FL'),
			  config('constants.BRAND_NAME_CODE_LIST.SR'),
			  config('constants.BRAND_NAME_CODE_LIST.PC'),
			  config('constants.BRAND_NAME_CODE_LIST.VP'),
			  config('constants.BRAND_NAME_CODE_LIST.PM'),
		  ];
		  return Arr::isAMemberOf($brand_code, $brand_list);
	  }

      public static function isAllowIpBlock(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.VP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

	  public static function failLoginMessageUserPanel($mins = null, $attempts = null){
		  $brand_code = config('brand.name_code');
		  $allow_list = [
			  config('constants.BRAND_NAME_CODE_LIST.SP'),
			  config('constants.BRAND_NAME_CODE_LIST.IM'),
			  config('constants.BRAND_NAME_CODE_LIST.SR'),
			  config('constants.BRAND_NAME_CODE_LIST.FL'),
			  config('constants.BRAND_NAME_CODE_LIST.PM'),
		  ];
		  $message = '';
		  if(in_array($brand_code, $allow_list)){
			  $message =  __("Too many login attempts. Please try again in :var1 minute(s)",['var1' => $mins]);
		  }elseif(config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.YP')){
			  $message = __('auth.throttle',['minute' => $mins]);
		  }elseif(config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.SD')){
			  $message = __('auth.throttle', ['minute' => $mins]);
		  }elseif(config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.DP')){
			  $message = __('auth.throttle', ['minute' => $mins]);
		  }elseif(config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.PP')) {
			  $message = __('Your account has been locked for :minute minutes due to :attempt wrong passwords.', ['minute' => $mins, 'attempt' => $attempts]);
		  }
		  else{
			  $message = __("Too many login attempts, please contact our customer services.");
		  }
		  return $message;
	  }

      public static function isSimplePaginateDeactivated(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
//              config('constants.BRAND_NAME_CODE_LIST.SP'),
              config('constants.BRAND_NAME_CODE_LIST.PB'),
          ];
          return !Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isMerchantStatusCheckByIks(){
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PL'),
              config('constants.BRAND_NAME_CODE_LIST.PP'),
              config('constants.BRAND_NAME_CODE_LIST.FL'),
              config('constants.BRAND_NAME_CODE_LIST.SR'),
              config('constants.BRAND_NAME_CODE_LIST.PB'),
              config('constants.BRAND_NAME_CODE_LIST.PC'),
              config('constants.BRAND_NAME_CODE_LIST.FP'),
              config('constants.BRAND_NAME_CODE_LIST.VP'),
              config('constants.BRAND_NAME_CODE_LIST.SP'),
	          config('constants.BRAND_NAME_CODE_LIST.PM'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowFilterByTransactionPosType(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
              config('constants.BRAND_NAME_CODE_LIST.PM'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }



      public static function isAllowedTransactionReversal(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP')
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowPointPayment(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.HP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowDBReportingPortChange(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isShowNegativeSignOutAmountOnAccountStatement(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
              config('constants.BRAND_NAME_CODE_LIST.YP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

	  public static function enableNewTabOpenLogOut(){
		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.YP'),
			  config('constants.BRAND_NAME_CODE_LIST.PM'),
			  config('constants.BRAND_NAME_CODE_LIST.FL'),
		  ];
		  return Arr::isAMemberOf($brand_code, $brand_list) && !Helper::isLocalServerEnvironment();
	  }

      public static function hideDateFilterForIntegratorList(){

          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PB'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowFeaturePosContent() {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.YP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowFeatureApiPosNotify() {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.YP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }


      public static function customBlockCardBinDigit(){
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PB'),
          ];
          $custom_bin_digit = BlockCC::BIN_DIGIT;
          if(Arr::isAMemberOf($brand_code, $brand_list)){
              $custom_bin_digit = BlockCC::CUSTOM_BIN_DIGIT;
          }
          return $custom_bin_digit;
      }

	  public static function allowSecurityImageOnCreateUserAdminMerchant(): bool
	  {

		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.PM'),
		  ];

		  return BrandConfiguration::secretQuestionResetPassword() && Arr::isAMemberOf($brand_code, $brand_list);
	  }

      public static function isAllowBrandImport()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
          ];
          return in_array($brand_code, $brand_list);
      }

	  public static function disableResendOtpBtnForMultipleOtpSend(): bool
	  {

		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.PP'),
		  ];
		  return in_array($brand_code, $brand_list);
	  }

      public static function isAllowRandomIpAddress (): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function showLogoOnPaymentPage(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.VP'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

	  public static function allowPreDefinedMerchantLanguageForDpl(): bool
	  {
		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.VP'),
              config('constants.BRAND_NAME_CODE_LIST.PB'),
		  ];

		  return Arr::isAMemberOf($brand_code, $brand_list);
	  }

      public static function allowToSendEmailForBlackListUserSanctionScannerVerification(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PP'),
              config('constants.BRAND_NAME_CODE_LIST.SR'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowedBrandFor_NoAwaitingRefund(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
              config('constants.BRAND_NAME_CODE_LIST.PB'),
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowedParatekService (): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.VP')
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isEnabledReconcileReport(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.HP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isAllowAdminApi(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
              config('constants.BRAND_NAME_CODE_LIST.PM'),
             /* config('constants.BRAND_NAME_CODE_LIST.SP'),*/
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowedTurnoverMonitoring(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PB'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isAllowAmexCardCommission() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.VP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowAlertEmailForBankErrorCodes(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PB'),
              config('constants.BRAND_NAME_CODE_LIST.VP'),
              config('constants.BRAND_NAME_CODE_LIST.SP'),
              config('constants.BRAND_NAME_CODE_LIST.PC'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function allowDuplicateEmailAndPhoneForMerchantCreate(){
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowShowPaymentReasonCodeDetail() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PB'),
              config('constants.BRAND_NAME_CODE_LIST.VP'),
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowOptionalStaticContent()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PP'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowAgreementReadAndApproveOnMerchantPanel() :bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.VP'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function fileChooseExportOptionForVPosMonitoring() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.YP'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowForeignCreditCardSettlement(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowTerminalOnMerchantPanel(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowFilterTransactionsByOriginalBankErrorCodeDescription()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.YP'),
          ];
          return in_array($brand_code, $brand_list);
      }

	  public static function enableJetSmsNewFlow()
	  {
		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.PP'),
		  ];
		  return in_array($brand_code, $brand_list);
	  }
      public static function isAllowedMultiplePosPrograms()
	  {
		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.PB'),
              config('constants.BRAND_NAME_CODE_LIST.PC'),
              config('constants.BRAND_NAME_CODE_LIST.VP'),
              config('constants.BRAND_NAME_CODE_LIST.FP'),
		  ];
		  return Arr::isAMemberOf($brand_code, $brand_list);
	  }
      public static function isAllowFreeTextForMerchantApplicationReject(){
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowSpecificMakerChecker(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM')
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowDailyFinancializationReport()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return in_array($brand_code, $brand_list);
      }
      public static function isAllowedCvvPass()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return in_array($brand_code, $brand_list);
      }

	  public static function allowUrlParamsEncryption()
	  {
		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.PP'),
			  config('constants.BRAND_NAME_CODE_LIST.FP'),
		  ];
		  return in_array($brand_code, $brand_list);
	  }

      public static function allowCustomDplBackgroundColor()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.VP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowCustomDigitDecimalLength()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.YP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isDpl3dCheckboxAllowed ($dpl_pos_option, $merchant_setting_option, $payment_type): bool
      {
          return $dpl_pos_option == $payment_type;

          /*
           * SMP-2406 - we need to disable this feature for sipay
           */
          $brand_code = config('brand.name_code');
          $is_allowed = false;

          if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.SP')) {
              $is_allowed = $merchant_setting_option == $payment_type;
          } else {
              $is_allowed = $dpl_pos_option == $payment_type;
          }

          return $is_allowed;
      }

      public static function allowDynamicFieldInReceipt(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PB'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowInputValidationInDPL(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PB'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowLimitCommercialCardCommission()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PB'),
              config('constants.BRAND_NAME_CODE_LIST.PC'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isDcCardProgramDisabled($brand_code, $brand_list)
      {
          $allowed_brand_list = [
              $brand_list['PB']
          ];
          return Arr::isAMemberOf($brand_code, $allowed_brand_list);
      }

	  public static function allowContentSecurityPolicyHeader()
	  {
		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.PP'),
		  ];
		  return Arr::isAMemberOf($brand_code, $brand_list);
	  }

	  public static function allowPermissionsPolicyHeader()
	  {
		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.PP'),
		  ];
		  return Arr::isAMemberOf($brand_code, $brand_list);
	  }

      public static function isAllowedInstallmentTransactionReport() {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PB'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowedPax()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return in_array($brand_code, $brand_list);
      }

      public static function isEnabledApplicationOnboardingState ()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
              config('constants.BRAND_NAME_CODE_LIST.QP'),
              config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
          ];
          return in_array($brand_code, $brand_list);
      }

      public static function isAllowedOnboardingPanel() {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.QP'),
              config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
          ];
          if($brand_code == config('constants.BRAND_NAME_CODE_LIST.SP')
              && (Helper::isDevServerEnvironment()
                  || Helper::isSpNginxServerEnvironment()
                  || Helper::isLocalServerEnvironment()
              )
          ){
              $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.SP');
          }
          return in_array($brand_code, $brand_list);
      }

      public static function isAllowedSelfOnboardingPanel() {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.QP'),
              config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
          ];
          if($brand_code == config('constants.BRAND_NAME_CODE_LIST.SP')
              && (Helper::isDevServerEnvironment()
                  || Helper::isNginxServerEnvironment()
                  || Helper::isLocalServerEnvironment()
              )
          ){
              $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.SP');
          }
          return in_array($brand_code, $brand_list);
      }

      public static function isAllowedMerchantAppDeleteFromAdmin() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [];

          if (Helper::isProvServerEnvironment() || Helper::isLocalServerEnvironment()) {
              $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.QP');
              $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.QP_TENANT');
          }

          if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.SP')
              && (Helper::isDevServerEnvironment()
                  || Helper::isNginxServerEnvironment()
                  || Helper::isLocalServerEnvironment()
              )
          ) {
              $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.SP');
          }

          return in_array($brand_code, $brand_list);
      }


      public static function isAllowedPaygo()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return in_array($brand_code, $brand_list);
      }

      public static function enableUserPasswordRequired()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return in_array($brand_code, $brand_list);
      }

      public static function allowToManipulateCookieAttribute($brand_code, $brand_list) : bool
      {
          $allowed_brand_list = [
              $brand_list['PP']
          ];
          return Arr::isAMemberOf($brand_code, $allowed_brand_list);
      }



      public static function isAllowMerchantInformationChangeEmailNotification()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowSecurityImageForAdminOtpPage()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.VP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function allowGroupMerchantWhileCreateNew()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowCustomJsonItemData()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function enableDateRangeForSettlementCalendar():bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
               config('constants.BRAND_NAME_CODE_LIST.SP')
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowReportHistoriesSettlementCalendar():bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP')
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowedMultipleDocumentsInMerchantApplication()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM')
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowedDailyTransactionHistories()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP')
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowBillingInfoSearch()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP')
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function shouldGetBankApprovedMappedMerchantId()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP')
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowHideBankAccountInfo(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowMerchantBkmIdForKuvyet(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.VP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isSwiftCodeOptional(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowIntegratorsInPosCommission(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowResendFirstTransactionMail(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isAllowTmpMerchantBankAccountAndTerminalsAttemptFieldSetZero(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isOptionalMinAndMaxInstallmentForLimitCommercialCardCommission()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PC'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowSettlementDateAndCardIssuerBankInAccountStatement(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowFilterDocumentAndAssessmentStatus()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isAllowedMerchantApiV2 ()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];

//          if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.SP')
//              && Helper::isLocalServerEnvironment()
//          ){
//              $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.SP');
//          }

          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isAllowSerialNumberInMerchantSearch()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function showBankNameInMerchantTerminal()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowAddNewCheckBoxForMerchantExtras()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowInputTypeInDPLDynamicField(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.VP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function allowDOBOnMerchantApplication(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function alertHidePayBill(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FL'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isBrandWantDeviceWiseTerminalEntry(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM'),
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isBrandDisabledForVirtualPosTypeInTerminal(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowTerminalParameterSettings(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM'),
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];


          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function showMerchantPanelPackageName(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isAllowVoidStatus(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowCloseReasonDescription(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

	  public static function isAllowIntegratorNameShowingOnMerchantAnalyticsReport()
	  {
		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.SP'),
		  ];
		  return Arr::isAMemberOf($brand_code, $brand_list);
	  }

      public static function isShowMerchantIdOnReceipt(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function shouldCheckIksVerificationDateInBtrans(): bool
      {
          $is_brand_allowed_for_iks = BrandConfiguration::isAllowedIKSMerchant();

          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP')
          ];

          return $is_brand_allowed_for_iks && Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isShowServerPort(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FL'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowDefaultForeignCreditBank(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM'),
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowNewMerchantDefaultActive(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function shouldApplyKycVerificationStaticInfo() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list) && !Helper::isProdServerEnvironment();
      }

      public static function isAllowShowFraudInfoInTransactions(): bool
      {
            // ref. BrandConfiguration::isAllowShowFraudRuleTransactionInMerchantTransactions()
          $brand_code = config('brand.name_code');
          $brand_list = [
//              config('constants.BRAND_NAME_CODE_LIST.PM'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isAllowExtraFieldsMerchantSettlementDaysAndPosType(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowNewFlowFinalizationReport(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowAddIndividualAuthorizedPersonSurname(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
//              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

	  public static function isAllowedEarlyAutomaticWithdrawal() {
		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.SP'),
		  ];

		  return Arr::isAMemberOf($brand_code, $brand_list);
	  }

	  public static function sendBlockEmail(): bool
	  {
		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.PM'),
		  ];

		  return Arr::isAMemberOf($brand_code, $brand_list);
	  }

      public static function isAllowWithdrawToWalletGate(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isAllowImportedTransactionCronjobInWorker(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowedMainAndSubDealerIntegration() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

	  public static function disableCodeViewOnSummerNoteXSS()
	  {
		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.FP'),
		  ];
		  return !Arr::isAMemberOf($brand_code, $brand_list);
	  }

      public static function enableBinMappingByCardType() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM'),
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isShowCurrency() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowedMerchantApplicationExtraCols() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.QP'),
              config('constants.BRAND_NAME_CODE_LIST.QP_TENANT'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function shouldAllowWaitingPhysicalTransactions() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM'),
              config('constants.BRAND_NAME_CODE_LIST.SP'),
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowMerchantBinRedirectionReport(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isAllowRemoteReferenceForImportedTransaction() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isDisabledGoogleCaptcha()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [];
          if($brand_code == config('constants.BRAND_NAME_CODE_LIST.FP') && Helper::isProvServerEnvironment()){
              $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.FP');
          }
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function canManipulateFinflowBtocDescription (): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowMerchantAgreementTermsConditionsAndPrivacyPolicy() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PC'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isAllowExtraValidationInMerchantTerminalSettings() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowVerifone(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = array(
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          );

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowToSendPackageAlertMail() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function allowMerchantPosCommissionBulkImport() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowPosCardAssociationRestriction()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function showCreditCardNoAndCardHolderName()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowShortForm() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function shouldAllowCardTypeOnPos(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function shouldEnableCardServiceIpRestriction(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM')
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function shouldEnableIysPushServiceIpRestriction(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM')
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowedFraudRuleStructureFeature() : bool {
          $brand_code = config('brand.name_code');
          $brand_list = [];

          if($brand_code == config('constants.BRAND_NAME_CODE_LIST.SP') && Helper::isSpNginxServerEnvironment()) {
              $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.SP');
          }

          if ($brand_code == config('constants.BRAND_NAME_CODE_LIST.PM') && Helper::isDevServerEnvironment()) {
              $brand_list[] = config('constants.BRAND_NAME_CODE_LIST.PM');
          }

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function showPaymentSourceAsNonSecureFor2D()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function shouldGetUpdatedInstallmentInPosEdit(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }


      public static function isAllowedFilterByRemoteTransactionAt() : bool {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function shouldAllowPartialChargeback() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function shouldAllowPosType(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowToShowPackageInformation() : bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function showSpecificFieldsAsAdminMakerCheckerPermission(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];

          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowedCashOutSettingRecipientType(): bool {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];

          if(Helper::isProdServerEnvironment() && $brand_code == config('constants.BRAND_NAME_CODE_LIST.FP')) {
             return false;
          }
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isAllowReceiverFilterInB2B(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isAllowPosProject(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isAllowDownloadAllMerchantPosWise(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PM'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowToShowBankTerminalId(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function shouldReplaceRubleCurrencyToLegacyCurrency($brand_code = null): bool
      {

          if (!empty($brand_code)) {
              $brand_list = [
                  'SP',
              ];
          } else {
              $brand_code = config('brand.name_code');
              $brand_list = [
                  config('constants.BRAND_NAME_CODE_LIST.SP'),
              ];
          }
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowUserAccountActivationHistory(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isAllowPackageNameAndRemainingTurnover(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.FP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function isAllowSubPaymentIntegrationOption(): bool
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.SP'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
  }
