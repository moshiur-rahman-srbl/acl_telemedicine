<?php
	
	namespace common\integration\Brand\Configuration\Frontend;

    use App\Models\Profile;
    use common\integration\ManipulateDate;
    use common\integration\Utility\Arr;
    use common\integration\Utility\Helper;

    class FrontendWallet
	{
      public static function wrongPasswordMessageUserPanel($attempt_left = null)
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
	          config('constants.BRAND_NAME_CODE_LIST.PM'),
	          config('constants.BRAND_NAME_CODE_LIST.FL'),
          ];

          $message = '';

          if(Arr::isAMemberOf($brand_code, $brand_list)){
              $message =  __("Your process cannot be continued, please log in again.");
          }elseif(config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.PL')) {
              $message = __('You entered the wrong password, :attempt attempts left', ['attempt' => $attempt_left]);
          }

          return $message;

      }

      public static function isNewReceiptForMobile(){
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PP'),
              config('constants.BRAND_NAME_CODE_LIST.SR'),
              config('constants.BRAND_NAME_CODE_LIST.FL'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }
      public static function depositSuccessSmsforEftAutomation($data, $lan = 'tr'){
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PP'),
              config('constants.BRAND_NAME_CODE_LIST.SR'),
              config('constants.BRAND_NAME_CODE_LIST.FL'),
          ];
          $message = ($data['amount'] ?? 0).' has been deposited to your account.';
          if($lan == 'tr'){
              $message = ($data['amount'] ?? 0). ' tutarındaki bakiyeniz hesabınıza yüklendi.';
          }
          if(Arr::isAMemberOf($brand_code, $brand_list)){
              $message = 'Your ' .config('brand.name'). ' wallet was deposit money with '. ($data['amount'] ?? 0) .' on '. (ManipulateDate::getSystemDateTime($data['created_at']) ?? '') ;
              if($lan == 'tr'){
                  $message =  config('brand.name'). ' cüzdanınıza '. ( ManipulateDate::getSystemDateTime($data['created_at']) ?? ""). ' tarihinde '. ($data['amount'] ?? 0) . ' yüklenmiştir.';
              }
          }
          return $message;
      }

      public static function isAllowChangeSenderSuccessEmailContent(){
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.PP'),
              config('constants.BRAND_NAME_CODE_LIST.SR'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isAllowRedirectPasswordPage()
      {
          $brand_code = config('brand.name_code');
          $brand_list = [
              config('constants.BRAND_NAME_CODE_LIST.YP'),
	          config('constants.BRAND_NAME_CODE_LIST.PM'),
          ];
          return Arr::isAMemberOf($brand_code, $brand_list);
      }

      public static function isSameWarningMessageForWrongPasswordAndUserNotFound(){
          $brand_code = config('brand.name_code');
          $brand_list = [
	          config('constants.BRAND_NAME_CODE_LIST.PM'),
          ];

          $registerLink = route('register');
          $errMsg = __('The information you entered does not match our records. Please check and try again. ') . __("Click here to var1");
          $errorMsg = str_replace("var1", "<a href='" . $registerLink . "'>" . __('Register') . "</a>", $errMsg);

          return [ Arr::isAMemberOf($brand_code, $brand_list), $errorMsg ];
      }
	  
	  public static function isDisableDplCurrencyDropdown(): bool
	  {
		  $brand_code = config('brand.name_code');
		  $brand_list = [
			  config('constants.BRAND_NAME_CODE_LIST.PB'),
		  ];
		  return Arr::isAMemberOf($brand_code, $brand_list);
	  }

        public static function allowPhysicalCardAndPhysicalCardMatching()
        {
            $brand_code = config('brand.name_code');
            $brand_list = [
//                config('constants.BRAND_NAME_CODE_LIST.FL'),
                config('constants.BRAND_NAME_CODE_LIST.PP'),
                config('constants.BRAND_NAME_CODE_LIST.SR'),
            ];

            $status = Arr::isAMemberOf($brand_code, $brand_list);

            if($brand_code == config('constants.BRAND_NAME_CODE_LIST.PP') && Helper::isProdServerEnvironment()){
                $status = false;
            }

            return $status;
        }
        public static function disallowTarimFromOnePageDPL(){
            $brand_code = config('brand.name_code');
            $brand_list = [
                config('constants.BRAND_NAME_CODE_LIST.SP'),
            ];
            return in_array($brand_code, $brand_list);
        }
		
		
		public static function allowWelcomeSmsForRegister()
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
		
		public static function allowUserCategoryUpdateApi(): bool
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}
		
		public static function validKycBirthYear()
		{
			$validate_kyc_year = Profile::VALIDATE_KYC_YEAR_13;
			
			$brand_code = config('brand.name_code');
			$allow_18_year = [
				config('constants.BRAND_NAME_CODE_LIST.PP'),
			];
			
			if(Arr::isAMemberOf($brand_code, $allow_18_year)){
				$validate_kyc_year = Profile::VALIDATE_KYC_YEAR_18;
			}
			
			return $validate_kyc_year;
		}
		
		public static function allowApiSentEmail()
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.PP'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

        public static function isAllowedUserVerificationQRCode(){
            $brand_code = config('brand.name_code');
            $allow_list = [
                config('constants.BRAND_NAME_CODE_LIST.PP'),
            ];
            return in_array($brand_code, $allow_list);
        }
		
		public static function isForcefullyEnableIsCustomerTcknForVerifiedPlusUser()
		{
			$brand_code = config('brand.name_code');
			$brand_list = [
				config('constants.BRAND_NAME_CODE_LIST.FL'),
			];
			return Arr::isAMemberOf($brand_code, $brand_list);
		}

	}