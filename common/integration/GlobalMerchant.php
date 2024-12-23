<?php


namespace common\integration;

use App\Http\Controllers\Traits\CommonLogTrait;
use App\Http\Controllers\Traits\ExportExcelTrait;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Controllers\Traits\SendEmailTrait;
use App\Models\AdminMakerChecker;
use App\Models\Announcement;
use App\Models\BtoC;
use App\Models\Company;
use App\Models\Country;
use App\Models\Currency;
use App\Models\DPLSetting;
use App\Models\Merchant;
use App\Models\MerchantAgreement;
use App\Models\MerchantAgreementHistory;
use App\Models\MerchantApplication;
use App\Models\MerchantB2BSetting;
use App\Models\MerchantB2CSetting;
use App\Models\MerchantBankAccount;
use App\Models\MerchantCommission;
use App\Models\MerchantDocument;
use App\Models\MerchantEmailReceiver;
use App\Models\MerchantIntegrator;
use App\Models\MerchantOperationCommission;
use App\Models\MerchantPaymentReceiveOption;
use App\Models\MerchantPosCommission;
use App\Models\MerchantReportHistory;
use App\Models\MerchantSale;
use App\Models\MerchantSettings;
use App\Models\MerchantSocialLink;
use App\Models\MerchantStatus;
use App\Models\MerchantTcknVknBlacklist;
use App\Models\MerchantTransactionLimit;
use App\Models\Otpl;
use App\Models\Pos;
use App\Models\Profile;
use App\Models\RandomCustomerId;
use App\Models\RefundHistory;
use App\Models\Role;
use App\Models\RolePage;
use App\Models\Sale;
use App\Models\Settlement;
use App\Models\SinglePaymentMerchantCommission;
use App\Models\TransactionState;
use App\Models\Usergroup;
use App\Models\UsergroupRole;
use App\Models\UserUsergroup;
use App\Models\Wallet;
use App\User;
use App\Utils\CommonFunction;
use Carbon\Carbon;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Backend\BackendAdmin;
use common\integration\Brand\Configuration\Backend\BackendMerchant;
use common\integration\Brand\Configuration\Backend\BackendMix;
use common\integration\Brand\Configuration\Frontend\FrontendAdmin;
use common\integration\CustomFileRules\FileValidation;
use common\integration\Models\CommercialCardCommission;
use common\integration\Models\DailySaleReport;
use common\integration\Models\Merchant as CommonMerchant;
use common\integration\Models\MerchantConfiguration;
use common\integration\Models\MerchantExtras;
use common\integration\Models\MerchantGroup;
use common\integration\Models\OutGoingEmail;
use common\integration\Models\SaleSettlement;
use common\integration\Models\UserAccountActivationHistory;
use common\integration\Models\UserHideMerchant;
use common\integration\Utility\Arr;
use common\integration\Utility\Cache;
use common\integration\Utility\Encode;
use common\integration\Utility\Exception;
use common\integration\Utility\File;
use common\integration\Utility\Functionality;
use common\integration\Utility\Json;
use common\integration\Utility\Language;
use common\integration\Utility\Number;
use common\integration\Utility\Phone;
use common\integration\Utility\SqlBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use common\integration\Utility\Str as StrUtility;
use DB;
use Config;
use Illuminate\Validation\Rule;

class GlobalMerchant {

   use FileUploadTrait, CommonLogTrait, SendEmailTrait, ExportExcelTrait;

   const PRIVILEGE_LOCAL = 0;
   const PRIVILEGE_GLOBAL = 1;

   const SETTLEMENT_CALENDAR_EXPORT_CHUNK_LIMIT = 500;
   const SETTLEMENT_CALENDAR_EXPORT_DATE_RANGE_LIMIT = 366; // in days (1 year)

   const RESEND_WELCOME_MAIL_CACHE_DURATION = 600;

   const CACHE_KEY_FOR_RESEND_WELCOME_MAIL = 'resent-welcome-mail-';

   const AGREEMENT_DYNAMIC_FIELDS = ["[FULL_COMPANY_NAME]", "[MERCHANT_ADDRESS]", "[MERCHANT_AUTH_EMAIL]", "[AUTH_PERSON_NAME_SURNAME]", "[PHONE_NUMBER]", "[AUTH_PERSON_ID_NUMBER_TCKN]", "[TAX_OFFICE]", "[TAX_NO]"];
   const IS_SWITCHED_MERCHANT = 'is_switched_merchant';
   const PARENT_MERCHANT_OBJ = 'parentMerchantObj';
   const PARENT_MERCHANT_AUTH_USER_OBJ = 'parentMerchantAuthUserObj';

   public $status_code = '';
   public $exists = false;
   public $exists_message = '';
   public $is_from_api = false;
   public $merchant_id = null;
   public $is_tenant_merchant = false;
   public $error_messages = [];
   public $successData = [];
   public $approve_from_api = true;
   public $status_description = '';
   public $onboarding_application_approval = false;

   public $settle_early_pre_data = [];

   const TYPE_SEND_PF_ACTIVE_RECORDS = 1;
   const TYPE_DONT_SEND_PF_ACTIVE_RECORDS = 0;
   const DIGITAL_CONTRACT_NOT_ACCEPTED = 0;

   public function checkIfDynamicFieldsReachedMaxLimit($dplBilling)
    {
        $response = $this->handleDynamicFieldMaxLimitResponse();
        $i = 0;
        foreach($dplBilling as $key => $value)
        {
            foreach($value as $k => $val)
            {
                if($k == "dynamic_field" )
                {
                    $i++;
                }
            }
        }

        if($i <= 10){
            return $response["success"];
        }
        return  $response["danger"];

    }

    public function handleDynamicFieldMaxLimitResponse()
    {
        $successMessage = "Dynamic billing field successfully added";
        $errorMessage = "Billing fields can have max 10 dynamic element";
        $responseSuccessArray = [
            "message" => $successMessage,
            "status" => "success"
        ];
        $responseFailArray = [
            "message" => $errorMessage,
            "status" => "danger"
        ];

        return [
            "success" => $responseSuccessArray,
            "danger" => $responseFailArray
        ];
    }

    public function mappingDynamicField($dplBilling, $input)
    {
        $key = Str::slug( $input['title']);
        $checkSimilar = $this->checkIfSameKeysException($dplBilling, $key);
        if($checkSimilar["status"] == "danger")
        {
            return $checkSimilar;
        }
        $dplBilling[$key] = [
            'title' => $input['title'],
            'title_tr' => $input['title_tr'],
            'enable' => (isset($input['enable'])) ? $input['enable'] : 0,
            'mendatory' => (isset($input['mendatory'])) ? $input['mendatory'] : 0,
            'dynamic_field' => true
        ];

        if (BrandConfiguration::call([Mix::class, 'isAllowInputTypeInDPLDynamicField'])) {
            $dplBilling[$key] = $dplBilling[$key] + [
                    'input_field_type' => (isset($input['input_field_type'])) ? $input['input_field_type'] : "",
                    'items' => (isset($input['items'])) ? $input['items'] : "",
                    'is_option_multiple' => (isset($input['is_option_multiple'])) ? $input['is_option_multiple'] : ""
                ];
        }

        return $dplBilling;
    }

    public function checkIfSameKeysException($dplBilling, $key)
    {
        $response = $this->handleKeyTitleEqualResponse();
        $status = true;
        foreach($dplBilling as $k => $value)
        {
            if($k == $key){
                $status = false;
                break;
            }
        }
        if($status)
        {
            return $response["success"];
        }
        return $response["danger"];
    }

    public function handleKeyTitleEqualResponse()
    {
        $successMessage = "Dynamic billing field successfully added";
        $errorMessage = "Billing fields can't have same title";
        $responseSuccessArray = [
            "message" => $successMessage,
            "status" => "success"
        ];
        $responseFailArray = [
            "message" => $errorMessage,
            "status" => "danger"
        ];

        return [
            "success" => $responseSuccessArray,
            "danger" => $responseFailArray
        ];
    }

    public function validateDynamicField($dplBilling, $input)
    {
        $message = "";
        $status = "danger";
        $key = Str::slug( $input['title']);
        $validateSuccessMessage = "Dynamic billing field successfully added";
        $maxLimitValidation = $this->checkIfDynamicFieldsReachedMaxLimit($dplBilling);
        $duplicateKeyValidation = $this->checkIfSameKeysException($dplBilling, $key);
        if(isset($maxLimitValidation["status"]) && $maxLimitValidation["status"] == "danger")
        {
            return $maxLimitValidation;
        }
        elseif(isset($duplicateKeyValidation["status"]) && $duplicateKeyValidation["status"] == "danger")
        {
            return $duplicateKeyValidation;
        }

        else{

            return [
                "message" => $validateSuccessMessage,
                "status" => "success"
            ];
        }
    }


   public static function isMarketplaceMerchant($merchantObj)
   {

      $status = false;
      if (!empty($merchantObj) && isset($merchantObj->type) && (
        $merchantObj->type == Merchant::MARKETPLACE_MERCHANT || $merchantObj->type == Merchant::TAXI_MERCHANT)) {
         $status = true;
      }

      return $status;
   }

    public static function isAlowDPLModule($merchantSettings)
    {
        $status = false;
        if (!empty($merchantSettings) && isset($merchantSettings->is_allow_dpl) && (
                $merchantSettings->is_allow_dpl == 1 )) {
            $status = true;
        }
        return $status;
    }

    public static function isAlowManualPosModule($merchantSettings)
    {
        $status = false;
        if (!empty($merchantSettings) && isset($merchantSettings->is_allow_manual_pos) && (
                $merchantSettings->is_allow_manual_pos == 1 )) {
            $status = true;
        }
        return $status;
    }


   public function createMerchant($data){


       $rules = $this->merchant_validation_rules(false,$this->is_tenant_merchant);
       $message = $this->merchant_validation_message();
       $validator = Validator::make($data, $rules, $message);

       if ($validator->fails()) {
           return $this->error_messages = $validator->errors();
           /*if ($this->is_from_api) {
               return $this->error_messages = $validator->errors();
           } else {
               return redirect()->back()
                   ->withErrors($validator)
                   ->withInput();
           }*/
       }

       $send_create_password_email = true;

      try {

         DB::beginTransaction();

         // check tckn/vkn in blacklist
          $this->checkTcknVknInBlacklist($data['tckn'], $data['vkn']);
          if ($this->exists) {
              if ($this->is_from_api) {
                  return $this->error_messages = $this->exists_message;
              } else {
                  flash($this->exists_message, 'danger');
                  return back();
              }
          }

         ////Company Adding
         $company_params = [];
         $company_params['name'] = $data['fullCompanyName'];
         $company_params['tax_no'] = 0;
         $company_params['address1'] = $data['address'];
         $company_params['status'] = Company::COMPANY_ACTIVE;

          $path = '';
          if (!empty($data['merchantlogo'])) {
              $company_params['logo'] = '';

              if($this->onboarding_application_approval) {
                  $path = $data['merchantlogo'];
              } else {
                  $path = $this->moveResourceFile($data['merchantlogo'], 'merchant/logo', 'merchant/logo/'.basename($data['merchantlogo']));
              }

              if($path){
                  $company_params['logo'] = $path;
              }
          }

         $company = new Company();
         $company->add_company($company_params);
         $company_id = $company->id;

         //get random customer number
         $rancustid = new RandomCustomerId();
          if (BrandConfiguration::isAllowSequentialCustomerId()) {
              $customer_number = $rancustid->getAndUpdateSequentialCustomerId();
          } else {
              $customer_number = $rancustid->getRandomCustomerId(Config::get('constants.defines.MERCHANT_USER_TYPE'));
          }

         $first_name = $last_name = '';
         if (isset($data['authorized_person_name']) && !empty($data['authorized_person_name'])){
            list($first_name, $last_name) = (new GlobalUser())->userFirstNameLastName($data['authorized_person_name']);
         }
          $password = bcrypt('Nop@ss1234');
          $is_admin_verified = User::ADMIN_VERIFIED;
          if (!empty($data['source']) && $data['source'] == MerchantApplication::SOURCE_ON_BOARDING) {
              $password = $data['password'] ?? '';
              $is_admin_verified = $data['is_admin_verified'];
          }

         //Create an user
         $user_params = [];
         $user_params['name'] = $data['authorized_person_name'];
         $user_params['first_name'] = $first_name;
         $user_params['last_name'] = $last_name;
         $user_params['company_id'] = $company_id;
         $user_params['email'] = $data['authorized_person_email'];
         $user_params['language'] = $data['language'];
         $user_params['user_phone'] = $data['authorizedPersonPhoneNumber'];
         $user_params['is_admin_verified'] = $data['is_admin_verified'] ?? $is_admin_verified;
         $user_params['password'] = $data['password'] ?? $password;
         $user_params["user_type"] = Config::get('constants.defines.MERCHANT_USER_TYPE');
         $user_params['profile'] = $path;
         $user_params['customer_number'] = $customer_number;
         $user_params['status'] = (isset($search['status'])  && ($search['status'] >= 0)) ? 1 : 0;
         $user_params['created_by_id'] = $data['created_by_id'];
         $user_params['currency_id'] = $data['currency_id'] ?? Currency::TRY;
         $user_params['created_by_name'] = $data['created_by_name'];
         $user_params['source'] = $data['source'] ?? '';
         $user_params['dob'] = $data['dob'] ?? null;

         $user_params['otp_channel'] = (new GlobalUser())->getDefaultOtpChannel();
          

         $user_model = new User();
         $user_obj = $user_model->add_user($user_params);
         $user_id = $user_obj->id;

         //update user by merchant Parent Id
          $user_obj->update([
           'merchant_parent_user_id' => $user_id
         ]);

         //delete used customer number
          if (!BrandConfiguration::isAllowSequentialCustomerId()) {
              $rancustid->deleteRandomCustomerId($customer_number);
          }

         ///User Adding
         if (!isset($data['send_pf_records'])) {
             if(BrandConfiguration::call([Mix::class, 'isAllowNewMerchantDefaultActive'])) {
                 $data['send_pf_records'] = self::TYPE_SEND_PF_ACTIVE_RECORDS;
             } else {
                 $data['send_pf_records'] = self::TYPE_DONT_SEND_PF_ACTIVE_RECORDS;
             }
         }

         $merchant_model = new Merchant();
         $request = collect($data);
         $merchant_params = $request->only(
           array('site_url', 'merchant_name',
             'business_area', 'contact_person_name', 'contact_person_phone',
             'contact_person_email', 'merchant_type',
             'fullCompanyName', 'authorized_person_name',
             'authorizedPersonPhoneNumber', 'authorized_person_email', 'address',
             'zip_code', 'city', 'country', 'expected_volumn', 'sale_platform',
             'applicationDate', 'activationDate', 'merchant_block', 'tckn', 'vkn',
             'send_pf_records'
           ));
         if($this->is_from_api || $this->is_tenant_merchant){
             $auth_id = $user_id;
         }else {
             $auth_id = Auth::user()->id;
         }
         if($this->is_tenant_merchant){
             $merchant_params['merchant_id'] = $data['merchant_id'];
         }
         $merchant_params['merchant_key'] = $merchant_model->generateMerchantKey($auth_id);
         $merchant_params['user_id'] = $user_id;
         $merchant_params['logo'] = $path;
         $merchant_params['is_new_merchant'] = $data['is_new_merchant'] ?? Merchant::NEW_MERCHANT;
         $merchant_params['is_digital_contract_accept'] = $data['is_digital_contract_accept'] ?? self::DIGITAL_CONTRACT_NOT_ACCEPTED;
         $merchant_params['status'] = $data['merchant_status'] ?? 0;
         $merchant_params['source_id'] = $data['source_id'] ?? null;
         $merchant_params['payment_integration_option'] = $data['payment_integration_option'] ?? 0;
         $merchant_params['is_allow_foreign_cards'] = $data['is_allow_foreign_cards'] ?? 0;
         $merchant_params['allow_pay_by_token'] =  $data['allow_pay_by_token'] ?? ($data['is_allow_pay_by_token'] ?? 0);
         $merchant_params['calculate_pos_by_bank'] = $data['calculate_pos_by_bank'] ?? 0;
         $merchant_params['is_allow_b2c_automation'] = $data['is_allow_b2c_automation'] ?? 0;
         $merchant_params['is_allow_walletgate'] = $data['is_allow_b2c_to_walletgate'] ?? 0;
         $merchant_params['dpl_option'] = $data['dpl_option'] ?? 0;
         $merchant_params['is_manual_pos_3d'] = $data['is_manual_pos_3d'] ?? 0;
         $merchant_params['type'] = $data['type'] ?? Merchant::GENERAL_MERCHANT;
         $merchant_params['merchant_type'] = $data['merchant_type'] ?? '';
         $merchant_params['is_3d'] = $data['is_3d'] ?? 0;
         $merchant_params['settlement_type'] = $data['settlement_type'] ?? '';
         $merchant_params['currency_id'] = $data['currency_id'] ?? Currency::TRY;

        $merchant_params['address2'] = $data['address2'] ?? '';
        $merchant_params['iso_country_code'] = $data['iso_country_code'] ?? '';
        $merchant_params['mcc'] = $data['mcc'] ?? '';
        $merchant_params['brand_accountant_email'] = $data['brand_accountant_email'] ?? '';
        $merchant_params['tax_no'] = $data['tax_no'] ?? '';
        $merchant_params['tax_office'] = $data['tax_office'] ?? '';
        $merchant_params['remote_sub_merchant_id'] = $data['remote_sub_merchant_id'] ?? '';
        $merchant_params['linked_pf_merchant_id'] = $data['linked_pf_merchant_id'] ?? '';
        $merchant_params['tenant_approval_status'] = $data['tenant_approval_status'] ?? null;

        if(BrandConfiguration::isMerchantApplicationCustomizedWay()){

            if($data['source'] == MerchantApplication::SOURCE_ON_BOARDING){
                $merchant_params['country_id'] =  Country::DEFULT_COUNTRY_ID;
            }
            $merchant_params['address1'] = $data['address'] ?? null;
            $merchant_params['vkn'] = $data['tax_no'] ?? null;
            $merchant_params['district'] = $data['district'] ?? null;
            $merchant_params['payment_integration_option'] = $data['payment_integration_option'] ?? 0;
            $merchant_params['is_receive_payment_receipt'] = $data['is_receive_payment_receipt'] ?? 0;
        }
        // "average_monthly_earning" input value is unused
        //$merchant_params['average_turnover'] = $data['average_monthly_earning'] ?? '';
          if (BrandConfiguration::call([BackendMix::class, 'showCustomMerchantApplicationFields'])) {
              $merchant_params['district'] = $data['district'] ?? null;
          }
          if (BrandConfiguration::call([BackendAdmin::class, 'isAllowCityLicenseTag'])) {
              $merchant_params['license_tag'] = $data['license_tag'] ?? null;
          }
          if(!empty($data['is_onboarding_merchant_customize_api']) && $this->is_from_api) {
              $merchant_params['main_dealer_id'] = $data['main_dealer_id'];
              $merchant_params['send_pf_records'] = $data['send_pf_records'];
              $merchant_params['dealer_type'] = $data['dealer_type'];
              $merchant_params['country'] =  Country::DEFULT_COUNTRY_ID;
              $send_create_password_email = $data['dealer_type'] != Merchant::SUB_DEALER_MERCHANT;
          }

         $merchant_id = $merchant_model->inser_entry($merchant_params);
         $this->merchant_id = $merchant_id;

         if(BrandConfiguration::call([Mix::class, 'allowGroupMerchantWhileCreateNew'])){
             $old_data = $data['old_data'];
             if(!empty($old_data) && (!empty($old_data['main_merchant_email']) || !empty($old_data['main_merchant_phone'])))
             {
                 $keys = $this->prepareSearchKeys($data['old_data']);
                 if(!empty($keys)){
                     $merchantObj  = $merchant_model->findByKeys($keys);
                 }

                 if(!empty($merchantObj)){
                     $group_merchant_ids = [$merchant_id];
                     $authObj = $data['auth_object'];
                     $this->processMerchantGroup($merchantObj, $group_merchant_ids, $authObj, true);
                     $group_merchant_main_log_data = $keys;
                 }
             }

         }
         
         // create merchant extra
          $merchantExtras = new MerchantExtras();
         
          $data['merchant_id'] = $merchant_id;
          $prepare_extra_merchant = $merchantExtras->prepareData($data);
          if(!empty($data['merchant_extra']) && !empty($data['is_onboarding_merchant_customize_api']) && $this->is_from_api){
              $prepare_extra_merchant = $this->prepareSingleMerchantData($data['merchant_extra'], $merchant_id);
          }
          
          if (BrandConfiguration::call([BackendAdmin::class, 'isAllowMerchantIksAutomation'])) {
              $prepare_extra_merchant['iks_automation_attempts'] = Merchant::MIN_IKS_ATTEMP;
          }
          
          if (!empty($prepare_extra_merchant)){
              $merchantExtras->insertData($prepare_extra_merchant);
          }

         //Inserting data into MerchantPaymentReceiveOption
         //$this->saveMerchantPaymentOption($data['paymentReceiveOptions'], $merchant_id);

          if(empty($data['paymentReceiveOptions'])){
              if($this->is_from_api){
                  $this->exists_message = __("Data can't be empty");
              }else{
                  return  "Data can't be empty";
              }
          }
          $merchantPaymentOptionObj = new MerchantPaymentReceiveOption();
          $merchantPaymentOptionObj->updateMerchantPaymentsOptions($data['paymentReceiveOptions'], $merchant_id);

         $api_key = $this->getGeneratedToken($merchant_id);
         $api_secret = $this->getGeneratedToken($merchant_id);
         $merchant_settings = new MerchantSettings();
         if (($this->is_tenant_merchant) && isset($data['merchant_setting']) && !empty($data['merchant_setting'])) {
              $merchant_setting = $this->prepareSingleMerchantData($data['merchant_setting'], $merchant_id, $api_key, $api_secret);
              $merchant_settings->saveMerchantSetting($merchant_setting);
         }else if(($this->is_from_api) && !empty($data['main_dealer_id']) && BrandConfiguration::call([Mix::class, 'isAllowedMainAndSubDealerIntegration'])) {
             $merchant_setting = $this->prepareSingleMerchantData($data['merchant_setting'], $merchant_id, $api_key, $api_secret);
             $merchant_settings->saveMerchantSetting($merchant_setting);
         }
         else {
             $merchant_settings->merchant_id = $merchant_id;
             $merchant_settings->app_id = $api_key;
             $merchant_settings->app_secret = $api_secret;
             $merchant_settings->dpl_pos_option = $data['dpl_pos_option'] ?? '';
             $merchant_settings->manual_pos_options = $data['manual_pos_option'] ?? '';
             $merchant_settings->is_allow_manual_pos = $data['is_allow_manual_pos'] ?? '';
             $merchant_settings->is_allow_dpl = $data['is_allow_dpl'] ?? '';
             $merchant_settings->is_allow_pre_auth = $data['is_allow_preauth_transaction'] ?? '';
             $merchant_settings->is_allow_one_page_payment = $data['is_allow_one_page_payment'] ?? '';
             $merchant_settings->is_allow_recurring_payment = $data['is_allow_recurring_payment'] ?? '';
             $merchant_settings->return_url = $merchant_params['site_url'] ?? '';
             $merchant_settings->is_installment_wise_settlement = $data['is_installment_wise_settlement'] ?? 0;
             $merchant_settings->is_physical_pos_allow = $data['is_physical_pos_allow'] ?? 0;
             $merchant_settings->is_allow_pavo = $data['is_allow_pavo'] ?? 0;
             $merchant_settings->is_allowed_pax = $data['is_allowed_pax'] ?? 0;
             if(BrandConfiguration::isMerchantApplicationCustomizedWay()){
                 $merchant_settings->dpl_pos_option = $data['dpl_pos_option'] ?? 0;
                 $merchant_settings->manual_pos_options = $data['manual_pos_options'] ?? 0;
                 $merchant_settings->is_allow_3d_cvvless = $data['is_allow_3d_cvvless'] ?? 0;
                 $merchant_settings->is_free_refund = $data['is_free_refund'] ?? 0;
             }

             if(BrandConfiguration::call([Mix::class, 'isAllowNewMerchantDefaultActive'])) {
                 $merchant_settings->is_show_installment_table = MerchantSettings::IS_SHOW_INSTALLMENT_TABLE;
             }
             $merchant_settings->save();
         }


         //merchant documents
         $merchant_document = new MerchantDocument();

         if($this->is_tenant_merchant){
            $document = $data['merchant_document'];
            $document['merchant_id'] = $merchant_id;
            $merchant_document->saveMerchantDocument($document);
         }else{
             // Deleting Doc files of the merchant application if exist
             if(BrandConfiguration::call([BackendMix::class, 'isAllowMerchantApplicationDocuments']) && isset($data['merchant_application_id'])) {
                 (new PackageService())->removeAndUploadMerchantApplicationDoc($data['merchant_application_id'], true, $merchant_id, $data['merchant_type'] ?? 0);
             }else {
             $file_path_1 = $file_path_2 = $file_path_3 = '';
             $file_1_status = $file_2_status = $file_3_status = 0;

             if ($this->onboarding_application_approval) {
                 $file_path_1 = $data['tax_board'];
                 $file_1_status = empty($file_path_1) ? $file_1_status : 1;
                 $file_path_2 = $data['signature'];
                 $file_2_status = empty($file_path_2) ? $file_2_status : 1;
                 $file_path_3 = $data['trade_registry'];
                 $file_3_status = empty($file_path_3) ? $file_3_status : 1;
             } else {
                 if ($this->is_from_api && isset($data['all_mandatory_docs']) && $data['all_mandatory_docs']) {
                     $file_1_status = $file_2_status = $file_3_status = $data['all_mandatory_docs'];
                 }
                 if (isset($data['document_list']) && count($data['document_list']) > 0) {

                     $directory = 'merchant/documents/' . $merchant_id;

                     $i = 1;
                     foreach ($data['document_list'] as $document) {

                         if (!empty($document)) {
                             $path = $this->moveResourceFile($document, $directory, $directory . '/' . basename($document));

                             if ($i == 1) {
                                 $file_path_1 = $path;
                                 $file_1_status = 1;
                             } elseif ($i == 2) {
                                 $file_path_2 = $path;
                                 $file_2_status = 1;
                             } elseif ($i == 3) {
                                 $file_path_3 = $path;
                                 $file_3_status = 1;
                             }

                         }
                         $i++;
                     }
                 }
             }


             $merchant_document_data = [
                 'merchant_id' => $merchant_id,
                 'file_1_path' => $file_path_1,
                 'file_2_path' => $file_path_2,
                 'file_3_path' => $file_path_3,
                 'file_1_status' => $file_1_status,
                 'file_2_status' => $file_2_status,
                 'file_3_status' => $file_3_status,
             ];
             $merchant_document->saveMerchantDocument($merchant_document_data);
             }
         }

          if (isset($data['social_media']) && count($data['social_media']) > 0) {
              $data['insert_social_link'] = true;
              $this->insertMerchantSocialLink($data, $merchant_id);
              /*$social_list_data = [];
              foreach ($data['social_media'] as $key => $social_link) {
                  $social_list_data[$key] = [
                      'merchant_id' => $merchant_id,
                      'media' => $key,
                      'media_link' => $social_link,
                  ];
              }
              if (!empty($social_list_data)) {
                  (new MerchantSocialLink())->saveData($social_list_data);
              }*/
          }

         //add UserGroup
         $usergroup_params["group_name"] = Config::get('constants.defines.MERCHANT_GROUP_NAME');
         $usergroup_params["company_id"] = $company_id;
         $usergroup = Usergroup::insert_entry($usergroup_params);
         $usergroup_id = $usergroup->id;

         //add Role
         $role_params["title"] = Config::get('constants.defines.MERCHANT_ROLE_TITLE');
         $role_params["company_id"] = $company_id;
         $role_params["created_by_id"] = $data['created_by_id'] ?? 0;
         $role = Role::insert_entry($role_params);

         //add User_usergroup
         $user_usergroup_params["usergroup_id"] = $usergroup_id;
         $user_usergroup_params["user_id"] = $user_id;
         $user_usergroup_params["company_id"] = $company_id;
         $user_usergroup = UserUsergroup::insert_entry($user_usergroup_params);

         //add User_usergroup role
         $usergroup_role = new UsergroupRole();
         $usergroup_role->usergroup_id = $usergroup->id;
         $usergroup_role->role_id = $role->id;
         $usergroup_role->company_id = $company->id;
         $usergroup_role->save();

         //add Role page
//            $page_id = array_merge(
//                Config::get('constants.defines.MERCHANT_ROLE_PAGE'),
//                Config::get('constants.defines.MERCHANT_INFORMATION_PAGE')
//            );
//
         $page_id = Config::get('constants.defines.MERCHANT_ALL_PAGE');

         for ($i = 0; $i < count($page_id); $i++) {
            $role_page = new  RolePage();
            $role_page->role_id = $role->id;
            $role_page->page_id = $page_id[$i];
            $role_page->save();
         }

          //Add wallet
          $wallet = new Wallet();
          if (($this->is_tenant_merchant) && isset($data['wallets']) && !empty($data['wallets'])) {
              $wallets = $this->checkFillableValue(
                  $this->prepareMerchantWalletData($data['wallets'], $user_id),
                  GlobalFillable::WALLET_FILLABLE);

              $wallet->saveWallet($wallets);
          } else {
              $wallet_params['currency_id'] = $data['currency_id'] ?? Currency::TRY;
              $merchant_wallets = $wallet->add_wallet_by_currency($user_id, $wallet_params);
          }

          if (($this->is_tenant_merchant) && isset($data['banks']) && !empty($data['banks'])) {
              $banks = $this->checkFillableValue(
                  $this->prepareMerchantData($data['banks'], $merchant_id),
                  GlobalFillable::MERCHANT_BANK_ACCOUNT);

              (new MerchantBankAccount())->saveMerchantBankAccount($banks);
          }
          if (($this->is_tenant_merchant) && isset($data['merchant_commission']) && !empty($data['merchant_commission'])) {
              $merchant_commission = $this->checkFillableValue(
                  $this->prepareMerchantData($data['merchant_commission'], $merchant_id),
                  GlobalFillable::MERCHANT_COMMISSION);
              (new MerchantCommission())->saveMultiMerchantCommission($merchant_commission);
          }

          if (($this->is_tenant_merchant) && isset($data['dpl_settings']) && !empty($data['dpl_settings'])) {
              $dpl_settings = $this->prepareSingleMerchantData($data['dpl_settings'], $merchant_id);
              (new DPLSetting())->saveDPLSetting($dpl_settings);
          }

          if (($this->is_tenant_merchant) && isset($data['otpls']) && !empty($data['otpls'])) {
              $otpls = $this->checkFillableValue(
                  $this->prepareMerchantData($data['otpls'], $merchant_id),
                  GlobalFillable::OTPL_FILLABLE);
              (new Otpl())->saveMultiOtpl($otpls);
          } else if(BrandConfiguration::call([Mix::class, 'isAllowNewMerchantDefaultActive'])) {
              $otpls = (new Otpl())->setStatus($merchant_id, Otpl::ACTIVE);
          }

          if (($this->is_tenant_merchant) && isset($data['merchant_b2c_settings']) && !empty($data['merchant_b2c_settings'])) {
              $merchant_b2c_settings = $this->checkFillableValue(
                  $this->prepareMerchantData($data['merchant_b2c_settings'], $merchant_id),
                  GlobalFillable::MERCHANT_B2C_SETTING);
              (new MerchantB2CSetting())->saveMultiMerchantB2CSetting($merchant_b2c_settings);
          }

          if (($this->is_tenant_merchant) && isset($data['merchant_b2b_settings']) && !empty($data['merchant_b2b_settings'])) {
              $merchant_b2b_settings = $this->checkFillableValue(
                  $this->prepareMerchantData($data['merchant_b2b_settings'], $merchant_id),
                  GlobalFillable::MERCHANT_B2B_SETTING);
              (new MerchantB2BSetting())->saveMultiMerchantB2BSetting($merchant_b2b_settings);
          }

          if (($this->is_tenant_merchant) && isset($data['merchant_operation_commissions']) && !empty($data['merchant_operation_commissions'])) {
              $merchant_operation_commissions = $this->checkFillableValue(
                  $this->prepareMerchantData($data['merchant_operation_commissions'], $merchant_id),
                  GlobalFillable::MERCHANT_OPERATION_COMMISSION);
              (new MerchantOperationCommission())->saveMultiMerchantOperationCommission($merchant_operation_commissions);
          }

         if(($this->is_from_api) && isset($data['pos_id']) && !empty($data['pos_id'])){
             // merchant pos commission data store
             if(!empty($data['is_onboarding_merchant_customize_api']) && !empty($data['dealer_type']) && $data['dealer_type'] == Merchant::SUB_DEALER_MERCHANT) {
                 $merchantPosCommissionObj = new MerchantPosCommission();
                 $merchant_pos_commissions =  $merchantPosCommissionObj->getMerchantPosCommisionByMerchantId($data['main_dealer_id'])->toArray();
                 $merchant_pos_commissions = $this->prepareMerchantData($merchant_pos_commissions, $merchant_id);
                 $merchantPosCommissionObj->saveMultiMerchantPosCommission($merchant_pos_commissions);

                 $data['merchant_commission'] = (new MerchantCommission())->getPosCommissionBYMerchantId($data['main_dealer_id'])->toArray();

                 $merchant_commission = $this->checkFillableValue(
                     $this->prepareMerchantData($data['merchant_commission'], $merchant_id),
                     GlobalFillable::MERCHANT_COMMISSION);
                 (new MerchantCommission())->saveMultiMerchantCommission($merchant_commission);
             }else {
                 $pos_data =  (new Pos())->getPosByPosId($data['pos_id']);
                 if(empty($pos_data)){
                     return $this->error_messages = __("Pos not found");
                 }
                 $merchant_pos_commission = $this->prepareMerchantPosCommissionData($data);
                 $merchant_pos_commission['merchant_id'] = $merchant_id;
                 $merchantPosCommissionObj =  new MerchantPosCommission();
                 $merchantPosCommissionObj->saveEntry($merchant_pos_commission, $data['installment'], $data['pos_id']);

                 // merchant  commission data store
                 $creditCard_data = $this->prepareCreditCardData($data);
                 $merchantCommissionObj =  new MerchantCommission();
                 $merchantCommissionObj->is_from_onboarding_api = true;
                 $merchantCommissionObj->saveMerchantCommission($merchant_id,$creditCard_data);
             }

            // otp table store data
            $otpl_data =  $this->prepareOtplData($data);
            $otpl_data['merchant_id'] = $merchant_id;
            $otplObj = new Otpl();
            $otplObj->singleCurrencyDataSave($otpl_data);

            // merchant integrator table data tore
             if (isset($data['integrator_id']) && !empty($data['integrator_id'])) {
                 $mi_data = $this->prepareMerchantIntegratorData($data);
                 $mi_data['merchant_id'] = $merchant_id;
                 $mercIntObj = new MerchantIntegrator();
                 $mercIntObj->insertData($mi_data);
             }
         }


         if($this->is_from_api || $this->is_tenant_merchant){
             $this->successData = [
                 'app_id' => $api_key,
                 'app_secret' => $api_secret,
                 'merchant_key' => $merchant_params['merchant_key'],
                 'merchant_id' => $merchant_id
             ];
         }

          if (isset($data['merchantApplicationObj']) && !empty($data['merchantApplicationObj'])) {
              $merchantApplicationObj = $data['merchantApplicationObj'];

              if ($this->is_tenant_merchant && !empty($this->successData)) {
                  $licenseOwnerObj = new LicenseOwnerTenantMerchant($merchantApplicationObj);
                  $licenseOwnerObj->processApprove($this->successData);
                  if ($licenseOwnerObj->approve != ApiService::API_SERVICE_SUCCESS_CODE) {
                      $this->approve_from_api = false;
                      $this->status_description = $licenseOwnerObj->status_description;
                  }
              }

              $merchantApplicationObj->stage = GlobalMerchantApplication::STAGE_COMPLETED;

              if(BrandConfiguration::call([Mix::class,'isAllowedOnboardingPanel'])){
                  $merchantApplicationObj->sub_stage = GlobalMerchantApplication::SUBSTAGE_ACTIVATED;
              }
              $merchantApplicationObj->status = GlobalMerchantApplication::STATUS_APPROVED;

              $merchantApplicationObj->save();
          }

          if(!empty($data['is_onboarding_merchant_customize_api']) && $this->is_from_api) {
              if(!empty($data['commercial_card_commissions'])){
                  (new CommercialCardCommission())->insertCardCommission($this->prepareMerchantData($data['commercial_card_commissions'], $merchant_id));
              }

              if(!empty($data['merchant_agreement_history'])){
                  (new MerchantAgreementHistory())->insertData($this->prepareMerchantData($data['merchant_agreement_history'], $merchant_id));
              }

              if(!empty($data['single_payment_merchant_commissions'])){
                  (new SinglePaymentMerchantCommission())->insertData($this->prepareMerchantData($data['single_payment_merchant_commissions'], $merchant_id));
              }

              if(!empty($data['merchant_transaction_limits'])){
                  (new MerchantTransactionLimit())->insertTransactionLimitData($this->prepareMerchantData($data['merchant_transaction_limits'], $merchant_id));
              }

              if (!empty($data['merchant_b2c_settings'])) {
                  $merchant_b2c_settings = $this->checkFillableValue(
                      $this->prepareMerchantData($data['merchant_b2c_settings'], $merchant_id),
                      GlobalFillable::MERCHANT_B2C_SETTING);
                  (new MerchantB2CSetting())->saveMultiMerchantB2CSetting($merchant_b2c_settings);
              }

              if (!empty($data['merchant_b2b_settings'])) {
                  $merchant_b2b_settings = $this->checkFillableValue(
                      $this->prepareMerchantData($data['merchant_b2b_settings'], $merchant_id),
                      GlobalFillable::MERCHANT_B2B_SETTING);
                  (new MerchantB2BSetting())->saveMultiMerchantB2BSetting($merchant_b2b_settings);
              }

              if (!empty($data['merchant_operation_commissions'])) {
                  $merchant_operation_commissions = $this->checkFillableValue(
                      $this->prepareMerchantData($data['merchant_operation_commissions'], $merchant_id),
                      GlobalFillable::MERCHANT_OPERATION_COMMISSION);
                  (new MerchantOperationCommission())->saveMultiMerchantOperationCommission($merchant_operation_commissions);
              }
          }

         if (!$this->approve_from_api) {
             DB::rollBack();
         } else {
             DB::commit();
             $this->status_code = 100;
         }

      } catch (\Throwable $e) {

         DB::rollBack();
         (new ManageLogging())->createLog([
              "Action" => "MERCHANT_CREATED_FAILED",
              "error" => Exception::fullMessage($e)
          ]);

         if($this->is_from_api){
             return $this->error_messages = $e->getMessage();
         }else{
             flash($e->getMessage(), 'danger');
             return back();
         }
      }


      //database operation end

       if ($send_create_password_email &&
           ((isset($data['source']) && $data['source'] != MerchantApplication::SOURCE_ON_BOARDING)
               || !BrandConfiguration::isMerchantApplicationCustomizedWay())
       ) {

           $merchant_model->sendMerchantNotification($user_obj);
           //sending email
           $encrypted_email = $this->customEncryptionDecryption($user_params['email'], config('app.brand_secret_key'), 'encrypt', true);
           $data['merchent_panel_link'] = config('app.app_merchant_url');
           $data['create_password_link'] = config('app.app_merchant_url') . "/password/create/" . $encrypted_email;
           $data['name'] = $data['authorized_person_name'];
           if (BrandConfiguration::welcomeMailNewContentForMerchantV1()) {
               $emailTemplate = "merchant.merchant_create_v1";
           }
           else{
               $emailTemplate = "merchant.merchant_create";
           }
           $from = Config('app.SYSTEM_NO_REPLY_ADDRESS');
           $data['email'] = $user_params['email'];
           $to = $user_params['email'];

           //out_going_email
           $this->setGNPriority(OutGoingEmail::PRIORITY_HIGH);
           $this->sendEmail($data, "Merchant_Create", $from, $to,
               "", $emailTemplate, $user_obj->language);
       }

      $businessLogData['action'] = "ADMIN_MERCHANT_ADD_SUCCESS";
      $logData = $request->all();
      unset($logData['password']);
      $businessLogData['message'] = "The record has been saved successfully!";
      $businessLogData['input_data'] = $logData;
      $businessLogData['GROUP_MERCHANT_MAIN_DATA'] = $group_merchant_main_log_data ?? null;
      $this->createLog($this->_getCommonLogData($businessLogData));
   }

    public function merchant_validation_rules($approved_by_checker = false,$tenant=false)
    {
        $phone_format=!$tenant?'|regex:/^[+]\d+$/':'';
        $email_validation_rules = $tenant ? "" : "email";

        $merchant_name_rules = BrandConfiguration::allowMerchantNameInEnglishOnly() ? '|max:24|'.AppRequestValidation::ruleAlphaNumericSpaces()['rule']:'|string|max:100';
        $business_area_validation = BrandConfiguration::isNotRequiredBusinessArea() ? "nullable" : "required";
        $rules = [
            'merchant_name' => 'required'.$merchant_name_rules,
            'fullCompanyName' => 'required|string|max:100',
            'authorized_person_name' => 'required|string|max:100',
            'merchant_description' => 'nullable|max:191',
            'paymentReceiveOptions' => 'required|array|min:1',
            'authorizedPersonPhoneNumber' => 'required|max:50|unique_phone:' . User::MERCHANT.$phone_format,
            'contact_person_phone' => 'nullable|max:50'.$phone_format,
            'authorized_person_email' => 'required|'.$email_validation_rules.'|string|max:50|unique_email:' . User::MERCHANT,
            'merchant_type' => 'required',
            'business_area' => $business_area_validation,
            'zip_code' => 'nullable|numeric',
        ];
        if (BrandConfiguration::call([Mix::class, 'isAllowedMainAndSubDealerIntegration'])) {
            $rules['main_dealer_id'] = [
                'required_if:merchant_dealer_type,'.Merchant::SUB_DEALER_MERCHANT,
                function ($attribute, $value, $fail) {
                    if (request('merchant_dealer_type') == Merchant::SUB_DEALER_MERCHANT) {
                        $mainDealer = (new Merchant())->getActiveMerchantById($value);
                        if (!$mainDealer || $mainDealer->status != Merchant::ACTIVE_STATUS) {
                            $fail('Main dealer is required, and must be an active merchant.');
                        }
                    }
                }
            ];
        }
        if (BrandConfiguration::call([FrontendAdmin::class, 'isAllowValidationForTcknAndVkn'])) {
            if(request()->merchant_type == Merchant::CORPORATE_MERCHANT_TYPE){
                $rules['vkn'] ='required|max:10';
            }
            if(request()->merchant_type == Merchant::INDIVIDUAL_MERCHANT_TYPE){
                $rules['tckn'] ='required|max:11';
            }
        }

        if ($approved_by_checker) {
            $rules['merchantlogo'] = 'nullable';
        } else {
            $rules['merchantlogo'] = ['sometimes', new FileValidation(['png','jpg','jpeg'])];
        }

        if(BrandConfiguration::allowVKNunique()){
            if(isset(request()->merchant_type) && request()->merchant_type == Merchant::CORPORATE_MERCHANT_TYPE){
                $rules['vkn'] = ['required', Rule::unique(CommonMerchant::class)];
            }else{
                $rules['vkn'] = ['nullable', Rule::unique(CommonMerchant::class)];
            }
        }

        if(BrandConfiguration::allowTCKNRequiredAndUnique()){
            if(isset(request()->merchant_type) && request()->merchant_type == Merchant::INDIVIDUAL_MERCHANT_TYPE){
                $rules['tckn'] = ['required', Rule::unique(CommonMerchant::class)];
            }else{
                $rules['tckn'] = ['nullable', Rule::unique(CommonMerchant::class)];
            }
        }

        if (BrandConfiguration::allowSomeDocumentsMerchantApplication()) {
            if ($approved_by_checker) {
                $required_type = ['nullable'];
            } else {
                $required_type = ['required', 'mimes:png,jpg,jpeg,webp,tiff,pdf,doc'];
            }

            if($this->onboarding_application_approval) {
                $required_type = ['required'];
            }

            $rules = $rules + $this->getMerchantExtraDocumentRules($required_type);
        }

        if (BrandConfiguration::isAllowedIKSMerchant()) {
            $rules['district'] = isset(request()->district) ? 'required' : 'nullable';
            $rules['neighborhood'] = isset(request()->neighborhood) ? 'required' : 'nullable';
            $rules['license_tag'] = isset(request()->license_tag) ? 'required' : 'nullable';
            $rules['psp_flag'] = isset(request()->psp_flag) ? 'required' : 'nullable';
            $rules['psp_seller_flag'] = isset(request()->psp_seller_flag) ? 'required' : 'nullable';
            $rules['mcc'] = isset(request()->mcc) ? 'required' : 'nullable';
            $rules['iso_country_code'] = isset(request()->iso_country_code) ? 'required' : 'nullable';
            $rules['national_address_code'] = isset(request()->national_address_code) ? 'sometimes' : 'nullable';
        }
        if (BrandConfiguration::call([BackendAdmin::class, 'MerchantApplicationToCreatePosCommission'])) {
            $rules['branch_name']           = 'nullable|string|max:150';
            $rules['branch_code']           = 'nullable|string|max:100';
            $rules['app_entry_user_code']   = 'nullable|string|max:50';
            $rules['digital_slip_code']     = 'nullable|string|max:50';
            $rules['digital_slip_msg']      = 'nullable|string|max:200';
            $rules['customer_number']       = 'nullable|string|max:20';
        }
	    
	    if(BrandConfiguration::call([BackendAdmin::class, 'isValidatePhoneNoOnMerchantAdd'])){
			
		    $rules['authorizedPersonPhoneNumber_'] = 'required|digits_between:' .
			    Merchant::PHONE_MIN_MAX_LENGTH_AFTER_COUNTRY_CODE .',' . Merchant::PHONE_MIN_MAX_LENGTH_AFTER_COUNTRY_CODE;
			
		    $rules['contact_person_phone_'] = 'sometimes|digits_between:' .
			    Merchant::PHONE_MIN_MAX_LENGTH_AFTER_COUNTRY_CODE .',' . Merchant::PHONE_MIN_MAX_LENGTH_AFTER_COUNTRY_CODE;
			
	    }
		

        if ( BrandConfiguration::call([FrontendAdmin::class, 'allowVKNFieldForMerchantIndividualType']) && (isset(request()->merchant_type) && request()->merchant_type == Merchant::INDIVIDUAL_MERCHANT_TYPE) ) {
            $rules['vkn'] = ['required'];
        }

        return $rules;
    }

    public static function getMerchantExtraDocumentRules($required_type){

       return [
         'signature' => $required_type,
         'tax_board' => $required_type,
         'trade_registry' => $required_type,
         'partner_identity' => $required_type,
       ];
    }

    public function merchant_validation_message(){
        $message = [
            'authorized_person_email.unique_email' => __('Email must be unique.'),
            'tax_no.same' => __('Tax number must be same as VKN.'),
        ];
        if(BrandConfiguration::allowMerchantNameInEnglishOnly()){
            $message = $message + [
                'merchant_name.regex' => __('Merchant name should include only english characters.'),
                'merchant_name.max' => __('Merchant name may not be greater than :max characters.'),
            ];
        }

        if(BrandConfiguration::allowVKNunique()){
            $message['vkn.unique'] = __('The vkn has already been taken.');
            $message['vkn.required'] = __('The vkn field is required.');
            $message['tax_no.unique'] = __('The Tax has already been taken.');
        }
	    
	    
	    if(BrandConfiguration::call([BackendAdmin::class, 'isValidatePhoneNoOnMerchantAdd'])){
			
		    $message['authorizedPersonPhoneNumber_.digits_between']  = __('Authorized phone number should not be more or less than :min_max_length digit after country code.', ['min_max_length' => Merchant::PHONE_MIN_MAX_LENGTH_AFTER_COUNTRY_CODE]);
			
		    $message['contact_person_phone_.digits_between'] = __('Contact Person phone number should not be more or less than :min_max_length digit after country code.', ['min_max_length' => Merchant::PHONE_MIN_MAX_LENGTH_AFTER_COUNTRY_CODE]);
	    }
		
        return $message;
    }

   public function merchant_validation_update_rules($request)
   {
      $customMessages = [];
      $allow_merchant_name_in_en = BrandConfiguration::allowMerchantNameInEnglishOnly();
      $merchant_name_rules = $allow_merchant_name_in_en ? '|max:24|'.AppRequestValidation::ruleAlphaNumericSpaces()['rule']:'|string|max:191';
      $rules = [
        'merchantName' => 'required'.$merchant_name_rules,
        'authorizedPersonName' => 'required|string|max:191',
        'fullCompanyName' => 'required|string|max:191',
        'authorizedPersonPhoneNumber' => 'required|string|max:191|unique_phone:'.User::MERCHANT.','.$request['user_id'],
         // 'website' => 'required|string|max:191',
        'authorizedPersonE_Mail' => 'required|email|max:191|unique_email:'.User::MERCHANT.','.$request['user_id']
      ];

      if($allow_merchant_name_in_en){
          $customMessages = [
              'merchantName.regex' => __('Merchant name should include only english characters.'),
              'merchantName.max' => __('Merchant name may not be greater than :max characters.'),
          ];
      }

      if(BrandConfiguration::isValidatePhoneNumberLength()){
         $rules = $rules + [
             'authorizedPersonPhoneNumber_' => 'required|digits_between:' . Merchant::PHONE_MIN_MAX_LENGTH_AFTER_COUNTRY_CODE .',' . Merchant::PHONE_MIN_MAX_LENGTH_AFTER_COUNTRY_CODE,
             'contactPersonPhoneNumber_' => 'sometimes|digits_between:' . Merchant::PHONE_MIN_MAX_LENGTH_AFTER_COUNTRY_CODE .',' . Merchant::PHONE_MIN_MAX_LENGTH_AFTER_COUNTRY_CODE,
           ];

         $customMessages = $customMessages + [
           'authorizedPersonPhoneNumber_.digits_between' => __('Authorized phone number should not be more or less than :min_max_length digit after country code.', ['min_max_length' => Merchant::PHONE_MIN_MAX_LENGTH_AFTER_COUNTRY_CODE]),
           'contactPersonPhoneNumber_.digits_between' => __('Contact Person phone number should not be more or less than :min_max_length digit after country code.', ['min_max_length' => Merchant::PHONE_MIN_MAX_LENGTH_AFTER_COUNTRY_CODE])
         ];

      }

      if (BrandConfiguration::call([Mix::class, 'isAllowedMainAndSubDealerIntegration'])) {
          $rules = $rules + [
              'dealer_type' => 'sometimes|integer',
              'main_dealer_id' => 'integer|required_if:dealer_type,'. Merchant::SUB_DEALER_MERCHANT
          ];

          $customMessages = $customMessages + ['main_dealer_id.required_if' => 'Main Dealer Merchant is required when Dealer Type is Sub Dealer'];
      }
       if (BrandConfiguration::call([Mix::class, 'isAllowSubPaymentIntegrationOption'])) {
           $rules = $rules + [
                   'sub_payment_integration_option' => 'required|integer',
               ];
       }

      return [$rules, $customMessages];
   }

    // check tckn/vkn in blacklist
    public function checkTcknVknInBlacklist($tckn, $vkn){

       if(BrandConfiguration::tcknVknBlacklist()){
          $tcknVknBlacklist = new MerchantTcknVknBlacklist();
          $tckn_vkn_blacklist = $tcknVknBlacklist->getMerchantTcknVkn($tckn, $vkn);
          if(count($tckn_vkn_blacklist) > 0){
             $this->exists = true;
             $this->exists_message = __('This tckn or vkn is in blacklisted');
          }
       }

        if(BrandConfiguration::call([BackendMix::class, 'isAllowedCheckGlobalMerchantStatus'])){
           $tckn_vkn_blacklist = (new MerchantStatus())->getBlackListedMerchantTcknVkn($tckn, $vkn);
           if(Arr::count($tckn_vkn_blacklist) > 0){
               $this->exists = true;
               $this->exists_message = __('This tckn or vkn is in blacklisted');
           }
       }

    }

    private static function brandRand()
    {
        $brand_code = config('brand.name_code');
        $rand = mt_rand(10000 , 99999);

        if($brand_code ==  (config('constants.BRAND_NAME_CODE_LIST.SP') || config('constants.BRAND_NAME_CODE_LIST.PIN'))){
            $rand = mt_rand(10000000, 99999999);
        }

        return $rand;
    }



    public static function generateRandomId() {
       $id = self::brandRand();

       $valid = false;

        while (!$valid) {
            //dd($id, Number::isArithmeticSequence($id), Number::isNextDigitASubsequentDigit($id), Number::isNextDigitSame($id));
            if (! Number::isArithmeticSequence($id) && ! Number::isNextDigitASubsequentDigit($id) && ! Number::isNextDigitSame($id)) {
                $valid = true;
            }else{
                $id = self::brandRand();
            }
        }

        if (self::isIdExists($id)) {
            return self::generateRandomId();
        }

        return $id;
    }

    public static function isIdExists($id) {
        return Merchant::where('id',$id)->exists();
    }

    public function getMerchantsData($search, $page_limit = false, $is_export = false)
    {
        $query = Merchant::query();
        $relations = [
            'user',
            'currency',
            'user.company',
            'wallets',
            'account_managers'
        ];

        if($is_export){
            // $relations = array_merge($relations, ['merchant_comissions']);
            $relations = [
                'wallets',
                'merchant_comissions',
                'merchant_payment_methods',
                'merchant_payment_methods.payment_rec_option',
                'merchant_setting',
                'account_managers',
                'user.company',
            ];

        }

        $query = $query->with($relations)
            ->join('users', 'users.id', '=', 'merchants.user_id')
            ->join('companies', 'companies.id', '=', 'users.company_id')
            ->leftjoin('merchant_integrators','merchant_integrators.merchant_id','=','merchants.id')
            ->leftjoin('integrators','integrators.id','=','merchant_integrators.integrator_id');

            if(BrandConfiguration::call([Mix::class, 'isAllowSerialNumberInMerchantSearch']) && !empty($search['serial_no'])){
                $query->leftjoin('merchant_terminals','merchants.id','=','merchant_terminals.merchant_id');
                $query->where('merchant_terminals.serial_no', $search['serial_no']);
            }

        $query->where(function ($query) use ($search) {

                // Merchant ID
                if (isset($search['merchantid']) && !empty($search['merchantid'])) {
                    if (is_array($search['merchantid'])) {
	                    $query->whereIn('merchants.id',$search['merchantid']);
                    } else {
                        $query->where('merchants.id' , $search['merchantid']);
                    }
                }

                // Currency
                if (isset($search['currency_id'])
                    && !empty($search['currency_id'])
                    && !isset($search['search'])) {
                    $query->where('merchants.currency_id' ,$search['currency_id']);
                }

                //account manager
                if (isset($search['account_manager']) && !empty($search['account_manager'])) {
                    $query->WhereHas('account_managers', function ($q) use($search) {
                        $q->where('name','like','%' . $search['account_manager'] . '%' );
                    });
                }

                //merchant name
                if (isset($search['name'])
                    && !empty($search['name'])) {
                    $query->where('merchants.name' , 'like' ,'%' . $search['name'] . '%');
                }

                //merchant company
                if (isset($search['full_company_name']) && !empty($search['full_company_name']) ) {
                    $query->where('merchants.full_company_name' , 'like' ,'%' . $search['full_company_name'] . '%');
                }

                // Status
                if (isset($search['status'])
                    && $search['status'] !== ''
                    && !isset($search['search'])) {
                    if ($search['status'] == 0) {
                        $query->Where(function ($q) use($search) {
                            $q->where('merchants.status', $search['status'])
                                ->orWhere('companies.status', $search['status']);
                        });
                    } else {
                        $query->Where(function ($q) use($search) {
                            $q->where('merchants.status', $search['status'] )
                                ->where('companies.status', $search['status']);
                        });
                    }
                } else if (isset($search['status']) && $search['status'] !== '') {
                    $query->where('merchants.status', $search['status']);
                }

                // Tenant Approval Status
                if (isset($search['tenant_approval_status']) && $search['tenant_approval_status'] != "") {
                    $query->where('merchants.tenant_approval_status', $search['tenant_approval_status']);
                }

                //Is Block
                if (isset($search['is_block']) && !empty($search['is_block'])) {
                    $query->where('merchants.is_block', $search['is_block']);
                }

                if (isset($search['type']) && !empty($search['type'])) {

                    if(is_array($search['type'])){
                        $query->whereIn('type',$search['type']);
                    }else{
                        $query->where('type',$search['type']);
                    }
                }
                //site_url
                if (isset($search['site_url'])
                    && !empty($search['site_url']))
                {
                    $query->where('merchants.site_url', 'like', '%' . $search['site_url'] . '%');
                }
                //tax_number
                if (isset($search['tax_number'])
                    && !empty($search['tax_number']))
                {
                    $query->where('merchants.vkn', 'like', '%' . $search['tax_number'] . '%');
                }
                //auth_person_name
                if (isset($search['auth_person_name'])
                    && !empty($search['auth_person_name']))
                {
                    $query->where('merchants.authorized_person_name', 'like', '%' . $search['auth_person_name'] . '%');
                }

                //auth_person_phone
                if (isset($search['auth_person_phone'])
                    && !empty($search['auth_person_phone']))
                {

                    $query->where('merchants.authorized_person_phone_number', 'like', '%' . $search['auth_person_phone'] . '%');
                }
                //zip_code
                if (isset($search['zip_code'])
                    && !empty($search['zip_code']))
                {
                    $query->where('merchants.zip_code', 'like', '%' . $search['zip_code'] . '%');
                }
                //authorized_person_email
                if (isset($search['auth_person_email'])
                    && !empty($search['auth_person_email']))
                {
                    $query->where('merchants.authorized_person_email', 'like', '%' . $search['auth_person_email'] . '%');
                }
                //mcc
                if (isset($search['mcc'])
                    && !empty($search['mcc'])
                    && !isset($search['search']))
                {
                    $query->where('merchants.mcc', 'like', '%' . $search['mcc'] . '%');
                }
                // MERCHANT INTEGETOR
                if (isset($search['merchant_integrator']) && !empty($search['merchant_integrator']) && Arr::isOfType($search['merchant_integrator'])) {
                    $query->whereIn('merchant_integrators.integrator_id',$search['merchant_integrator']);
                } else if (! empty($search['integrator_id'])) {
                    $query->where('merchant_integrators.integrator_id', $search['integrator_id']);
                }

                // account manager
                if (isset($search['account_manager_ids']) && !empty($search['account_manager_ids'])) {
                    $query->WhereHas('account_managers', function ($q) use ($search) {
                        if (is_array($search['account_manager_ids'])) {
                            $q->whereIn('account_manager.user_id', $search['account_manager_ids']);
                        } else {
                            $q->where('account_manager.user_id', $search['account_manager_ids']);
                        }
                    });
                }

                //daterange
                if (isset($search['daterange'])
                    && !empty($search['daterange'])
                    && !isset($search['search']))
                {
                    $search['from_date'] = ManipulateDate::startOfTheDay($search['from_date']);
                    $search['to_date'] = ManipulateDate::endOfTheDay($search['to_date']);
                    $query->whereBetween('merchants.activation_date',[$search['from_date'] , $search['to_date']]);
                }

                // Search text
                if (isset($search['search']) && !empty($search['search'])) {
                    if (isset($search['status']) && $search['status'] !== '') {
                        if ($search['status'] == 1) {
                            if (isset($search['currency_id']) && !empty($search['currency_id'])) {
                                $query->Where(function ($q) use($search) {
                                    $q->where(function ($q) use($search){
                                        $q->where('merchants.name','like','%' . $search['search'] . '%' )
                                            ->orWhere('merchants.id', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.tckn', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.vkn', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.tax_no', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.name', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.full_company_name', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('users.name', 'like' , '%' . $search['search'] . '%');
                                    })->where('merchants.status', $search['status'])
                                    ->where('companies.status', $search['status'])
                                    ->where('merchants.currency_id', $search['currency_id']);
                                });// merchants.merchant_key and users.email was removed from search query
                            } else {
                                $query->Where(function ($q) use($search) {
                                    $q->where(function ($q) use($search){
                                        $q->where('merchants.name','like','%' . $search['search'] . '%' )
                                            ->orWhere('merchants.id', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.tckn', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.vkn', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.tax_no', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.name', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.full_company_name', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('users.name', 'like' , '%' . $search['search'] . '%');
                                    })->where('merchants.status', $search['status'])
                                        ->where('companies.status', $search['status']);
                                });// merchants.merchant_key and users.email was removed from search query
                            }
                        } else {
                            if (isset($search['currency_id'])) {
                                $query->Where(function ($q) use($search) {
                                    $q->where(function ($q) use($search){
                                        $q->where('merchants.name','like','%' . $search['search'] . '%' )
                                            ->orWhere('merchants.id', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.tckn', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.vkn', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.tax_no', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.name', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.full_company_name', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('users.name', 'like' , '%' . $search['search'] . '%');
                                    })->where(function ($q) use($search){
                                        $q-> where('merchants.status', $search['status'])
                                            ->orWhere('companies.status', $search['status']);
                                    })->where('merchants.currency_id', $search['currency_id']);
                                });// merchants.merchant_key and users.email was removed from search query
                            } else {
                                $query->Where(function ($q) use($search) {
                                    $q->where(function ($q) use($search){
                                        $q->where('merchants.name','like','%' . $search['search'] . '%' )
                                            ->orWhere('merchants.id', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.tckn', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.vkn', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.tax_no', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.name', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('merchants.full_company_name', 'like' , '%' . $search['search'] . '%')
                                            ->orWhere('users.name', 'like' , '%' . $search['search'] . '%');
                                    })->where(function ($q) use($search){
                                        $q-> where('merchants.status', $search['status'])
                                            ->orWhere('companies.status', $search['status']);
                                    });
                                });// merchants.merchant_key and users.email was removed from search query
                            }
                        }
                    } else {
                        if (isset($search['currency_id']) && $search['status'] !== '') {
                            $query->Where(function ($q) use($search) {
                                $q->where(function ($q) use($search){
                                    $q->where('merchants.name','like','%' . $search['search'] . '%' )
                                        ->orWhere('merchants.id', 'like' , '%' . $search['search'] . '%')
                                        ->orWhere('merchants.tckn', 'like' , '%' . $search['search'] . '%')
                                        ->orWhere('merchants.vkn', 'like' , '%' . $search['search'] . '%')
                                        ->orWhere('merchants.tax_no', 'like' , '%' . $search['search'] . '%')
                                        ->orWhere('merchants.name', 'like' , '%' . $search['search'] . '%')
                                        ->orWhere('merchants.full_company_name', 'like' , '%' . $search['search'] . '%')
                                        ->orWhere('users.name', 'like' , '%' . $search['search'] . '%');
                                })->where('merchants.currency_id', $search['currency_id']);
                            });// merchants.merchant_key and users.email was removed from search query
                        } else {
                            $query->Where(function ($q) use($search) {
                                $q->where('merchants.name','like','%' . $search['search'] . '%' )
                                    ->orWhere('merchants.id', 'like' , '%' . $search['search'] . '%')
                                    ->orWhere('merchants.tckn', 'like' , '%' . $search['search'] . '%')
                                    ->orWhere('merchants.vkn', 'like' , '%' . $search['search'] . '%')
                                    ->orWhere('merchants.tax_no', 'like' , '%' . $search['search'] . '%')
                                    ->orWhere('merchants.name', 'like' , '%' . $search['search'] . '%')
                                    ->orWhere('merchants.full_company_name', 'like' , '%' . $search['search'] . '%')
                                    ->orWhere('users.name', 'like' , '%' . $search['search'] . '%');
                            });// merchants.merchant_key and users.email was removed from search query
                        }
                    }
                }
            });
            $query->select('merchants.*','integrators.integrator_name as integrator_name');
            if(BrandConfiguration::call([Mix::class, 'isAllowSerialNumberInMerchantSearch']) && !empty($search['serial_no'])){
                $query->addSelect('merchant_terminals.serial_no as serial_no');
                $query->distinct('merchants.id');
            }
            $query->orderBy('merchants.updated_at', 'desc');

       $query = $this->restrictFilterForMerchantAndUsers($query, 'merchants.id', $search['auth_user_id'] ?? '', $search['is_filter_by_user_id'] ?? false );

       if ($page_limit) {
            $result = $query->paginate($page_limit);
        } elseif(!empty($is_export)){
            $result = $query;
        }else{
            $result = $query->get();
        }

        return $result;
    }

    private function prepareCreditCardData($data)
    {
        $creditCard_data = [
            'merchant_commission' => $data['commission_percentage'] ?? 0,
            'merchant_commission_fixed' => $data['commission_fixed'] ?? 0,
            'user_commission' => $data['end_user_commission_percentage'] ?? 0,
            'user_commission_fixed' => $data['end_user_commission_fixed'] ?? 0,
            'is_enable_pay_by_token' => $data['is_enable_pay_by_token'] ?? 0,
            'merchant_block_amount' => $data['merchant_block_amount'] ?? 0,
            'credit_card_merchant_commission' => $data['credit_card_merchant_commission'] ?? 0,
            'credit_card_user_commission' => $data['credit_card_user_commission'] ?? 0,
            'credit_card_payment_settlement' => $data['settlement_type'] ?? null,
            'credit_card_single_payment_settlement' => $data['settlement_type'] ?? null,
            'is_foreign_card_commission_enable' => $data['is_foreign_card_commission_enable'] ?? 1,
            'credit_card_active_status' => $data['credit_card_active_status'] ?? 0,
            'currency' => $data['currency_id'] ?? Currency::TRY,
        ];
        return $creditCard_data;
    }

    private function prepareMerchantPosCommissionData($data)
    {
        $merchant_pos_commission['com_percentage'][$data['pos_id']][$data['installment']] = $data['commission_percentage'];
        $merchant_pos_commission['com_fixed'][$data['pos_id']][$data['installment']] = $data['commission_fixed'];
        $merchant_pos_commission['end_user_com_percentage'][$data['pos_id']][$data['installment']] = $data['end_user_commission_percentage'];
        $merchant_pos_commission['end_user_com_fixed'][$data['pos_id']][$data['installment']] = $data['end_user_commission_fixed'];
        $merchant_pos_commission['is_allow_foreign_card'] = $data['is_allow_foreign_card'];
        return $merchant_pos_commission;
    }

    private function prepareOtplData($data)
    {
        $otpl_data['currency_id'] = $data['currency_id'] ?? Currency::TRY;
        $otpl_data['max_amount'] = $data['pos_transaction_limit'] ?? 0;
        $otpl_data['min_amount'] = $data['min_amount'] ?? 0;
        $otpl_data['status'] = Otpl::ACTIVE;
        return $otpl_data;
    }

    private function prepareMerchantIntegratorData($data)
    {
        $mi_data["integrator_id"] = $data['integrator_id'];
        $mi_data["commission_percentage"] = $data['commission_percentage'] ?? 0;
        $mi_data["commission_fixed"] = $data['commission_fixed'];
        return $mi_data;
    }

    public static function checkMerchantsActiveStatus ($request)
    {
        $merchatObj = (new Merchant())->getMerchantById($request->merchant_id_maker_checker);
        if (!empty($merchatObj)) {
            if (Route::currentRouteName() == config('constants.defines.APP_MERCHANTS_INFORMATION_EDIT')
                && isset($request->status) && $request->status == Merchant::MERCHANT_ACTIVE) {
                $response = true;
            } elseif ($merchatObj->status == Merchant::MERCHANT_ACTIVE) {
                $response = true;
            } else {
                $response = false;
            }
        } else {
            $response = false;
        }
        return $response;
    }

    public function getSettlementData($transactions, $available_balances, $with_date = true)
    {

        $formattedData = [];

        if (count($transactions) > 0) {

            $sale_ids = $transactions->where('is_installment_wise_settlement', MerchantSettings::IS_INSTALLMENT_WISE_SETTLEMENT)->pluck('sale_id')->toArray();
            $saleSettlements = collect();
            if (!empty($sale_ids)) {
                $saleSettlements = (new SaleSettlement())->getBySaleId($sale_ids);
            }

            foreach ($transactions as $transaction) {

                if (isset($available_balances[$transaction->currency_id])) {
                    list($total_completed_transaction, $total_refunded_transaction, $total_chargeback_transaction) = $this->countTransaction($transaction);
                    $formattedData = $this->formattedData($formattedData ,$transaction, $available_balances, $with_date , $saleSettlements , $total_completed_transaction, $total_refunded_transaction, $total_chargeback_transaction);
                }
            }
        }
        return $formattedData;

   }

    public function countTransaction($transaction): array
    {

        $total_completed_transaction = $total_refunded_transaction = $total_chargeback_transaction = 0;

        if ($transaction->transaction_state_id == TransactionState::COMPLETED) {
            $total_completed_transaction = 1;
        } elseif ($transaction->transaction_state_id == TransactionState::CHARGE_BACKED) {
            $total_completed_transaction = $total_chargeback_transaction = 1;
        } elseif (($transaction->transaction_state_id == TransactionState::REFUNDED) || ($transaction->transaction_state_id == TransactionState::PARTIAL_REFUND)) {
            $refundRequestDate = Carbon::parse($transaction->refund_request_date)->format('Y-m-d');
            $createdAt = Carbon::parse($transaction->created_at)->format('Y-m-d');
            if (Carbon::parse($refundRequestDate)->equalTo($createdAt)) {
                $total_completed_transaction = $total_refunded_transaction = 1;
            } else {
                $total_completed_transaction = $total_refunded_transaction = 1;
            }
        }
        return [$total_completed_transaction,$total_refunded_transaction,$total_chargeback_transaction];
    }

    public function formattedData($formattedData , $transaction, $available_balances, $with_date, $saleSettlements,
                                  $total_completed_transaction, $total_refunded_transaction, $total_chargeback_transaction)
    {

        $is_installment_wise_settlement = $transaction->is_installment_wise_settlement ?? 0;

        if ($is_installment_wise_settlement) {
            $saleSettlementCollection = (new SaleSettlement())->filterBySingleSale($transaction, $saleSettlements, $with_date);

            foreach ($saleSettlementCollection as $saleSettlement) {

                $merchant_settlement_date = Carbon::parse($saleSettlement->settlement_date_merchant)->format('Y-m-d');
                $amount = $saleSettlement->net_settlement - $saleSettlement->refunded_amount - $saleSettlement->refund_request_amount;

                if (($with_date == false && isset($formattedData[$transaction->currency_id])) || ($with_date == true && isset($formattedData[$merchant_settlement_date][$transaction->currency_id]))) {

                    $formattedData = $this->updateExisting($formattedData, $merchant_settlement_date, $transaction,
                        $amount, $saleSettlement->gross, $saleSettlement->merchant_commission,
                        $total_completed_transaction, $total_refunded_transaction, $total_chargeback_transaction, $with_date);

                } else {

                    $formattedData = $this->newEntry($available_balances, $formattedData, $merchant_settlement_date, $transaction,
                        $amount, $saleSettlement->gross, $saleSettlement->merchant_commission,
                        $total_completed_transaction, $total_refunded_transaction, $total_chargeback_transaction, $with_date);

                }
            }
        } else {

            $merchant_settlement_date = Carbon::parse($transaction->effective_date)->format('Y-m-d');
            $amount = $transaction->amount - $transaction->refund_request_amount;

            if (($with_date == false && isset($formattedData[$transaction->currency_id])) || ($with_date == true && isset($formattedData[$merchant_settlement_date][$transaction->currency_id]))) {

                $formattedData = $this->updateExisting($formattedData, $merchant_settlement_date, $transaction,
                    $amount, $transaction->gross, $transaction->merchant_commission,
                    $total_completed_transaction, $total_refunded_transaction, $total_chargeback_transaction, $with_date);

            } else {

                $formattedData = $this->newEntry($available_balances, $formattedData, $merchant_settlement_date, $transaction,
                    $amount, $transaction->gross, $transaction->merchant_commission,
                    $total_completed_transaction, $total_refunded_transaction, $total_chargeback_transaction, $with_date);

            }
        }

        return $formattedData;
   }

   public function prepareSettlementFilterData($request, $from_admin = false){
       $this->settle_early_pre_data = [];
       $current_month = $settlement_month = ManipulateDate::getSystemDateTime('Y-m');

       if (isset($request->settlement_month)) {
           $settlement_month = ManipulateDate::getDateFormat($request->settlement_month, 'Y-m');

           if (ManipulateDate::isGreaterThanOrEqualToMonths($current_month, $settlement_month)) {
               $current_month = ManipulateDate::getDateFormat($settlement_month, 'Y-m-01');
           } else {
               $current_month = ManipulateDate::getSystemDateTime('Y-m-d');
           }
       }

       if ($from_admin) {
           $search['merchant_id'] = Number::toInt($request->merchant_id ?? 0);
       } else {
           $search['merchant_id'] = Number::toInt(Auth::user()->merchants->id ?? 0);
       }

       if (isset($request->daterange)
           && !empty($request->daterange)
           && BrandConfiguration::call([Mix::class, 'enableDateRangeForSettlementCalendar'])
       ) {
           $date = explode('-', $request->daterange);
           $search['from_date'] = ManipulateDate::startOfTheDay(trim($date[0]), 'Y-m-d H:i:s');
           $search['to_date'] = ManipulateDate::endOfTheDay(trim($date[1]), 'Y-m-d H:i:s');
       }else{
           $search['from_date'] = ManipulateDate::startOfTheDay($current_month, 'Y-m-d H:i:s');
           $search['to_date'] = ManipulateDate::endOfTheDay($settlement_month, 'Y-m-t H:i:s');
       }

       $search['settlement_month'] = $settlement_month;

       return $search;
   }

   public function resetCalendarExportDateRange($search): array
   {
       $search['from_date'] = ManipulateDate::startOfTheMonth(null,'Y/m/d');
       $search['to_date'] = ManipulateDate::endOfTheMonth(null,'Y/m/d');
       $search['daterange'] = $search['from_date'] . " - " . $search['to_date'];

       return $search;
   }

    public function settlementReportExport($request, $search)
    {

        $type = $request->file_type ?? MerchantReportHistory::FORMAT_XLS;
        $extension = MerchantReportHistory::FORMAT_LIST[$type];

        $filename = 'Settelment Report of '.$request->settlement_month;
        $heading = $this->getMerchantSettlementReportsHeading();
        $reportDatas = $this->getMerchantSettlementReportsArrayGenerator($search);

        if (!empty($reportDatas)) {
            return (new File())->fileExport($extension, collect($reportDatas), $filename, $heading);
        } else {
            flash(__('No data found'));
            return redirect()->back();
        }
    }

   public function getByMerchantIdEffectiveDate($search)
   {

      if(empty($search['is_export'])){
          $query = Merchant::query()
                  ->with('wallets');
          $merchant_id_selector = 'merchants.id';

          if (SqlBuilder::isMysql()) {
              $query->leftJoin(DB::raw('merchant_sales FORCE INDEX FOR JOIN (idx_effective_date_int)'), 'merchant_sales.merchant_id', '=', 'merchants.id');
          } else {
              $query->leftJoin('merchant_sales', 'merchant_sales.merchant_id', '=', 'merchants.id');
          }
      }else{
          $query = MerchantSale::query()
                   ->with('refundHistory');
          $merchant_id_selector = 'merchant_sales.merchant_id';

          if (SqlBuilder::isMysql()) {
              $query->from(DB::raw('merchant_sales FORCE INDEX (idx_effective_date_int)'));
          }
      }

       $query->join('sales', 'sales.id', '=',  'merchant_sales.sale_id');

       //// force index on multiple join not working as expected
       // if (SqlBuilder::isMysql()){
       //    $query->leftJoin(DB::raw('sales_settlements FORCE INDEX FOR JOIN (idx_settlement_date_merchant_int)'), 'sales_settlements.sale_id', '=', 'merchant_sales.sale_id');
       // } else {
       $query->leftJoin('sales_settlements', 'sales_settlements.sale_id', '=', 'merchant_sales.sale_id');
       // }

      if (isset($search['merchant_id']) && !empty($search['merchant_id'])) {
         if (is_array($search['merchant_id'])) {
            $query->whereIn($merchant_id_selector, $search['merchant_id']);
         } else {
            $query->where($merchant_id_selector, $search['merchant_id']);
         }
      }
      $query->whereIn('sales.transaction_state_id', [
        TransactionState::COMPLETED,
        TransactionState::REFUNDED,
        TransactionState::PARTIAL_REFUND,
        TransactionState::CHARGE_BACKED,
        TransactionState::PARTIAL_CHARGEBACK
      ]);

       if (isset($search['from_date']) && isset($search['to_date'])) {
           $date_int_array = $this->convertToDateInt($search);

           if (!empty($search['settle_early']) && $search['settle_early']) {
               $query->where(function ($q) use ($search, $date_int_array) {
                   $q->where(function ($qry) use ($search, $date_int_array) {
                       $qry->where('sales.is_installment_wise_settlement', MerchantSettings::IS_INSTALLMENT_WISE_SETTLEMENT)
                           ->where('sales_settlements.settlement_date_merchant_int', $date_int_array['from_date']);
                   })->orWhere(function ($qry) use ($search, $date_int_array) {
                       $qry->where('sales.is_installment_wise_settlement', MerchantSettings::NOT_IS_INSTALLMENT_WISE_SETTLEMENT)
                           ->where('merchant_sales.effective_date_int', $date_int_array['from_date']);
                   });
               });
           } else {
               $query->where(function ($q) use ($search, $date_int_array) {
                   $q->whereBetween('merchant_sales.effective_date_int', [$date_int_array['from_date'], $date_int_array['to_date']])
                       ->orWhereBetween('sales_settlements.settlement_date_merchant_int', [$date_int_array['from_date'], $date_int_array['to_date']]);
               });
           }
       }

      if (isset($search['currency_id']) && !empty($search['currency_id'])) {
        $query->where('sales.currency_id', $search['currency_id']);
      }

      if (!empty($search['settle_early']) && $search['settle_early']) {
          $query->where(function ($q) use ($search) {
              $q->where('merchant_sales.reference_unique_id', MerchantSale::REF_UNIQUE_ID_INIT)
                  ->orWhere('sales_settlements.reference_unique_id', MerchantSale::REF_UNIQUE_ID_INIT);
          });
      }

      $query->select(
        'merchant_sales.id as merchant_sale_id',
        'merchant_sales.merchant_id',
        'merchant_sales.sale_id',
        'merchant_sales.amount',
        'merchant_sales.refund_request_amount',
        'merchant_sales.currency_id',
        'merchant_sales.status',
        'merchant_sales.merchant_commission_percentage',
        'merchant_sales.merchant_commission_fixed',
        'sales.id',
        'sales.merchant_name as name',
        'sales.user_id',
        'sales.payment_id',
        'sales.invoice_id',
        'sales.order_id',
        'sales.transaction_state_id',
        'sales.gross',
        'sales.net',
        'sales.product_price',
        'sales.merchant_commission',
        'sales.currency_symbol',
        'sales.installment as sales_installment',
        'sales.total_refunded_amount',
        'sales.refund_request_date',
        'sales.rolling_amount',
        'sales.created_at',
        'sales.updated_at',
        'sales.is_installment_wise_settlement',
        'sales_settlements.id as sale_settlement_id',
        'sales_settlements.net_settlement',
        'sales_settlements.refunded_amount',
        'sales_settlements.gross as sale_settlement_gross',
        'sales_settlements.merchant_commission as sale_settlement_merchant_commission',
        'sales_settlements.is_fully_refunded as is_sale_settlement_fully_refunded',
        'sales_settlements.status as sale_settlement_status',
        'sales_settlements.installments_number',
        'sales_settlements.settled_amount as sale_settlement_settled_amount'
      );

      $query->selectRaw('DATE(merchant_sales.effective_date) as effective_date')
          ->selectRaw('DATE(sales_settlements.settlement_date_merchant) as settlement_date_merchant');

      $query->orderBy('merchant_sales.effective_date_int', 'ASC');

       if(!empty($search['is_chunk_limit'])
           &&
           !empty($search['chunk_callback'])
           &&
           Functionality::isCallableFunction($search['chunk_callback'])
       ) {
           $query->chunk($search['is_chunk_limit'], $search['chunk_callback']);
       }

       if (!empty($search['pagination']) && !empty($search['page_limit'])) {
           $result =  $query->paginate($search['page_limit'])->withQueryString();
       } else {
           $result = $query->get();
       }
      return $result;

   }

   public function convertToDateInt ($data): array
   {
       $result = [];

       if (!empty($data['from_date'])) {
           $result['from_date'] = ManipulateDate::getDateFormat($data['from_date'], ManipulateDate::FORMAT_DATE_Ymd);
       }
       if (!empty($data['to_date'])) {
           $result['to_date'] = ManipulateDate::getDateFormat($data['to_date'], ManipulateDate::FORMAT_DATE_Ymd);
       }

       return $result;
   }

   public function getMerchantSettlementReportsHeading()
   {
       if (BrandConfiguration::call([BackendMix::class, 'customizeMerchantSettlementReport'])) {
           $headings = [
               __('Merchant Name'),
               __('Total Amount'),
               __('Net Amount'),
               __('Total Commission Amount'),
               __('Settlement Payment Date'),
               __('Settlement Amount')
           ];
       }
       else if(BrandConfiguration::call([Mix::class, 'allowReportHistoriesSettlementCalendar'])){
           $headings = [
               __('Merchant ID'),
               __('Merchant Name'),
               __('Transaction Id'),
               __('Status'),
               __('Installment Number'),
               __('Total Installment Count'),
               __('Transaction Amount'),
               __('Commission'),
               __('Settlement Amount'),
               __('Settlement Date'),
               __('Transaction Date'),
               __('Process Date & Time'),
           ];
       }else{
           $headings = [
               __('Merchant ID'),
               __('Merchant Name'),
               __('Settlement Amount'),
               __('Settlement Date'),
               __('Total Amount'),
               __('Net Amount'),
               __('Commission'),
           ];
       }

      return $headings;
   }

   public function getMerchantSettlementReportsArrayGenerator($search)
   {
       $merchant_list = [];
       $available_balances = [];
       $settlement_data = [];
       $rows = [];
       $transaction_list = $this->getByMerchantIdEffectiveDate($search);

       if (!empty($transaction_list) && count($transaction_list) > 0) {
           $merchant_list = $transaction_list->groupBy(['merchant_id']);
       }

       if (!empty($merchant_list) && count($merchant_list) > 0) {
           foreach ($merchant_list as $merchant_id => $transaction_data) {
               $transaction_first_data = $transaction_data->first();
               $wallets = $transaction_first_data->wallets ?? null;

               if (!empty($wallets) && count($wallets) > 0) {
                   foreach ($wallets as $wallet) {
                       $available_balances[$merchant_id][$wallet->currency_id] = $wallet->withdrawable_amount;
                   }
                   $settlement_data[$merchant_id] = [
                       'name' => $transaction_first_data->name ?? '',
                       'data' => $this->prepareSettlementCalendarData($transaction_data, $available_balances[$merchant_id], $search, true)
                   ];
               }

               // $settlement_data[$merchant_id] = $this->getSettlementData($transaction_data, $available_balances[$merchant_id], false);
           }
       }

       if (!empty($settlement_data) && count($settlement_data) > 0) {
           foreach ($settlement_data as $merchant_id => $single_data) {
               foreach ($single_data['data'] as $settlement_date => $formatted_data) {
                   $settlement_amount = $net_amount = $this->prepareAmountByCurrency($formatted_data, 'total_amount');
                   $commission_amount = $this->prepareAmountByCurrency($formatted_data, 'total_merchant_commission');
                   $gross_amount = $this->prepareAmountByCurrency($formatted_data, 'total_gross');

                   if (BrandConfiguration::call([BackendMix::class, 'customizeMerchantSettlementReport'])) {
                       $rows[] = [
                           $single_data['name'],
                           $gross_amount,
                           $net_amount,
                           $commission_amount,
                           ManipulateDate::getDateFormat($settlement_date, 'Y-m-d'),
                           $settlement_amount,
                       ];
                   }else{
                       $rows[] = [
                           $merchant_id,
                           $single_data['name'],
                           $settlement_amount,
                           ManipulateDate::getDateFormat($settlement_date, 'Y-m-d'),
                           $gross_amount,
                           $net_amount,
                           $commission_amount
                       ];
                   }
               }
           }
       }

       return $rows;
   }

   public function prepareAmountByCurrency ($balances, $key): string
   {
       $result = [];

       if (!empty($balances) && !empty($key)) {
//           $currencies = Currency::ALL_CURRENCY_LIST;
           $currencies = Currency::getCurrencies();
           ksort($balances);

           foreach ($balances as $currency_id => $data) {
               $array_keys = array_keys($data);

               if (in_array($key, $array_keys) && isset($currencies[$currency_id])) {
                   $result[] = $currencies[$currency_id][1] . ' ' . Number::format($data[$key], 2, '', '.', ',');
               }
           }
       }

       return implode(' | ', $result);
   }


    public static function isDuplicateInvoiceMerchant($merchant_id):bool
    {
        return in_array($merchant_id, (new MerchantConfiguration())->getMerchantListByEvent(MerchantConfiguration::EVENT_DUPLICATE_INVOICE));
/*        $is_duplicate_invoice_merchant = false;
        $live_merchant_list = [];
        $test_merchant_list = [98950];

        $brand_code = config('brand.name_code');
        $environment = config('constants.APP_ENVIRONMENT');
        switch ($brand_code) {
            case  config('constants.BRAND_NAME_CODE_LIST.SP'):
                switch ($environment) {
                    case "sp_prod":
                        $live_merchant_list = [25522, 98950, 95625, 52020, 19267, 46050];
                        $is_duplicate_invoice_merchant =  in_array($merchant_id, $live_merchant_list);
                        break;
                    default:
                        $is_duplicate_invoice_merchant =  in_array($merchant_id, $test_merchant_list);
                        break;
                }
                break;
            default:
                $is_duplicate_invoice_merchant = in_array($merchant_id, $test_merchant_list);
                break;
        }

        return $is_duplicate_invoice_merchant;*/
    }

   public function prepareAmount($data, $amount_type)
   {

      $amount = '';
      $merchant_name = '';

      if (count($data) > 0 && !empty($amount_type)) {
         foreach ($data as $currency_amount) {

            $merchant_name = $currency_amount['merchant_name'] ?? '';

            $current_ammount = $currency_amount['currency_symbol'] . ' ' . number_format($currency_amount[$amount_type], 2);
            if (!empty($amount)) {
               $amount .= " | " . $current_ammount;
            } else {
               $amount = $current_ammount;
            }

         }
      }


      return [$merchant_name, $amount];
   }

   public function b2c_approve($request, $auth)
   {

      $sipay_bank_account_id = $request->bank_static_id;

      /**
       * maker checker specific part
       */

      list($amc_response, $amc_message) = (new AdminMakerChecker())->processMakerChecker(
        AdminMakerChecker::MERCHANT_B2C, AdminMakerChecker::ACTION_CREATE, $request, $auth
      );

      if (!$amc_response) {

         $btoc = new BtoC();
         $statusCode = $btoc->approve($request->btocid, $sipay_bank_account_id, $auth->id);

         list($statusCode, $statusMessage) = $this->b2c_approve_message_mapping($statusCode);

         $label = "danger";
         if ($statusCode == \config('constants.SUCCESS_CODE')) {
            $label = "success";
            AdminMakerChecker::setCheckerSession($request, $auth);
         }

      } else {

         $statusMessage = __($amc_message[0]);
         $label = $amc_message[1];

      }

      return [$statusMessage, $label];

   }

   public function b2c_approve_message_mapping($statusCode)
   {

      if ($statusCode == 100) {
         $statusMessage = __('B2C has been approved successfully');
      } else if ($statusCode == 1) {
         $statusMessage = __('B2C not found');
      } else if ($statusCode == 2) {
         $statusMessage = __('You can\'t update your own request!');
      } else if ($statusCode == 3) {
         $statusMessage = __('User not found');
      } else if ($statusCode == 4) {
         $statusMessage = __('Currency not found');
      } else if ($statusCode == 5) {
         $statusMessage = __(':company Bank Account not found', ['company' => config('brand.name')]);
      } else if ($statusCode == 6) {
         $statusMessage = __('CoT has not been setup for the selected bank');
      } else if ($statusCode == 8) {
         $statusMessage = __('Non verified user limit exceed');
      } else if ($statusCode == 9) {
         $statusMessage = __('Receiver not registered yet');
      } else if ($statusCode == ApiService::API_SERVICE_MERCHANT_B2B_AND_B2C_BLOCKED_DURING_SETTLEMENT_WITHDRAWAL_PROCESS) {
          $statusMessage = Language::isLocalize(ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_MERCHANT_B2B_AND_B2C_BLOCKED_DURING_SETTLEMENT_WITHDRAWAL_PROCESS], ['transactionType' => "B2C"], true);
      } else {
         $statusMessage = __('Unknown error');
      }

      return [$statusCode, $statusMessage];
   }

   public function b2c_reject($request, $auth)
   {

      /**
       * maker checker specific part
       */

      list($amc_response, $amc_message) = (new AdminMakerChecker())->processMakerChecker(
        AdminMakerChecker::MERCHANT_B2C, AdminMakerChecker::ACTION_CREATE, $request, $auth
      );

      if (!$amc_response) {

         $btoc = new BtoC();
         $status = $btoc->reject($request->btocid, $request->rejectreason);


         if ($status === true) {

            $label = "success";
            $statusMessage = __('B2C has been rejected successfully!');
            AdminMakerChecker::setCheckerSession($request, $auth);

         } else {
            $label = "danger";
            $statusMessage = __($status);
         }

      } else {

         $statusMessage = __($amc_message[0]);
         $label = $amc_message[1];

      }

      return [$statusMessage, $label];

   }

   public static function selectedMailReceiverEmail($merchant_auth_email,$receivers_email){

      $selectChecker = !empty($merchant_auth_email) ? [$merchant_auth_email]
        : ((isset($receivers_email) && !empty($receivers_email)) ? explode(',', $receivers_email) : []);

      return $selectChecker;
   }

    public static function getMerchantStatusDetails(){
        
        $status_details = [
            Merchant::MERCHANT_ACTIVE => __('Active'),
            Merchant::MERCHANT_INACTIVE => __('Passive'),
        ];
        if (BrandConfiguration::call([BackendMix::class, 'isAllowMerchantNewStatus'])){
            $status_details=$status_details+Merchant::getNewStatus();

        }
        return $status_details;
    }

    public static function getMerchantDeactiveStatusList()
    {
        $status_details = [
            Merchant::MERCHANT_INACTIVE => __('Inactive'),
        ];

        if (BrandConfiguration::call([BackendMix::class, 'isAllowMerchantNewStatus'])) {
            $status_details = $status_details + Merchant::getNewStatus();
        }

        return $status_details;
    }

   public function getRestrictedMerchantIdAndUserId($auth_user_id = null, $is_filter_by_user_id = false)
   {

      $ids = [];
      if (BrandConfiguration::allowRestrictFilterForMerchantAndUsers() && !empty($auth_user_id)) {

         $restrictedMerchant = $this->getRestictedMerchantIds($auth_user_id);

         $data = isset($restrictedMerchant[$auth_user_id]) ? $restrictedMerchant[$auth_user_id] : [];

         if (!empty($data) && count($data) > 0) {
            $ids = $is_filter_by_user_id ? array_keys($data) : array_values($data);
         }

      }

      return $ids;
   }

   public function restrictFilterForMerchantAndUsers($query, $table_column_name, $auth_user_id = null, $is_filter_by_user_id = false)
   {

      $ids = $this->getRestrictedMerchantIdAndUserId($auth_user_id, $is_filter_by_user_id);
      if (!empty($table_column_name) && !empty($ids) && count($ids) > 0) {
         $query->whereNotIn($table_column_name, $ids);
      }

      return $query;

   }

   public static function getRestictedMerchantIds($auth_user_id){

      $restrictedMerchant = [];

      $hiddenMerchants = (new UserHideMerchant())->findHiddenMerchantByUserId($auth_user_id);

      if(!empty($hiddenMerchants)){
         $hiddenMerchantList = json_decode($hiddenMerchants->data, true);
         $restrictedMerchant[$hiddenMerchants->user_id] = $hiddenMerchantList;
      }

      return $restrictedMerchant;

   }
   private function prepareMerchantData($data , $merchant_id)
    {

        $bank_data = [];
        foreach ($data as $datum){
            unset($datum['id']);
            unset($datum['created_at']);
            unset($datum['updated_at']);
            $datum['merchant_id']=$merchant_id;
            array_push($bank_data,$datum);
        }
        return $bank_data;
    }
    private function prepareSingleMerchantData($data , $merchant_id , $api_key = null, $api_secret = null)
    {
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);

        if(!empty($api_secret)){
             $data['app_secret'] = $api_secret;
        }

        if(!empty($api_key)){
             $data['app_id'] = $api_key;
        }

        $data['merchant_id'] = $merchant_id;
        return $data;
    }

    private function prepareMerchantWalletData($data, $user_id)
    {
        $merchant_data = [];
        foreach ($data as $datum) {
            unset($datum['id']);
            unset($datum['created_at']);
            unset($datum['updated_at']);
            $datum['user_id'] = $user_id;
            array_push($merchant_data, $datum);
        }
        return $merchant_data;
    }

    private function checkFillableValue($arrays, $fillable)
    {
        $data = [];
        foreach ($arrays as $array) {
            $data[] = Arr::filterByKeys($array,$fillable);
        }
        return $data;
    }


    public function insertMerchantSocialLink($request, $merchant_id)
    {

        if (BrandConfiguration::allowSomeDocumentsMerchantApplication() || (isset($request['insert_social_link']) && $request['insert_social_link'])) {

            $social_media = $this->prepareMerchantSocialLink($request);
            $all_merchant_links = (new MerchantSocialLink())->getAllSocialMediaRequestLinks();
            if (count($social_media) > 0) {
                $social_list_data = [];
                foreach ($social_media as $key => $social_link) {

                    $media = array_search($key, $all_merchant_links);
                    if(!empty($media)){
                        $social_list_data[$key] = [
                          'merchant_id' => $merchant_id,
                          'media' => $media,
                          'media_link' => $social_link,
                        ];
                    }

                }
                if (!empty($social_list_data)) {
                    (new MerchantSocialLink())->saveData($social_list_data);
                }
            }
        }

    }


    public function prepareMerchantSocialLink($request, $social_files = [])
    {
        $social_media = [];
        $all_merchant_links = (new MerchantSocialLink())->getAllSocialMediaRequestLinks();
        foreach ($all_merchant_links as $value) {
            $social_media[$value] = (isset($request->{$value}) && !empty($request->{$value})) ? $request->{$value} : ($social_files[$value] ?? '');
        }

        return $social_media;
    }


    public function createMerchantDocument($request, $merchant_id)
    {

        $merchant_document = new MerchantDocument();
        $upload_path = 'merchant/documents/' . $merchant_id;
        if (BrandConfiguration::allowSomeDocumentsMerchantApplication()) {

            $move_file = isset($request->approved_by_checker) ? true : false;
            $file_paths = $this->uploadMerchantExtraDocument($request, $upload_path, $move_file);

            $doc_data = [
              'merchant_id' => $merchant_id,
              'file_1_path' => $file_paths['tax_board'] ?? '',
              'file_2_path' => $file_paths['signature'] ?? '',
              'file_3_path' => $file_paths['trade_registry'] ?? '',
              'other_file_1_path' => $file_paths['partner_identity'] ?? '',
              'file_1_status' => 1,
              'file_2_status' => 1,
              'file_3_status' => 1,
              'other_file_1_status' => 1,
            ];
            $merchant_document->saveMerchantDocument($doc_data);

        } else {

            $doc_data = [
              'merchant_id' => $merchant_id,
              'file_1_status' => 0,
              'file_2_status' => 0,
              'file_3_status' => 0
            ];
            $merchant_document->saveMerchantDocument($doc_data);
        }


    }

    public function uploadMerchantExtraDocument($request, $upload_path, $move_file = false, $doc_files = [])
    {
        if($move_file){

            $file_paths = [
              'signature' => $this->moveResourceFile($request->signature, $upload_path, $upload_path . '/' . basename($request->signature)),
              'tax_board' => $this->moveResourceFile($request->tax_board, $upload_path, $upload_path . '/' . basename($request->tax_board)),
              'trade_registry' => $this->moveResourceFile($request->trade_registry, $upload_path, $upload_path . '/' . basename($request->trade_registry)),
              'partner_identity' => $this->moveResourceFile($request->partner_identity, $upload_path, $upload_path . '/' . basename($request->partner_identity)),
              'others_one' => $this->moveResourceFile($request->others_one, $upload_path, $upload_path . '/' . basename($request->others_one)),
              'others_two' => $this->moveResourceFile($request->others_two, $upload_path, $upload_path . '/' . basename($request->others_two)),
              'others_three' => $this->moveResourceFile($request->others_three, $upload_path, $upload_path . '/' . basename($request->others_three)),
              'price_offer' => $this->moveResourceFile($request->price_offer, $upload_path, $upload_path . '/' . basename($request->price_offer)),
              'agreements' => $this->moveResourceFile($request->agreements, $upload_path, $upload_path . '/' . basename($request->agreements)),
            ];

        }else{
            $file_paths = [
              'signature' => $request->hasFile('signature') ? $this->uploadFile($request->signature, $upload_path) : $doc_files['signature'] ?? '',
              'tax_board' => $request->hasFile('tax_board') ? $this->uploadFile($request->tax_board, $upload_path) : $doc_files['tax_board'] ?? '',
              'trade_registry' => $request->hasFile('trade_registry') ? $this->uploadFile($request->trade_registry, $upload_path) : $doc_files['trade_registry'] ?? '',
              'partner_identity' => $request->hasFile('partner_identity') ? $this->uploadFile($request->partner_identity, $upload_path) : $doc_files['partner_identity'] ?? '',
              'others_one' => $request->hasFile('others_one') ? $this->uploadFile($request->others_one, $upload_path) : $doc_files['others_one'] ?? '',
              'others_two' => $request->hasFile('others_two') ? $this->uploadFile($request->others_two, $upload_path) : $doc_files['others_two'] ?? '',
              'others_three' => $request->hasFile('others_three') ? $this->uploadFile($request->others_three, $upload_path) : $doc_files['others_three'] ?? '',
              'price_offer' => $request->hasFile('price_offer') ? $this->uploadFile($request->price_offer, $upload_path) : $doc_files['price_offer'] ?? '',
              'agreements' => $request->hasFile('agreements') ? $this->uploadFile($request->agreements, $upload_path) : $doc_files['agreements'] ?? '',
              'working_condition' => $request->working_condition?? '',
              'installment_count' => $request->installment_count?? '',
              'installment' => $request->installment ?? ''
            ];
        }


        return $file_paths;
    }

    public static function updateArrayKeyByBrand ($data)
    {
        $oldKeys = [];
        $needle = 'sipay';

        if (BrandConfiguration::changeBrandNameFromKey()) {
            $dataArray = is_array($data) ? $data : $data->toArray();
            if (array_key_exists("non_sipay_user_email", $dataArray)) {
                $oldKeys[] = 'non_sipay_user_email';
            }
            if (array_key_exists("non_sipay_user_phone", $dataArray)) {
                $oldKeys[] = 'non_sipay_user_phone';
            }
        }

        if (!empty($oldKeys)) {
            $brand_name = 'brand'; //mb_strtolower(\config('brand.name'));
            foreach ($oldKeys as $key => $value) {
                $haystack = mb_strtolower($value);
                if (strpos($haystack, $needle) !== false) {
                    $newKey = str_replace($needle, $brand_name, $haystack);
                    $data[$newKey] = $data[$value];
                    unset($data[$value]);
                }
            }
        }

        return $data;
    }

    public function updateExisting($formattedData, $merchant_settlement_date, $transaction,
                                   $amount, $gross, $merchant_commission, $total_completed_transaction,
                                   $total_refunded_transaction, $total_chargeback_transaction , $with_date): array
    {
       if($with_date) {
           $formattedData[$merchant_settlement_date][$transaction->currency_id]["available_balance"] += $amount;
           $formattedData[$merchant_settlement_date][$transaction->currency_id]["total_amount"] += $amount;
           $formattedData[$merchant_settlement_date][$transaction->currency_id]["completed_transactions"] += $total_completed_transaction;
           $formattedData[$merchant_settlement_date][$transaction->currency_id]["refunded_transactions"] += $total_refunded_transaction;
           $formattedData[$merchant_settlement_date][$transaction->currency_id]["chargebacked_transactions"] += $total_chargeback_transaction;
       }else{
           $formattedData[$transaction->currency_id]['merchant_name'] = $transaction['name'];
           $formattedData[$transaction->currency_id]["available_balance"] += $amount;
           $formattedData[$transaction->currency_id]["total_amount"] += $amount;
           $formattedData[$transaction->currency_id]["total_gross_amount"] += $gross;
           $formattedData[$transaction->currency_id]["merchant_commission"] += $merchant_commission;
           $formattedData[$transaction->currency_id]["completed_transactions"] += $total_completed_transaction;
           $formattedData[$transaction->currency_id]["refunded_transactions"] += $total_refunded_transaction;
           $formattedData[$transaction->currency_id]["chargebacked_transactions"] += $total_chargeback_transaction;

       }
       return $formattedData;
   }

    public function newEntry($available_balances, $formattedData, $merchant_settlement_date, $transaction,
                             $amount, $gross, $merchant_commission, $total_completed_transaction,
                             $total_refunded_transaction, $total_chargeback_transaction , $with_date) : array
    {
        if ($with_date) {
           $formattedData[$merchant_settlement_date][$transaction->currency_id] = [
               "available_balance" => $available_balances[$transaction->currency_id] + $amount,
               "total_amount" => $amount,
               "currency_symbol" => $transaction->currency_symbol,
               "completed_transactions" => $total_completed_transaction,
               "refunded_transactions" => $total_refunded_transaction,
               "chargebacked_transactions" => $total_chargeback_transaction,

           ];
       }else{
           $formattedData[$transaction->currency_id] = [
               "merchant_name" => $transaction['name'],
               "available_balance" => $available_balances[$transaction->currency_id] + $amount,
               "total_amount" => $amount,
               "total_gross_amount" => $gross,
               "merchant_commission" => $merchant_commission,
               "completed_transactions" => $total_completed_transaction,
               "refunded_transactions" => $total_refunded_transaction,
               "chargebacked_transactions" => $total_chargeback_transaction,
               "currency_symbol" => $transaction->currency_symbol
           ];
       }

       return $formattedData;

   }


    public static function getRequiredMailTypes ()
    {
        $required_mail_types = MerchantEmailReceiver::DEFAULT_USER_EMAIL;

        if (BrandConfiguration::isOptionalWithdrawMailReceiver()) {
            $removable_key = array_search(MerchantEmailReceiver::WITHDRAWAL_NOTIFICATION, $required_mail_types);
            if ($removable_key !== false) {
                unset($required_mail_types[$removable_key]);
            }
        }

        return $required_mail_types;
    }

    public static function setRequiredMailTypes ($required_mail_types)
    {
        if (BrandConfiguration::isOptionalWithdrawMailReceiver()) {
            if (!isset($required_mail_types[MerchantEmailReceiver::WITHDRAWAL_NOTIFICATION])) {
                $required_mail_types[MerchantEmailReceiver::WITHDRAWAL_NOTIFICATION] = '';
            }
        }

        return $required_mail_types;
    }

    public static function prepareMailReceiverValidation ()
    {
        $optional_type = $rule = $message = $label = [];
        $required_mail_types = MerchantEmailReceiver::DEFAULT_USER_EMAIL;

        if (BrandConfiguration::isOptionalWithdrawMailReceiver()) {
            array_push($optional_type, MerchantEmailReceiver::WITHDRAWAL_NOTIFICATION);
        }
        if (sizeof($required_mail_types) > 0) {
            foreach ($required_mail_types as $key => $value) {
                $rule[$value] = in_array($value, $optional_type) ? 'nullable' : 'required';
                $title_label = StrUtility::titleCase(str_replace('_', ' ', $value) . ' email');
                $message[$value . '.required'] = $title_label . " " . __("is required");
                array_push($label, $title_label);
            }
        }

        return [$label, $rule, $message];
    }

    public function prepareSettlementCalendarData ($transactions, $available_balances, $extra_data = [], $is_report = false): array
    {
        $transaction_data = $transactions->groupBy(function ($item, $key) {
            if ($item->is_installment_wise_settlement == MerchantSettings::IS_INSTALLMENT_WISE_SETTLEMENT) {
                $date = $item->settlement_date_merchant;
            } else {
                $date = $item->effective_date;
            }
            return $date;
        })->sortBy(function($value, $key) {
            return (string) $key;
        }, SORT_STRING);

        $formatted_data = $dailyTransactionHistroies = [];
        $dailySaleReports = collect();
        if (BrandConfiguration::call([Mix::class, 'allowedDailyTransactionHistories'])) {
            $dailySaleReport = new DailySaleReport();
            $dailySaleReports = $dailySaleReport->getData($this->dailyReportSearch($extra_data));
        }

        foreach ($transaction_data as $key => $value) {
            $total_refund_net = $total_chargeback_net = $total_reverse_net = $reversed_count = 0;
            foreach ($value as $transaction) {
                if (isset($available_balances[$transaction->currency_id])) {

                    list($amount, $gross, $merchant_commission, $refund_or_chargeback_commission) = $this->calculateSettlementCalendarAmounts($transaction);

                    if ($transaction->status != MerchantSale::PROCESSED
                        && !empty($transaction->sale_settlement_status)
                        && $transaction->sale_settlement_status != SaleSettlement::STATUS_SETTLED
                    ) {
                        $available_balances[$transaction->currency_id] += $amount;
                    }

                    list($total_completed_transaction, $total_refunded_transaction, $total_chargeback_transaction) = $this->countTransaction($transaction);
                    $this_month = ManipulateDate::getDateFormat($key, 'Y-m-d H:i:s');
                    if (Arr::count($dailySaleReports) > 0) {
                        $dailySaleReportData = $dailySaleReports->where("currency_id", $transaction->currency_id)
                            ->where("merchant_id", $transaction->merchant_id)
                            ->where("formatted_created_at", ManipulateDate::format(9, $key))
                            ->first();
                    }
                    $total_refund_net += $dailySaleReportData->total_refund_net ?? 0;
                    $total_chargeback_net += $dailySaleReportData->total_chargeback_net ?? 0;
                    $total_reverse_net += $dailySaleReportData->total_reverse_net ?? 0;
                    $reversed_count += $dailySaleReportData->number_of_reverse ?? 0;

                    if ($is_report || ManipulateDate::isSameMonth($this_month, $extra_data['to_date'])) {
                        if (isset($formatted_data[$key][$transaction->currency_id])) {
                            $formatted_data[$key][$transaction->currency_id]['available_balance'] += $amount;
                            $formatted_data[$key][$transaction->currency_id]['total_amount'] += $amount;
                            $formatted_data[$key][$transaction->currency_id]['currency_symbol'] = $transaction->currency_symbol;
                            $formatted_data[$key][$transaction->currency_id]['completed_transactions'] += $total_completed_transaction;
                            $formatted_data[$key][$transaction->currency_id]['refunded_transactions'] += $total_refunded_transaction;
                            $formatted_data[$key][$transaction->currency_id]['chargebacked_transactions'] += $total_chargeback_transaction;
                            // $formatted_data[$key][$transaction->currency_id]['reverse_transactions'] += $transaction->refund_histories_reversed_count?? 0;
                            $formatted_data[$key][$transaction->currency_id]['total_merchant_commission'] += $merchant_commission;
                            $formatted_data[$key][$transaction->currency_id]['total_gross'] += $gross;
                        } else {
                            $formatted_data[$key][$transaction->currency_id] = [
                                'available_balance' => $available_balances[$transaction->currency_id],
                                'total_amount' => $amount,
                                'currency_symbol' => $transaction->currency_symbol,
                                'completed_transactions' => $total_completed_transaction,
                                'refunded_transactions' => $total_refunded_transaction,
                                'chargebacked_transactions' => $total_chargeback_transaction,
                                'reverse_transactions' => $reversed_count,
                                'total_merchant_commission' => $merchant_commission,
                                'total_gross' => $gross,
                                "total_refund_net" => $total_refund_net,
                                "total_chargeback_net" => $total_chargeback_net,
                                "total_reverse_net" => $total_reverse_net,
                                'currency_id' => $transaction->currency_id
                            ];
                        }
                        $this->populateDataForEarlySettlement($transaction);
                    }
                }
            }
        }
        if (Arr::count($dailySaleReports) > 0) {
            $dailyTransactionHistroies = $this->getDailyTransactionHistroies($transactions, $dailySaleReports, $available_balances);
        }
        $formatted_data = Arr::merge($formatted_data, $dailyTransactionHistroies);
        return $formatted_data;
    }

    public function populateDataForEarlySettlement ($transaction)
    {
        if ($transaction->is_installment_wise_settlement == MerchantSettings::IS_INSTALLMENT_WISE_SETTLEMENT) {
            $settle_date = $transaction->settlement_date_merchant;
            $amount = $transaction->sale_settlement_gross ?? 0;
            $merchant_share = $transaction->net_settlement;
        } else {
            $settle_date = $transaction->effective_date;
            $amount = $transaction->gross ?? 0;
            $commission_amount = (($transaction->gross * $transaction->merchant_commission_percentage) / 100) + $transaction->merchant_commission_fixed;
            $merchant_share = $transaction->gross - $commission_amount - $transaction->rolling_amount;
        }

        $this->settle_early_pre_data[$settle_date][$transaction->currency_id][] = [
            $transaction->payment_id,
            $transaction->order_id,
            $transaction->invoice_id,
            $amount,
            $merchant_share,
            !empty($transaction->installments_number) ? $transaction->installments_number : 0,
            $transaction->merchant_sale_id,
            !empty($transaction->sale_settlement_id) ? $transaction->sale_settlement_id : 0,
            $transaction->is_installment_wise_settlement,
        ];
    }

    public static function getMerchantSupportTicketLength(){
       $length = 25;

       if(BrandConfiguration::merchantSupportTicketEnableCustomLength()){
           $length = 10;
       }

       return $length;
    }
    public function prepareReconciliationReportData($paginate = false, $limit = 10, $page = 1)
    {
        $merchants = Merchant::query()
            ->leftJoin('wallets', 'wallets.user_id', 'merchants.user_id')
            ->leftJoin('currencies', 'currencies.id', 'wallets.currency_id')
            ->leftJoin('merchant_sales', 'merchants.id', 'merchant_sales.merchant_id')
            ->whereBetween('merchant_sales.effective_date', [ManipulateDate::startOfTheDay(), ManipulateDate::endOfTheDay()])
            ->select('merchants.id', 'merchants.name', 'wallets.amount', 'wallets.block_amount', 'wallets.withdrawable_amount', 'wallets.currency_id', 'wallets.updated_at', 'currencies.code', 'merchant_sales.effective_date');
        if ($paginate) {
            $merchants = $merchants->paginate($limit)->withQueryString();
        } else {
            $merchants = $merchants->get();
        }

        return $merchants;
    }

    public function reconciliationReportHeading()
    {
        return [
            __('Merchant ID'),
            __('Merchant Name'),
            __('Avaliable Balance'),
            __('Blocked Balance'),
            __('Payable'),
            __('Currency'),
            __('Payment Date')
        ];
    }

    public function checkAuthorizedMerchantInformationChange($req_data, $merchantObj)
    {
        $errorMsg = "";
        $status_code = "";
        if (BrandConfiguration::sendWelcomeMailOnAthorizedPersonEmailChanged() && isset($req_data['authorizedPersonE_Mail'])
          && $merchantObj->authorized_person_email != $req_data['authorizedPersonE_Mail']) {

            $authorized_person_name = GlobalUser::getNameSurnameByFullName($merchantObj->user->name,$merchantObj->id);

            if (isset($req_data['authorizedPersonName']) && $authorized_person_name['name'] == $req_data['authorizedPersonName']) {

                $errorMsg .= __("Please change authorized person name as authorized email changed") . "<br/>";
                $status_code = 1;
            }

            if (isset($req_data['authorizedPersonSurname']) && $authorized_person_name['surname'] == $req_data['authorizedPersonSurname']) {

                $errorMsg .= __("Please change authorized person surname as authorized email changed") . "<br/>";
                $status_code = 1;
            }

            if (isset($req_data['authorizedPersonPhoneNumber']) && $merchantObj->authorized_person_phone_number == $req_data['authorizedPersonPhoneNumber']) {

                $errorMsg .= __("Please change authorized person phone number as authorized email changed") . "<br/>";
                $status_code = 1;
            }

        }

        if (empty($status_code)) {
            $status_code = 100;
        }

        return [$status_code, $errorMsg];

    }

    public function canMerchantDeterminePos_withPosIdParamFromRequest($merchant)
    {

        if(BrandConfiguration::ignoreTokenValidation() &&
            !empty($merchant)){
            return $merchant->allow_token_less_access;
        }

        return 0;
    }
	
	public static function merchantKyesMasking($value){
	   
	   if(BrandConfiguration::merchantInformationMasking()){
		  $value = InformationMasking::mask($value, '*', 1);
	   }
	   
	   return $value;
	   
	}


    public function processMerchantGroup($merchantObj, $group_merchant_ids, $authObj, $is_request_from_merchant_create = false)
    {
        $merchantGroup = new MerchantGroup();
        $groupMerchants = (new Merchant())->getAllMerchantList($group_merchant_ids, ['id', 'user_id']);
        if (count($groupMerchants) > 0) {
            $merchant_groups = [];
            if(!$is_request_from_merchant_create){
                $merchantGroup->deleteByMerchantId($merchantObj->id);
            }
            foreach ($groupMerchants as $gmerchant) {
                $merchant_groups [] = [
                  "merchant_id" => $merchantObj->id,
                  "user_id" => $merchantObj->user_id,
                  "group_merchant_id" => $gmerchant->id,
                  "group_merchant_user_id" => $gmerchant->user_id,
                  "created_by_id" => $authObj->id ?? 0,
                  "updated_by_id" => $authObj->id ?? 0,
                  "created_at" => ManipulateDate::toNow(),
                  "updated_at" => ManipulateDate::toNow(),
                ];
            }

            if (count($merchant_groups) > 0) {
                $merchantGroup->saveEntry($merchant_groups);
            }

        }else{
            if(!$is_request_from_merchant_create) {
                $merchantGroup->deleteByMerchantId($merchantObj->id);
            }
        }

    }
	
	public function getAvailableBalance($transactions): array
	{
		$available_balances = [];
		
		if (!empty($transactions) && count($transactions) > 0) {
			$wallets = $transactions->first()->wallets ? $transactions->first()->wallets : [];

			if (!empty($wallets) && count($wallets) > 0) {
				foreach ($wallets as $wallet) {
					$available_balances[$wallet->currency_id] = Number::format($wallet->withdrawable_amount);
				}
			}
		}

		
		return $available_balances;
	}
	
	
	public function getMerchantTypeList(){
		$taxi_merchant_type = [];
		$fastpay_wallet_merchant_type = [];
		$tenant_merchant = [];
		$monthly_fee_merchant = [];
        $fast_pos_merchant = [];
        $early_settlement_fee_merchant = [];

		$merchant_list = [
			Merchant::GENERAL_MERCHANT => 'General Merchant',
			Merchant::OWN_TEST_MERCHANT => 'Own Test Merchant',
			Merchant::CRAFTGATE_MERCHANT => 'CraftGate Merchant',
			Merchant::DEPOSIT_BY_CREDIT_CARD_PF_MERCHANT => 'Deposit by Credit Card PF Merchant',
			Merchant::TEST_PAYMENT_INTEGRATION_MERCHANT => 'Test Payment Integration Merchant',
			Merchant::MARKETPLACE_MERCHANT => 'Marketplace Merchant',
			Merchant::OXIVO_MERCHANT => 'Oxivo Merchant',
			Merchant::WALLET_GATE_MERCHANT => 'Wallet Gate Merchant',
			Merchant::PAVO_MERCHANT => 'Pavo Merchant',
			Merchant::API_MERCHANT => 'Created by API Merchant',
			Merchant::MT_MERCHANT => 'MT Merchant',
			Merchant::BILL_PAYMENT_MERCHANT => 'Bill Payment Merchant',
		];
		
		if(BrandConfiguration::allowTaxiMerchant()){
			$taxi_merchant_type = Merchant::TAXI_MERCHANT_TYPE;
		}elseif (BrandConfiguration::allowFastPayWalletMerchant()){
			$fastpay_wallet_merchant_type = Merchant::FASTPAY_WALLET_MERCHANT_TYPE;
		}
		if (BrandConfiguration::isLicenseOwnerTenant()) {
			$tenant_merchant = Merchant::TENANT_MERCHANT_TYPE;
		}
		if (BrandConfiguration::isAllowMerchantMonthlyFee()){
			$monthly_fee_merchant = $this->monthlyMerchantType();
		}
        if (BrandConfiguration::call([BackendAdmin::class, 'isAllowMerchantFastPosType'])) {
            $fast_pos_merchant = $this->FastPosMerchantType();
        }

        if (BrandConfiguration::call([BackendAdmin::class, 'isAllowedEarlySettlement'])) {
            $early_settlement_fee_merchant = $this->earlySettlementFeeMerchantType();
        }
		
		return $merchant_list + $taxi_merchant_type + $fastpay_wallet_merchant_type + $tenant_merchant + $monthly_fee_merchant + $fast_pos_merchant + $early_settlement_fee_merchant;
	}
	
	public function monthlyMerchantType(){
		return [
			Merchant::MONTHLY_FEE_MERCHANT => 'Monthly Fee Merchant'
		];
	}

    public function earlySettlementFeeMerchantType(){
        return [
            Merchant::EARLY_SETTLEMENT_FEE_MERCHANT => 'Early Settlement Fee Merchant'
        ];
    }

	public function FastPosMerchantType(){
		return [
            Merchant::FASTPOS_PHYSICAL_MERCHANT => 'Fastpos Physical',
            Merchant::FASTPOS_VIRTUAL_MERCHANT => 'Fastpos Virtual',
            Merchant::FASTPOS_DPL_MERCHANT => 'Fastpos – DPL'
		];
	}
	
	public static function getAdminMerchantAnalyticsHeaders($is_revenue_hide = false,$allow_account_manager=false): array
	{
		$index_revenue = 14;
		$index_integrator_name = 2;
		$heading = [
			__('Merchant ID'),
			__('Merchant name'),
			__('Integrator'),
			__('Currency'),
			__('Sales Amount'),
			__('Number of Sales'),
			__('Refund Amount'),
			__('Number of Refunds'),
            __('Refund Request Amount'),
            __('Number of Refund Request'),
			__('Chargeback Amount') ,
			__('Number of Chargebacks'),
            __('Chargeback Requested Amount'),
            __('Number of Chargeback Request'),
			__('Revenue'),
			__('First Transaction Date'),
			__('Last Successful Transaction Date'),
		];

        if ($allow_account_manager) {
            $heading = Arr::merge($heading, [__('Account Manager')]);
        }

		if($is_revenue_hide === false){
			$heading = Arr::unset($heading, [$index_revenue]);
		}
		
		if(!BrandConfiguration::call([Mix::class, 'isAllowIntegratorNameShowingOnMerchantAnalyticsReport'])){
			$heading = Arr::unset($heading, [$index_integrator_name]);
		}
		
		return $heading;
	}
	
	public static function hideRevenueFromSaleData($SaleData, $is_revenue_hide)
	{
		return Arr::map(function($sale) use ($is_revenue_hide){
			unset($sale['merchant_status'], $sale['integrator_id'], $sale['merchant_created_at'], $sale['created_at']);
			if(!$is_revenue_hide){
				unset($sale['revenue']);
			}
			return $sale;
		}, $SaleData);
	}
	
	public static function hideIntegratorNameFormSaleData($SaleData, $is_show_integrator_name)
	{
		if($is_show_integrator_name){
			$SaleData = Arr::map(function($sale) use ($is_show_integrator_name){
				
				if($is_show_integrator_name){
					unset($sale['integrator_name']);
				}
				return $sale;
			}, $SaleData);
		}
		
		return $SaleData;

	}
	
	public static function merchantAnalyticsIsRevenueHide(): bool
	{
		$status = false;
		if (BrandConfiguration::isShowAnalyticsRevenue() &&
			auth()->user()->hasPermissionOnAction(config('constants.defines.APP_MERCHANT_ANALYTICS_HIDE_REVENUE'))
		) {
			$status = true;
		}
		
		return $status;
	}
	
	public static function prepareMerchantAnalyticsDataForCronjob($request, $search): array
	{
		return [
			'file_type' => $request->get('file_type', MerchantReportHistory::FORMAT_CSV),
			'date_range' => $search['daterange'],
			'is_revenue_hide' => GlobalMerchant::merchantAnalyticsIsRevenueHide(),
			'is_process_by_admin_user' => true,
			'language' => Language::getSystemLanguage()
		];
	}
	

    public function resendWelcomeMail($id)
    {
        $user = (new User())->findById($id);
        $encrypt_email_data = $this->customEncryptionDecryption($user->email, config('app.brand_secret_key'), 'encrypt', true);
        $data['merchent_panel_link'] = config('app.app_merchant_url');
		$data['create_password_link'] =  config('app.app_merchant_url').'/password/create/'.$encrypt_email_data;
        $data['name'] = $user->name;
        $emailTemplate = "merchant_user.user_create";
	    // $filePath = view()->getFinder()->find('email.merchant_user.user_create_en');
        $from = Config('app.SYSTEM_NO_REPLY_ADDRESS');
        $data['email'] = $user->email;
        $to = $user->email;
        //out_going_email
        $this->setGNPriority(OutGoingEmail::PRIORITY_MEDIUM);
        $sendMail = $this->sendEmail($data, "merchant_user_create", $from, $to, "", $emailTemplate, $user->language);
        if ($sendMail) {
            Cache::add(self::CACHE_KEY_FOR_RESEND_WELCOME_MAIL.$id, true, self::RESEND_WELCOME_MAIL_CACHE_DURATION);
            $result['message'] = __('Email sent successfully');
            $result['status'] = true;
        } else {
            $result['message'] = __('Email not sent');
            $result['status'] = false;
        }
        return $result;
    }

    public function prepareSearchKeys($keys)
    {
        $data['email'] = $keys['main_merchant_email'] ?? null;
        $data['phone'] = $keys['main_merchant_phone'] ?? null;
        return $data;
    }

    public function getProtectedAmountReportData($search, $wallets, $currencies)
    {
        $search['merchantid'] = $wallets->unique('merchant_id')->pluck('merchant_id')->toArray();
        if (Arr::count($search['merchantid']) > 0) {
            $search['allTransaction'] = config('constants.TRANSACTION_TYPE.SALE');
            $search['both_created_updated_at'] = "yes";
            $search['transactionStateChargeback'] = SaleTransaction::getAccountStatementDefualtTransactionState();

            $commission_data = [];
            $net_data = [];
            $settlement_amount = [];

            $wallet_data = $wallets->map(function ($wallets) {
                return collect($wallets->toArray())
                    ->only('user_id', 'currency_id')
                    ->all();
            });

            if(BrandConfiguration::call([BackendAdmin::class, 'allowNonBankSettlementAmountInDailyBalanceReport'])){
                $today_settlement_bank_date = ManipulateDate::addHour(ManipulateDate::startOfTheDay(), Settlement::SETTLEMENT_HOURS);
                $now = ManipulateDate::toNow();
                if ($now > $today_settlement_bank_date) {
                    $now = $today_settlement_bank_date;
                }
                $non_bank_settlement_amount_data = (new MerchantSale())->getNonBankSettlementData($now, $search, ['user_id', 'currency_id', 'amount']);
                $settlement_amount = $this->formatNonBankSettlementData($wallet_data, $non_bank_settlement_amount_data);
            }else{
                [$commission_data, $net_data] = $this->getNetAndCommissionForDailyBalanceProtectedAmountReport($wallet_data, $search, $currencies);
            }

            return [$wallets, $commission_data, $net_data, $settlement_amount];
        }
    }

    public function getNetAndCommissionForDailyBalanceProtectedAmountReport($wallet_data, $search, $currencies){
        $saleTransactionObj = new SaleTransaction();
        $sale_transactions = $saleTransactionObj->accountStatement($search);
        $saleTransactionObj->currencies = $currencies;
        $saleTransactionObj->is_protected_amount = true;
        $commission_data = [];
        $net_data = [];
        foreach ($wallet_data as $wallet) {
            $sale_transactions_merchant = $sale_transactions->where('user_id', $wallet['user_id'])->where('currency_id', $wallet['currency_id']);
            list($headings, $transaction_row) = $saleTransactionObj->formattingAccountStatementNew($sale_transactions_merchant);

            $merchant_commission = 0;
            $merchant_net = 0;
            foreach ($transaction_row as $transaction) {
                $merchant_commission += $transaction["merchant_commission"] ?? 0;
                $merchant_net += $transaction["net_amount"] ?? 0;
            }
            $commission_data[$wallet['user_id']][$wallet['currency_id']] = $merchant_commission;
            $net_data[$wallet['user_id']][$wallet['currency_id']] = $merchant_net;
        }

        return [$commission_data, $net_data];
    }

    public function formatNonBankSettlementData($wallet_data, $non_bank_settlement_amount_data){
        $settlement_amount = [];

        foreach ($wallet_data as $wallet) {
            $non_bank_settlement_amount = $non_bank_settlement_amount_data->where("user_id", $wallet['user_id'])->where('currency_id', $wallet['currency_id'])->sum('amount');
            $settlement_amount[$wallet['user_id']][$wallet['currency_id']] = $non_bank_settlement_amount ?? 0;
        }

        return $settlement_amount;
    }

    public function getMerchantSettlementReport($search)
    {
        $rows = [];
        $refund_ids = [];
        $from_date = ManipulateDate::getDateFormat($search['from_date'] ?? '', ManipulateDate::FORMAT_DATE_Y_m_d);
        $to_date = ManipulateDate::getDateFormat($search['to_date'] ?? '', ManipulateDate::FORMAT_DATE_Y_m_d);

        $temp_refund_history = [];
        $payment_id = '';
        $temp_last_index = 0;
        $refund_history_data = [];
        $temp_settlement_date = null;

        $search['is_chunk_limit'] = self::SETTLEMENT_CALENDAR_EXPORT_CHUNK_LIMIT;
        $search['chunk_callback'] = function($transaction_list)
        use (
            &$rows,
            &$refund_ids,
            $from_date,
            $to_date,
            &$payment_id,
            &$temp_last_index,
            &$temp_refund_history,
            &$refund_history_data,
            &$temp_settlement_date
        ) {
            foreach ($transaction_list as $transaction){

                $common_info = [
                    $transaction->merchant_id,
                    $transaction->name,
                    $transaction->payment_id
                ];

                $currency = $transaction->currency_symbol;

                $settlement_date = ManipulateDate::getDateFormat(
                    $transaction->is_installment_wise_settlement == MerchantSettings::IS_INSTALLMENT_WISE_SETTLEMENT ?
                        $transaction->settlement_date_merchant: $transaction->effective_date, ManipulateDate::FORMAT_DATE_Y_m_d
                );

                if($transaction->payment_id != $payment_id){

                    $rows = Arr::merge($rows, $temp_refund_history);
                    $temp_refund_history = [];
                    $refund_history_data = $transaction->is_installment_wise_settlement == MerchantSettings::IS_INSTALLMENT_WISE_SETTLEMENT
                        ? Encode::unserialize((Encode::serialize(($transaction->refundHistory))))
                        : null;
                    $temp_last_index = 0;

                    foreach ($transaction->refundHistory as $refundHistory){
                        if(!empty($refundHistory->id)){
                            $refund_ids[] = $refundHistory->id;
                        }

                        if($refundHistory->transaction_state_id == TransactionState::PENDING ||
                            ($refundHistory->transaction_state_id == TransactionState::REJECTED && !$refundHistory->is_reversed)) continue;

                        $merchant_commission_percentage = $transaction->merchant_commission_percentage ?? 0;
                        $merchant_commission_fixed = $transaction->merchant_commission_fixed ?? 0;
                        $refund_histories_amount = $refundHistory->amount ?? 0;
                        $commission = (($refund_histories_amount * $merchant_commission_percentage) /100) + $merchant_commission_fixed;
                        $fee_amount = $refundHistory->refund_commission + $refundHistory->refund_commission_fixed;
                        $merchant_share = $refundHistory->net_refund_amount + $refundHistory->rolling_refund_amount + $fee_amount;

                        $refund_updated_at = ManipulateDate::getDateFormat($refundHistory->updated_at ?? '', ManipulateDate::FORMAT_DATE_Y_m_d);
                        $refund_created_at = ManipulateDate::getDateFormat($refundHistory->created_at ?? '', ManipulateDate::FORMAT_DATE_Y_m_d);

                        $data =[
                            'transaction_state_id' => $refundHistory->transaction_state_id,
                            'is_reversed' => $refundHistory->is_reversed,
                            'refund_type' => $refundHistory->refund_type,
                            'is_fully_refunded' => $refundHistory->is_fully_refunded,
                            'product_price' => $transaction->product_price,
                            'refund_histories_amount' => $refundHistory->amount,
                        ];

                        [$status, $is_reversed] = $this->getRefundStatus($data);

                        if($transaction->is_installment_wise_settlement != MerchantSettings::IS_INSTALLMENT_WISE_SETTLEMENT
                            || ($transaction->is_installment_wise_settlement == MerchantSettings::IS_INSTALLMENT_WISE_SETTLEMENT && $is_reversed)
                        ){

                            if($is_reversed){
                                $sign = "-";
                                [$status_for_reversed, $check_reversed] = $this->getRefundStatus($data, true);
                                $temp_refund_history[] = [
                                    ...$common_info,
                                    __($status_for_reversed),
                                    self::getSaleSettlementsInstallmentNumber($transaction ), //sales settlements installment for reversed
                                    self::getSaleInstallmentNumber($transaction), //sale table  installment for reversed
                                    $sign.$currency.Number::format($refundHistory->amount, 2),
                                    $sign.$currency.Number::format($commission, 2),
                                    $sign.$currency.Number::format($merchant_share, 2),
                                    ManipulateDate::greaterThanOrEqualTo($refund_created_at,$settlement_date) ? $refund_created_at : $settlement_date,
                                    ManipulateDate::format(ManipulateDate::CASE_TYPE_DMY_HI,$refundHistory->created_at),
                                    ManipulateDate::format(ManipulateDate::CASE_TYPE_DMY_HI,$refundHistory->updated_at)
                                ];

                                $temp_last_index++;
                            }

                            $sign = $is_reversed ? "" : "-";

                            $temp_refund_history[] = [
                                ...$common_info,
                                __($status),
                                self::getSaleSettlementsInstallmentNumber($transaction), //sales settlements partial refund installment
                                self::getSaleInstallmentNumber($transaction), //sale table  partial refund installment
                                $sign.$currency.Number::format($refundHistory->amount, 2),
                                $sign.$currency.Number::format($commission, 2),
                                $sign.$currency.Number::format($merchant_share, 2),
                                $is_reversed ? (ManipulateDate::greaterThanOrEqualTo($refund_updated_at,$settlement_date) ? $refund_updated_at : $settlement_date):
                                    (ManipulateDate::greaterThanOrEqualTo($refund_created_at,$settlement_date) ? $refund_created_at : $settlement_date),
                                $is_reversed ? ManipulateDate::format(ManipulateDate::CASE_TYPE_DMY_HI,$refundHistory->updated_at)  : ManipulateDate::format(ManipulateDate::CASE_TYPE_DMY_HI,$refundHistory->created_at),
                                ManipulateDate::format(ManipulateDate::CASE_TYPE_DMY_HI,$refundHistory->updated_at)
                            ];

                            $temp_last_index++;
                        }
                    }

                }

                $payment_id = $transaction->payment_id;
                $created_at = $transaction->created_at;
                $updated_at = $transaction->updated_at;

                if($transaction->is_installment_wise_settlement == MerchantSettings::IS_INSTALLMENT_WISE_SETTLEMENT){
                    $merchant_share = $transaction->net_settlement;
                    $gross = $transaction->sale_settlement_gross ?? 0;
                    $merchant_commission = $transaction->sale_settlement_merchant_commission ?? 0;
                }else{
                    $commission_amount = (($transaction->gross * $transaction->merchant_commission_percentage)
                            /100) + $transaction->merchant_commission_fixed;
                    $merchant_share = $transaction->gross - $commission_amount - $transaction->rolling_amount;
                    $gross = $transaction->gross ?? 0;
                    $merchant_commission = $transaction->merchant_commission ?? 0;
                }



                if($settlement_date >= $from_date && $settlement_date <= $to_date){
                    $rows[] = [
                        ...$common_info,
                        __('Sale'),
                        self::getSaleSettlementsInstallmentNumber($transaction ), //sales settlements installment
                        self::getSaleInstallmentNumber($transaction), //sale table  installment
                        $currency.Number::format($gross, 2),
                        $currency.Number::format($merchant_commission, 2),
                        $currency.Number::format($merchant_share, 2),
                        $settlement_date,
                        ManipulateDate::format(ManipulateDate::CASE_TYPE_DMY_HI,$created_at),
                        ManipulateDate::format(ManipulateDate::CASE_TYPE_DMY_HI,$updated_at),
                    ];
                }

                if($transaction->is_installment_wise_settlement == MerchantSettings::IS_INSTALLMENT_WISE_SETTLEMENT){

                    $net_settlement = $transaction->net_settlement;

                    foreach($refund_history_data as $key=>$refundHistory){

                        if(!$refundHistory->net_refund_amount
                            || $refundHistory->is_reversed
                            || $refundHistory->transaction_state_id == TransactionState::PENDING
                            || ($refundHistory->transaction_state_id == TransactionState::REJECTED && !$refundHistory->is_reversed)
                        ) continue;

                        if(!$net_settlement) break;

                        if($net_settlement >= $refundHistory->net_refund_amount){
                            $settlement_amount = $refundHistory->net_refund_amount;
                            $net_settlement -= Number::format($refundHistory->net_refund_amount, 2);
                            $refund_history_data[$key]["net_refund_amount"] = 0;
                        }else{
                            $settlement_amount = $net_settlement;
                            $refund_history_data[$key]["net_refund_amount"] -= $net_settlement;
                            $net_settlement = 0;
                        }

                        $data =[
                            'transaction_state_id' => $refundHistory->transaction_state_id,
                            'is_reversed' => $refundHistory->is_reversed,
                            'refund_type' => $refundHistory->refund_type,
                            'is_fully_refunded' => $refundHistory->is_fully_refunded,
                            'product_price' => $transaction->product_price,
                            'refund_histories_amount' => $refundHistory->amount,
                        ];

                        [$status, $is_reversed] = $this->getRefundStatus($data);

                        $merchant_commission_percentage = $transaction->merchant_commission_percentage ?? 0;
                        $merchant_commission_fixed = $transaction->merchant_commission_fixed ?? 0;
                        $refund_histories_amount = $refundHistory->amount ?? 0;
                        $commission = (($refund_histories_amount * $merchant_commission_percentage) /100) + $merchant_commission_fixed;

                        $merchant_commission = ($commission/$transaction->refundHistory[$key]["net_refund_amount"])*$settlement_amount;
                        $total_amount = $settlement_amount + $merchant_commission;

                        $fee_amount = (($refundHistory->refund_commission/$transaction->refundHistory[$key]["net_refund_amount"])* $settlement_amount)
                            + (($refundHistory->refund_commission_fixed/$transaction->refundHistory[$key]["net_refund_amount"])* $settlement_amount);

                        $merchant_share = $settlement_amount
                            + (($refundHistory->rolling_refund_amount/$transaction->refundHistory[$key]["net_refund_amount"])* $settlement_amount)
                            + $fee_amount;


                        if($settlement_date >= $from_date && $settlement_date <= $to_date){

                            $refund_settlement_date = ManipulateDate::getDateFormat($refundHistory->created_at ?? '', 'Y-m-d');
                            $last_index = Arr::count($temp_refund_history) - 1;
                            $sign = "-";

                            if($last_index >= $temp_last_index &&
                                ($temp_settlement_date == $settlement_date
                                    || $refund_settlement_date >= $settlement_date)
                            ){

                                $temp_amount = \common\integration\Utility\Str::preg_replace($temp_refund_history[$last_index][6],'/[^0-9.]/');
                                $temp_commission = \common\integration\Utility\Str::preg_replace($temp_refund_history[$last_index][7],'/[^0-9.]/');
                                $temp_settlement_amount = \common\integration\Utility\Str::preg_replace($temp_refund_history[$last_index][8],'/[^0-9.]/');

                                $temp_refund_history[$last_index][6] = $sign.$currency.Number::format((Number::normalize($temp_amount)+$total_amount), 2);
                                $temp_refund_history[$last_index][7] = $sign.$currency.Number::format((Number::normalize($temp_commission)+$merchant_commission), 2);
                                $temp_refund_history[$last_index][8] = $sign.$currency.Number::format((Number::normalize($temp_settlement_amount)+$merchant_share), 2);

                            }else{

                                $temp_refund_history[] = [
                                    ...$common_info,
                                    __($status),
                                    self::getSaleSettlementsInstallmentNumber($transaction), //sales settlements refund installment
                                    self::getSaleInstallmentNumber($transaction), //refund sales installment
                                    $sign.$currency.Number::format($total_amount, 2),
                                    $sign.$currency.Number::format($merchant_commission, 2),
                                    $sign.$currency.Number::format($merchant_share, 2),
                                    ManipulateDate::greaterThanOrEqualTo($refund_settlement_date,$settlement_date) ? $refund_settlement_date : $settlement_date,
                                    ManipulateDate::format(ManipulateDate::CASE_TYPE_DMY_HI,$refundHistory->created_at),
                                    ManipulateDate::format(ManipulateDate::CASE_TYPE_DMY_HI,$refundHistory->updated_at),
                                ];
                            }
                        }
                    }

                    $temp_settlement_date = $settlement_date;
                }

            }

        };

        $this->getByMerchantIdEffectiveDate($search);
        $rows = Arr::merge($rows, $temp_refund_history);

        $rows = $this->includeRefundWithExport($rows, $search, $refund_ids);

        return $rows;
    }

    private function calculateSettlementCalendarAmounts($transaction):array
    {

        $amount = $gross = $merchant_commission = $refund_or_chargeback_commission =0;

        if ($transaction->is_installment_wise_settlement == MerchantSettings::IS_INSTALLMENT_WISE_SETTLEMENT) {
            $amount = $transaction->sale_settlement_status ? Number::format($transaction->sale_settlement_settled_amount) : Number::format($transaction->net_settlement - $transaction->refunded_amount - $transaction->refund_request_amount);
            $gross = Number::format($transaction->sale_settlement_gross - $transaction->refunded_amount - $transaction->refund_request_amount);
            $refund_or_chargeback_commission = Number::format((($transaction->refunded_amount  * $transaction->merchant_commission_percentage) / 100) + $transaction->merchant_commission_fixed);

        } else {
            $amount = Number::format($transaction->amount - $transaction->refund_request_amount);
            $gross = Number::format($transaction->gross - $transaction->total_refunded_amount);
            $refund_or_chargeback_commission = Number::format((($transaction->total_refunded_amount * $transaction->merchant_commission_percentage) / 100) + $transaction->merchant_commission_fixed);
        }

//        if (BrandConfiguration::call([BackendMix::class, 'customizeMerchantSettlementReport'])){
//            $merchant_commission = Number::format($transaction->merchant_commission - $refund_or_chargeback_commission);
//            $gross = Number::format($gross);
//        } else {
        $merchant_commission = ! $transaction->is_installment_wise_settlement
            ? Number::format($transaction->merchant_commission - $refund_or_chargeback_commission)
            : (new GlobalCommission())->getCommissionAmountForSettlement($transaction);
        $gross = Number::format($amount + $merchant_commission);
//        }

        return [$amount, $gross, $merchant_commission, $refund_or_chargeback_commission];
    }

    public function getDailyTransactionHistroies($transactions, $dailySaleReports, $available_balances, $extras = [])
    {
        if (isset($extras['settlement_dates'])) {
            $settlement_dates = $extras['settlement_dates'];
        } else {
            $settlement_dates = Arr::merge($transactions->pluck("effective_date")->toArray(), $transactions->pluck("settlement_date_merchant")->toArray());
        }

        $transaction_dates = $dailySaleReports->pluck("formatted_created_at")->toArray();
        $daily_transaction_dates = Arr::diff($transaction_dates, $settlement_dates);

        $data = [];
        foreach ($daily_transaction_dates as $daily_transaction_date) {

            foreach ($available_balances as $currency_id => $amount) {

                $dailySaleReportData = $dailySaleReports->where("currency_id", $currency_id)
                    ->where("formatted_created_at", ManipulateDate::format(9, $daily_transaction_date));

                if (Arr::count($dailySaleReportData) > 0) {
                    $data[$daily_transaction_date][$currency_id] = [
                        'available_balance' => 0.00,
                        'total_amount' => 0.00,
                        'completed_transactions' => 0.00,
                        'refunded_transactions' => 0.00,
                        'chargebacked_transactions' => 0.00,
                        'reverse_transactions' => 0.00,
                        'total_merchant_commission' => 0.00,
                        'total_gross' => 0.00,
                        'currency_symbol' => Currency::findCurrencyById($currency_id)[1],
                        "total_refund_net" => $dailySaleReportData->sum("total_refund_net") ?? 0,
                        "total_chargeback_net" => $dailySaleReportData->sum("total_chargeback_net") ?? 0,
                        "total_reverse_net" => $dailySaleReportData->sum("total_reverse_net") ?? 0,
                        "currency_id" => $currency_id
                    ];
                }
            }
        }
        return $data;
    }

    public function dailyReportSearch($extra_data)
    {
        $search['merchant_id'] = [$extra_data['merchant_id']];
        $search['from_date'] = $extra_data['from_date'];
        $search['to_date'] = $extra_data['to_date'];
        $search['enable_create_at_group_by'] = true;
        $search['daterange'] = $search['from_date'] . " - " . $search['to_date'];
        return $search;
    }

    public function getMerchantAnnouncement($request){
        $data = $status_code = $status_message = "";
        $log_data['ACTION'] = "GET_MERCHANT_ANNOUNCEMENT";
        try {
            $rules = AppRequestValidation::getMerchantAnnouncementRules();
            $status = AppRequestValidation::validateData($request->all(), $rules);
            if(!empty($status['status_code'])){
                $status_code = $status['status_code'];
                $status_message =$status['status_message'];
            }
            if (empty($status_code) && !(ManipulateDate::checkDiffBetweenTwoDates($request->from_date, $request->to_date) > 0)) {
                $status_code = ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR;
                $status_message = "Date range difference should be minimum 1 days";
            }
            if (empty($status_code)) {
                $lang = isset($request->language)? $request->language : 'tr';
                $from_date = ManipulateDate::startOfTheDay($request->from_date);
                $to_date = ManipulateDate::endOfTheDay($request->to_date);
                $search = $this->prepareGetTransactionSearchData($request, $from_date, $to_date);
                $annObj = new Announcement();
                $announcementsData = $annObj->getAnnouncementByMerchantID($search['merchant_id'], 0, $search);
                $data = $this->prepareAnnouncement($annObj, $announcementsData,$lang,$request->isReqFromApi);
                $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
                $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_SUCCESS_CODE];

            }

        }catch (\Throwable $e) {
            $status_code = ApiService::API_SERVICE_FAILED_CODE;
            $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_FAILED_CODE];
            $log_data['error_msg'] = Exception::fullMessage($e);
        }

        $log_data["response"] = $response = [
            'data' => $data,
            'status_code' => $status_code,
            'status_message' => $status_message
        ];
        (new ManageLogging())->createLog($log_data);
        return $response;
    }

    public function prepareAnnouncement($annObj,$announcementsData,$lang='tr',$isReqFromApi = true){
       $data = [];
       foreach ($announcementsData as $announcement){
        $data[] = $annObj->prepareAnnouncementData($announcement, $lang,$isReqFromApi);
       }
       return $data;
    }
    public function prepareGetTransactionSearchData($request, $from_date, $to_date){
        $search['merchant_id'] = $request->merchant_id ?? '';
        $search['from_date'] = ManipulateDate::startOfTheDay($from_date);
        $search['to_date'] = ManipulateDate::endOfTheDay($to_date);

        return $search;
    }

    public function getMerchantInformation($request)
    {
        $data = $status_code = $status_message = "";
        $log_data['ACTION'] = "GET_MERCHANT_ALL_INFORMATION";
        try {
            if (!BrandConfiguration::call([BackendMerchant::class, 'isAllowedGetMerchantInformation'])) {
                $status_code = ApiService::API_SERVICE_FAILED_CODE;
                $status_message = "Invalid request";
            }
            if (empty($status_code)) {
                $rules = AppRequestValidation::getMerchantInformationValidation();
                $status = AppRequestValidation::validateData($request->all(), $rules);
                if (!empty($status['status_code'])) {
                    $status_code = ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR;
                    $status_message = $status['status_message'];
                }
            }
            if (empty($status_code)) {
                $selected_column = $this->merchantSelectedColumns();
                $merchantData = (new  CommonMerchant())->getMerchantRelationDataById($request->merchant_id, $selected_column);
                $data = !empty($merchantData) ? $merchantData->toArray() : [];
                $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
                $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_SUCCESS_CODE];
            }
        } catch (\Throwable $e) {
            $status_code = ApiService::API_SERVICE_FAILED_CODE;
            $status_message = $e->getMessage();
            $log_data['error_msg'] = Exception::fullMessage($e);
        }
        $log_data["response"] = $response = [
            'data' => $data,
            'status_code' => $status_code,
            'status_message' => $status_message
        ];
        (new ManageLogging())->createLog($log_data);
        return $response;
    }

    public function merchantSelectedColumns()
    {
      return $columns = [
            'users.is_otp_required',

            'merchants.is_allow_foreign_cards',
            'merchants.calculate_pos_by_bank',
            'merchants.allow_pay_by_token',
            'merchants.is_allow_b2c_automation',
            'merchants.is_allow_walletgate',

            'merchant_settings.is_allow_dpl',
            'merchant_settings.is_allow_manual_pos',
            'merchant_settings.is_allow_one_page_payment',
            'merchant_settings.is_allow_pre_auth',
            'merchant_settings.is_allow_recurring_payment',
            'merchant_settings.is_allow_virtual_card',
            'merchant_settings.is_exempt_card_block',
            'merchant_settings.is_allow_automatic_ftp_report',
            'merchant_settings.is_test_merchant'
        ];
    }

    public function getSettlementCalenderData($request){
        $status_code = ApiService::API_SERVICE_NO_DATA_FOUND;
        $data = [];
        $validation = AppRequestValidation::settlementCalendarValidation($request->all());

        if ($validation->fails()) {
            $status_code = ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR;
            $status_description = $validation->errors()->first();
        }else{
            $search = $this->prepareSettlementFilterData($request);
            $transactions = $this->getByMerchantIdEffectiveDate($search);
            $available_balances = $this->getAvailableBalance($transactions);
            $report_data = $this->prepareSettlementCalendarData($transactions, $available_balances, $search);

            if (!empty($report_data)) {
                $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
                $data = $report_data;
            }
            $status_description = ApiService::API_SERVICE_STATUS_MESSAGE[$status_code];
        }

        return [$status_code, $status_description, $data];
    }

    public function getMerchantCommissionData($request, $is_request_from_api = false)
    {
        $merchant_id = Auth::user()->merchants->id ?? 0;
        if($is_request_from_api){
            $currencies = (new Currency())->getAllCurrencies([], false, false, [$request->currency_code]);
        }else{
            $currencies = (new Currency())->getAllCurrencies();
        }

        $activeCards = [];
        $cardProgram = new CardProgram();

        $merchant_sale_commissions  = (new GlobalCommission())->getMerchantSaleCommissions($merchant_id, $currencies);

        $card_programs = $merchant_sale_commissions["card_programs"] ?? [];
        $foreign_card_commission = $merchant_sale_commissions["foreign_card_commission"] ?? [];
        $minFixedCommission = $merchant_sale_commissions["min_fixed_commission"] ?? '-';
        $minPercentageCommission =  $merchant_sale_commissions["min_percentage_commission"] ?? '-';
        $single_payment_commissions = $merchant_sale_commissions["single_payment_commissions"] ?? [];

        list($commissions, $operation_names) = (new GlobalCommission())->formatMerchantCommissions($merchant_id, $currencies);

        $program_logos = $cardProgram->getAllLogos(1);
        $program_col_color = $cardProgram->getAllColorCodes(1);
        $hideable_card_programs = $cardProgram->hideCardProgramsMerchantCommissionTableList();
        $activeCardPrograms = $cardProgram->getAll(1)->whereNotIn('code', $hideable_card_programs);

        if (BrandConfiguration::call([BackendMerchant::class, 'showCardProgramsAccordingToPos'])) {
            $posCommissionInsatance = new MerchantPosCommission();
            $is_allowed_multiple_pos_programs = GlobalUser::isAllowedMultiplePosPrograms();
            $select_col = 'pos.program';
            if($is_allowed_multiple_pos_programs) {
                $select_col = 'pos_programs.program';
            }
            $posList = $posCommissionInsatance->getPosByDistinct([
                'merchant_ids' => [$merchant_id],
                'select_cols' => ['merchant_pos_commissions.pos_id', $select_col]
            ], $is_allowed_multiple_pos_programs);
            $posProgramArr = $posList?->pluck('program');
            if (!empty($posProgramArr)) {
                $activeCardPrograms = $activeCardPrograms->whereIn('code', $posProgramArr);
            }
        }

        if (!empty($activeCardPrograms)) {
            $activeCardPrograms = Json::decode($activeCardPrograms, true);

            foreach ($activeCardPrograms as $k => $v) {
                $activeCards[] = $v['brand_api_name'];
            }
        }

        $logo_url = Storage::url(config('brand.logo'));

        if($is_request_from_api){

            return [
                'activeCards' => $activeCards,
                'card_programs' => $card_programs,
                'minFixedCommission' => $minFixedCommission,
                'minPercentageCommission' => $minPercentageCommission,
                'card_commissions' => $foreign_card_commission,
                'program_logos' => $program_logos,
                'logo_storage_url' => Storage::url("assets/images/card_program_images/"),
                'program_col_color' => $program_col_color,
                'activeCardPrograms' => $activeCardPrograms,
            ];
        }

        return view(
            'commissions.index',
            compact(
                'currencies', 'operation_names', 'commissions', 'card_programs',
                'foreign_card_commission', 'minFixedCommission', 'minPercentageCommission',
                'program_logos', 'program_col_color', 'hideable_card_programs', 'activeCardPrograms',
                'activeCards', 'single_payment_commissions', 'logo_url'
            )
        );
    }

    public function getMerchantCommissionDataApi($request){
        $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
        $status_description = ApiService::API_SERVICE_STATUS_MESSAGE[$status_code];
        $data = [];

        $validation = AppRequestValidation::merchantCommissionDataValidation($request->all());

        if ($validation->fails()) {
            $status_code = ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR;
            $status_description = $validation->errors()->first();
        }else{
            $data = $this->getMerchantCommissionData($request, true);
        }

        return [$status_code, $status_description, $data];
    }

    private function getRefundStatus($data, $for_reversed=false){

       $label = 'Unknown';
       $is_reversed = false;

        if(!$for_reversed && $data['transaction_state_id'] == TransactionState::REJECTED){
            $label = $data['is_reversed'] == RefundHistory::REVERSED ? 'Reversed' : 'Refund Rejected';
            $is_reversed = $data['is_reversed'] == RefundHistory::REVERSED;
        }elseif($data['refund_type']==RefundHistory::TYPE_REFUND){
            if($for_reversed){
                $label = $data['product_price'] == $data['refund_histories_amount'] ? 'Refund' : 'Partial Refund';
            }else{
                $label = $data['is_fully_refunded'] == RefundHistory::IS_FULLY_REFUNDED ? 'Refund' : 'Partial Refund';
            }
        }elseif($data['refund_type']==RefundHistory::TYPE_CHARGEBACK){
            $label = 'Chargeback';
        }

       return [$label,$is_reversed];
    }

    public function processSettlementCalendarReport(MerchantReportHistory $merchantReportHistory){
        $beforeLogData = [
            'action' => 'BEFORE_GENERATE_SETTLEMENT_CALENDAR_REPORT_BY_CRONJOB',
            'request' => $merchantReportHistory,
        ];
        (new ManageLogging())->createLog($beforeLogData);

        $requestData = (new Sale())->updateToAwaitingTemporaryly($merchantReportHistory);

        $language = config('constants.SYSTEM_SUPPORTED_LANGUAGE.1');
        if (isset($requestData->user_id)) {
            $user = (new Profile())->getUserById($requestData->user_id);
        }
        if (!empty($user)) {
            $language = $user->language;
        }
        app()->setLocale($language);

        $filePath = '';
        $status_msg = 'Awaiting process';
        $exception_message = '';
        $email_response = '';
        $export_response = '';
        $exception_response = '';
        $email_exception = '';

        $search = Json::decode($requestData->params, true);

        try {

            $saleTransObj = new SaleTransaction();

            $heading = $this->getMerchantSettlementReportsHeading();
            $transaction_row = $this->getMerchantSettlementReport($search);;
            $view_blade = $requestData->format == MerchantReportHistory::FORMAT_PDF ? 'new_template.pdf_blades.common' : null;
            $file_path = $this->exportReportOnServer($heading, $transaction_row, $requestData->user_id, $requestData->format, $requestData->report_type, $view_blade, $user->user_type ?? User::MERCHANT);

            if (!empty($file_path)) {
                $flag_exp = true;
                $file_path = config('app.app_frontend_url') . '/' . $file_path;
                $status_msg = 'Processed';

                if (!empty($user)) {
                    $data['user_name'] = $user->name;
                    $data['requested_date'] = ManipulateDate::getDateFormat($requestData->created_at, 'Y-m-d H:i:s');
                    $data['file_path'] = $file_path;

                    $from = config('app.SYSTEM_NO_REPLY_ADDRESS');
                    $to = $user->email;
                    $template = 'exported_report.created';
                    $language = $user->language;
                    $subject = 'exported_report_subject';
                    $attachment = '';

                    try {
                        //out_going_email
                        $this->setGNPriority(OutGoingEmail::PRIORITY_MEDIUM);
                        $this->sendEmail($data, $subject, $from, $to, $attachment, $template, $language);
                        $email_response = 'Email sent';
                    } catch (\Throwable $throwable) {
                        $email_response = "Got Exception";
                        $email_exception = Exception::fullMessage($throwable);
                    }
                }

            }

            if ($flag_exp) {
                $requestData->file_url = $file_path;
                $requestData->status = MerchantReportHistory::STATUS_PROCESSED;
                (new Sale())->updateReportByCurl($requestData);
            }

            $export_response = 'Success';

        } catch (Throwable $throwable) {
            $export_response = 'Got Exception';
            $exception_response = Exception::fullMessage($throwable);
        }

        //Create Log
        $afterLogData = [
            'action' => 'AFTER_GENERATE_SETTLEMENT_CALENDAR_REPORT_BY_CRONJOB',
            'request' => $requestData,
            'status_msg' => $status_msg,
            'file_path' => $file_path,
            'export_response' => $export_response,
            'exception_response' => $exception_response,
            'email_response' => $email_response,
            'email_exception' => $email_exception,
            'date_time' => ManipulateDate::toNow(),
        ];
        $this->createLog($afterLogData);
        return $flag_exp;
    }

    private function includeRefundWithExport($rows, $search, $refund_ids=[]){

        $payment_ids = [];

        $filters =[
            'merchant_id' => $search['merchant_id'],
            'date_range' => $search['date_range'] ?? $search['from_date'].' - '.$search['to_date'],
            'from_date' => $search['from_date'],
            'to_date' => $search['to_date'],
            'exclude_id'=> true,
            'id' => $refund_ids,
            'is_export' => true,
            'is_chunk_limit' => self::SETTLEMENT_CALENDAR_EXPORT_CHUNK_LIMIT,
            'chunk_callback' => function($refund_history)
                use (&$rows, &$payment_ids) {
                foreach ($refund_history as $transaction){

                    if(empty($transaction->sale)
                        || $transaction->transaction_state_id == TransactionState::PENDING
                        || ($transaction->transaction_state_id == TransactionState::REJECTED && !$transaction->is_reversed)) continue;

                    $common_info = [
                        $transaction->merchant_id,
                        $transaction->sale->merchant_name ?? '',
                        $transaction->sale->payment_id ?? ''
                    ];
                    $merchant_commission_percentage = $transaction->sale->merchantSale->merchant_commission_percentage ?? 0;
                    $merchant_commission_fixed = $transaction->sale->merchantSale->merchant_commission_fixed ?? 0;
                    $transaction_amount = $transaction->amount ?? 0;
                    $commission = (($transaction_amount * $merchant_commission_percentage) /100) + $merchant_commission_fixed;
                    $net_amount = $transaction_amount - $commission;

                    $data =[
                        'transaction_state_id' => $transaction->transaction_state_id,
                        'is_reversed' => $transaction->is_reversed,
                        'refund_type' => $transaction->refund_type,
                        'is_fully_refunded' => $transaction->is_fully_refunded,
                        'refund_histories_amount' => $transaction->amount,
                        'product_price' => $transaction->sale->product_price ?? 0
                    ];

                    $currency = $transaction->sale->currency_symbol ?? '';

                    $settlement_date = ManipulateDate::getDateFormat(
                        $transaction->sale->is_installment_wise_settlement == MerchantSettings::IS_INSTALLMENT_WISE_SETTLEMENT ?
                            $transaction->sale->settlement_date_merchant: $transaction->sale->merchantSale->effective_date, ManipulateDate::FORMAT_DATE_Y_m_d
                    );

                    $refund_updated_at = ManipulateDate::getDateFormat($transaction->updated_at ?? '', ManipulateDate::FORMAT_DATE_Y_m_d);
                    $refund_created_at = ManipulateDate::getDateFormat($transaction->created_at ?? '', ManipulateDate::FORMAT_DATE_Y_m_d);

// this commented code is for showing sales against all partial refunds, refund , chargeback or reversed [related to the SMP-2505]
//
//                    $payment_id = $transaction->sale->payment_id ?? '';
//                    $settlement_date = $transaction->sale->merchantSale->effective_date ?? '';



//                    if(!Arr::isAMemberOf($payment_id, $payment_ids)){
//                        $payment_ids[] = $payment_id;
//
//                        if($transaction->sale->is_installment_wise_settlement){
//                            foreach ($transaction->sale->saleSettlement as $sale_settlement){
//                                $rows[] = [
//                                    ...$common_info,
//                                    __('Sale'),
//                                    $currency.Number::format($sale_settlement->gross, 2),
//                                    $currency.Number::format($sale_settlement->merchant_commission, 2),
//                                    $currency.Number::format($sale_settlement->net_settlement, 2),
//                                    ManipulateDate::getDateFormat($sale_settlement->settlement_date_merchant ?? '', 'Y-m-d'),
//                                    $sale_settlement->created_at,
//                                    $sale_settlement->updated_at
//                                ];
//                            }
//
//                        }else {
//
//                            $commission_amount = (($transaction->sale->gross * $transaction->sale->merchantSale->merchant_commission_percentage)
//                                    / 100) + $transaction->sale->merchantSale->merchant_commission_fixed;
//                            $settlement_amount = $transaction->sale->gross - $commission_amount;
//
//                            $rows[] = [
//                                ...$common_info,
//                                __('Sale'),
//                                $currency.Number::format($transaction->sale->gross ?? 0, 2),
//                                $currency.Number::format($transaction->sale->merchant_commission ?? 0, 2),
//                                $currency.Number::format($settlement_amount ?? 0, 2),
//                                ManipulateDate::getDateFormat(!empty($settlement_date) ? $settlement_date : ($transaction->sale->created_at ?? ''), 'Y-m-d'),
//                                $transaction->sale->created_at ?? '',
//                                $transaction->sale->updated_at ?? ''
//                            ];
//                        }
//                    }


                    $fee_amount = $transaction->refund_commission + $transaction->refund_commission_fixed;
                    $settlement_amount = $transaction->net_refund_amount + $transaction->rolling_refund_amount + $fee_amount;

                    [$status, $is_reversed] = $this->getRefundStatus($data);

                    if($is_reversed){
                        $sign = "-";
                        [$status_for_reversed, $check_reversed] = $this->getRefundStatus($data, true);
                        $rows[] = [
                            ...$common_info,
                            __($status_for_reversed),
                            self::getSaleSettlementsInstallmentNumber($transaction ), //sales settlements installment for reversed
                            self::getSaleInstallmentNumber($transaction), //sale table  installment installment for reversed
                            $sign.$currency.Number::format($transaction->amount, 2),
                            $sign.$currency.Number::format($commission, 2),
                            $sign.$currency.Number::format($settlement_amount, 2),
                            ManipulateDate::greaterThanOrEqualTo($refund_created_at,$settlement_date) ? $refund_created_at : $settlement_date,
                            ManipulateDate::format(ManipulateDate::CASE_TYPE_DMY_HI, $transaction->created_at),
                            ManipulateDate::format(ManipulateDate::CASE_TYPE_DMY_HI, $transaction->updated_at),
                        ];
                    }

                    $sign = $is_reversed ? "" : "-";

                    $shouldInsertRow = true;
                    if (!$is_reversed && !ManipulateDate::greaterThanOrEqualTo($refund_created_at,$settlement_date)) {
                        $shouldInsertRow = false;
                    }

                    if ($shouldInsertRow) {
                        $rows[] = [
                            ...$common_info,
                            __($status),
                            self::getSaleSettlementsInstallmentNumber($transaction), //sales settlements  installment for chargeback
                            self::getSaleInstallmentNumber($transaction), //sales installment for chargeback
                            $sign.$currency.Number::format($transaction->amount, 2),
                            $sign.$currency.Number::format($commission, 2),
                            $sign.$currency.Number::format($settlement_amount, 2),
                            $is_reversed ? (ManipulateDate::greaterThanOrEqualTo($refund_updated_at,$settlement_date) ? $refund_updated_at : $settlement_date): $refund_created_at,
                            ManipulateDate::format(ManipulateDate::CASE_TYPE_DMY_HI, $transaction->created_at),
                            ManipulateDate::format(ManipulateDate::CASE_TYPE_DMY_HI, $transaction->updated_at)
                        ];
                    }
                }
            }
        ];

        (new RefundHistory())->getRefundHistories($filters, false, true);

        return $rows;
    }

    public function checkMccRestriction($merchantObj, $bankObj)
    {
        $errorCode = '';
        $errorMessage = '';

        if (!empty($merchantObj) && !empty($bankObj)) {

            if ($bankObj->payment_provider == config('constants.PAYMENT_PROVIDER.KUVEYT_TURK_KATILIM')) {
                $ignored_merchant_ids = (new MerchantConfiguration())->getMerchantListByEvent(MerchantConfiguration::EVENT_IGNORED_MCC_FOR_KUVEYT);

                if (Arr::isAMemberOf($merchantObj->id, $ignored_merchant_ids)) {
                    $errorCode = ApiService::API_SERVICE_MERCHANT_MCC_IS_NOT_ALLOWED;
                    $errorMessage = __('This MCC is not allowed');
                }

            }

        }

        return [$errorCode, $errorMessage];

    }

    public function prepareMerchantInfos($merchantObj)
    {
        $data['clientIp'] = $this->getClientIp();
        $data["billAddressCity"] = $merchantObj['city'] ?? '';
        $data["billAddressCountryId"] = $merchantObj['country_id'] ?? '';
        $data["billAddressLine1"] = $merchantObj['address1'] ?? '';
        $data["billAddressPostCode"] = $merchantObj['zip_code'] ?? '';
        $data["billAddressState"] = $merchantObj['license_tag'] ?? '';
        $data["brandAccountEmail"] = $merchantObj['brand_accountant_email'] ?? '';
        $data["pfSubMerchantIdentityTaxNumber"] = $merchantObj['tckn'] ?? $merchantObj['vkn'] ?? '';
        $data["bkmId"] = !empty($merchantObj->is_iks_verified) ? $merchantObj?->merchant_iks[0]?->global_merchant_id : '';
        $data["cc"] = $data["subscriber"] = '';

        if ( !empty($merchantObj['authorized_person_phone_number']) ) {
            [ $data["cc"], $data["subscriber"] ] = Phone::getCountryPhoneCode($merchantObj['authorized_person_phone_number']);
        }

        return $data;
    }

    public function prepareSettlementCalendarUIData($search, $from_admin = false) {
       $calendarData = [];
       if (!empty($search['merchant_id'])) {

           $startTimer = microtime(true);
           $writeCustomLog = $from_admin && ($search['merchant_id'] == '16722' || $search['merchant_id'] == '87965');
           if ($writeCustomLog) {
               ManageLogging::queryLog('SettlementCalendarUIQueriesNew');
               (new ManageLogging())->createLog([
                   'action' => 'SETTLEMENT_CALENDAR_TIMER_START'
               ]);
           }

           //// below method is new modified method
           $settlementCalendarService = new SettlementCalendarService();
           $settlementCalendarService->show_settle_early_modal = $from_admin;
           $settlementCalendarService->getAndPrepareCalendarAndWalletData($search);
           $this->settle_early_pre_data = $settlementCalendarService->settle_early_modal_data;
           // emptying array
           $settlementCalendarService->settle_early_modal_data = [];

           if ($writeCustomLog) {
               (new ManageLogging())->createLog([
                   'action' => 'SETTLEMENT_CALENDAR_TIMER_END',
                   'duration' => microtime(true) - $startTimer
               ]);
           }
           $startTimer = microtime(true);

           //// below commented 2 methods are prev methods
           // $transactions = $this->getByMerchantIdEffectiveDate($search);
           // $available_balances = $this->getAvailableBalance($transactions);

           // $formattedData = $this->prepareSettlementCalendarData($transactions, $available_balances, $search);

           $curDate = ManipulateDate::getSystemDateTime('Y-m-d');
           if (!empty($settlementCalendarService->settlement_calendar_data)) {
               foreach ($settlementCalendarService->settlement_calendar_data as $sDate => $calData) {
                   foreach ($calData as $key => $value) {
                       $title = $value["currency_symbol"] . CommonFunction::getFormatedAmount($value["total_amount"]);
                       if (BrandConfiguration::call([Mix::class, 'allowedDailyTransactionHistories'])) {
                           $title = $this->formatSettlementCalendarTitle($value);
                       }

                       $calendarData[] = [
                           "day_title" => __('Settlement Details'),
                           "title" => $title,
                           "description" => $from_admin ? "<table class='w-100 d-block'><tbody><tr><td class='text-muted'>" . __('Number of Successful Transactions') . "</td><td class='pr-4 pl-4'>:</td><td>" . $value["completed_transactions"] . "</td></tr></tbody></table>" : "<table class='w-100 d-block'><tbody><tr><td class='text-muted'>" . __('Available Balance After Settlement') . "</td><td class='pr-4 pl-4'>:</td><td>" . $value["currency_symbol"] . $value["available_balance"] . "</td></tr><tr><td class='text-muted'>" . __('Number of Successful Transactions') . "</td><td class='pr-4 pl-4'>:</td><td>" . $value["completed_transactions"] . "</td></tr><tr><td class='text-muted'>" . __('Number of Refunds') . "</td><td class='pr-4 pl-4'>:</td><td>" . $value["refunded_transactions"] . "</td></tr><tr><td class='text-muted'>" . __('Number of Chargebacks') . "</td><td class='pr-4 pl-4'>:</td><td>" . $value["chargebacked_transactions"] . "</td></tr><tr><td class='text-muted'>" . __('Number of Reverses') . "</td><td class='pr-4 pl-4'>:</td><td>" . $value["reverse_transactions"] . "</td></tr></tbody></table>",
                           "start" => $sDate,
                           "end" => $sDate,
                           "className" => strtotime($curDate) < strtotime($sDate) ? 'custom-blue' : '',
                           'currency_id' => $value['currency_id']
                       ];
                   }
               }
           }
           // emptying array
           $settlementCalendarService->settlement_calendar_data = [];

           if ($writeCustomLog) {
               (new ManageLogging())->createLog([
                   'action' => 'SETTLEMENT_CALENDAR_TIMER_FINISH',
                   'duration' => microtime(true) - $startTimer
               ]);
           }
       }

       return $calendarData;
    }

    private function formatSettlementCalendarTitle($value)
    {
        $settlement_text = __('S : ');
        $refund_text = __('R : ');
        $chargeback_text = __('C : ');
        $reversed_text = __('Rev : ');

        $refunds = $chargebacks = $reversed = '';
        $settlement_amount = $settlement_text . $value["currency_symbol"] . CommonFunction::getFormatedAmount($value["total_amount"]);

        if (!empty($value["total_refund_net"])) {
            $refunds = "<br> " . $refund_text . $value["currency_symbol"] . CommonFunction::getFormatedAmount($value["total_refund_net"] ?? 0);
        }

        if (!empty($value["total_chargeback_net"])) {
            $chargebacks = "<br>" . $chargeback_text  . $value["currency_symbol"] . CommonFunction::getFormatedAmount($value["total_chargeback_net"] ?? 0);
        }

        if (!empty($value["total_reverse_net"])) {
            $reversed = "<br> " . $reversed_text  . $value["currency_symbol"] . CommonFunction::getFormatedAmount($value["total_reverse_net"] ?? 0);
        }

        return $settlement_amount . $refunds . $chargebacks . $reversed;
    }

    public function settlementCalendarExport($search, $request) {

        if(!ManipulateDate::checkDatesVlidationByDays($search['from_date'],$search['to_date'], self::SETTLEMENT_CALENDAR_EXPORT_DATE_RANGE_LIMIT)){
            $status_code = ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR;
            $status_message = __("Date range cannot be more than 1 year.");
            return [$status_code, $status_message];
        }
        $status_code = ApiService::API_SERVICE_FAILED_CODE;
        $status_message = __("Your export request has been failed");

        $search['daterange'] = $search['from_date'].' - '.$search['to_date'];
        $search['is_export'] = true;
        $search['file_type'] = $request->file_type ?? MerchantReportHistory::FORMAT_XLS;

        $auth_user = Auth::user();
        $merchantReportHistory = new MerchantReportHistory();
        $mercReportData = $merchantReportHistory->getAllData($search['merchant_id'], $auth_user->id, MerchantReportHistory::RT_SETTLEMENT_CALENDAR_REPORT);

        if (Arr::count($mercReportData) > 0) {
            $status_code = ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR;
            $status_message = __('Your previous request is not processed yet');
        } else {
            $jsonParams = Json::encode($search);

            $insertData = [
                'merchant_id' => $search['merchant_id'],
                'user_id' => $auth_user->id,
                'report_type' => MerchantReportHistory::RT_SETTLEMENT_CALENDAR_REPORT,
                'format' => $search['file_type'],
                'status' => MerchantReportHistory::STATUS_PENDING,
                'params' => $jsonParams
            ];

            $response = $merchantReportHistory->insertData($insertData);

            if (!empty($response)) {
                $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
                $status_message = __('Your export request has been created, you will be notified via email');
            }
        }

        return [$status_code, $status_message];
    }

    public static function checkTransactionTypeBlocked($merchant_id, $merchant_parent_user_id = "", $user_type = "", $merchantObj = null)
    {
        $status_code = "";
        if($user_type != User::MERCHANT){
            return $status_code;
        }

        $merchant = new Merchant();
        if(!empty($merchant_id) && empty($merchantObj)){
            $merchantObj = $merchant->getMerchantById($merchant_id);
        }

        if(empty($merchantObj) && !empty($merchant_parent_user_id)){
            $merchantObj = $merchant->getMerchantByUserId($merchant_parent_user_id);
        }

        if ($user_type == User::MERCHANT && !empty($merchantObj) && $merchantObj->is_transaction_type_blocked == \common\integration\Models\Merchant::MERCHANT_TRANSACTION_TYPE_BLOCKED) {
            $status_code = ApiService::API_SERVICE_MERCHANT_B2B_AND_B2C_BLOCKED_DURING_SETTLEMENT_WITHDRAWAL_PROCESS;
        }

        return $status_code;
    }
    public static function getReceiverEmailFromParent(){
        $receiver_email = '';
           $parentMerchantObj = Session::get(GlobalMerchant::PARENT_MERCHANT_OBJ);
           if(!empty($parentMerchantObj)){
               $receiver_email = $parentMerchantObj->authorized_person_email ?? '';
           }
       return $receiver_email;
    }
    public static function isSwtichedMerchant()
    {
        $is_swtiched = false;
        if(BrandConfiguration::call([BackendMerchant::class, 'allowReportMailSendToParentMerchantForSwitchedMerchant'])){
            $is_swtiched = Session::has(GlobalMerchant::IS_SWITCHED_MERCHANT);
        }
        return $is_swtiched;
    }
    public static function getReceiverEmailFromResponseData($response_data, $auth_user_email = ''){
        $email = '';
        if(!empty($response_data) && Json::isValid($response_data)){
            $response_data = Json::decode($response_data, true);
                $email = $response_data['parent_merchant_email_for_send_mail'] ?? '';
                if(!empty($email)){
                    $log_data = [
                        'action'=>'MERCHANT_REPORT_EMAIL_SEND_LOG',
                        'origin_email' => $auth_user_email,
                        'parent_email' => $email
                    ];
                    (new ManageLogging())->createLog($log_data);
                }
        }
       return $email;
    }

    public static function setAgreementSession($merchantObj)
    {
        $merchantAgreement = new MerchantAgreement();
        $merchantAgreementObj = $merchantAgreement->findById(MerchantAgreement::MERCHANT_DIGITAL_AGREEMENT);

        $isMerchantAgreementSet = 'isMerchantAgreementSet' . $merchantObj->id;
        Session::forget($isMerchantAgreementSet);
        //added to check merchant agreement contract


        if (!empty($merchantAgreementObj)
            && isset($merchantAgreementObj->status) && $merchantAgreementObj->status
            && isset($merchantObj->is_new_merchant) && $merchantObj->is_new_merchant) {

            Session::put($isMerchantAgreementSet, $merchantAgreementObj->status);

        }
    }
    public static function setPasswordUpdatedSession($authUser)
    {
        $LastChangedPasswordSKey = 'MerchantChangedPasswordStatus' . $authUser->id;
        Session::forget($LastChangedPasswordSKey);
        $lpc_duration = \config('constants.PASSWORD_CHANGE_AFTER_MONTHS');

        if ($lpc_duration > 0) {
            $lpc_months = (new Profile())->checkPasswordChange($authUser->updated_password_at);

            if ($lpc_months >= $lpc_duration) {
                Session::put($LastChangedPasswordSKey, 1);
            }
        }
    }

    public static function getMerchantTransactionDailyAmountLimit(): string
    {
        $daily_max_amount = '99999999';

        if (config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.FP')) {
            $daily_max_amount = '9999999999';
        }

        return $daily_max_amount;
    }

    public static function getSwitchedMerchantLogData($data)
    {
        if (BrandConfiguration::call([BackendMix::class, 'isAllowMerchantGroup'])) {

            if (GlobalFunction::hasBrandSession(GlobalMerchant::IS_SWITCHED_MERCHANT) && GlobalFunction::hasBrandSession(GlobalMerchant::PARENT_MERCHANT_OBJ)) {
                $switchedParentMerchantObj = GlobalFunction::getBrandSession(GlobalMerchant::PARENT_MERCHANT_OBJ);
                $merchantAuthUserObj = GlobalFunction::getBrandSession(GlobalMerchant::PARENT_MERCHANT_AUTH_USER_OBJ);

                $data = Arr::merge($data, [
                    'is_switched_merchant' => true,
                    'switched_merchant_id' => $switchedParentMerchantObj->id ?? '',
                    'switched_merchant_auth_user_id' => $merchantAuthUserObj->id ?? '',
                    'switched_merchant_auth_user_email' => $merchantAuthUserObj->email ?? ''
                ]);
            }
        }

        return $data;
    }

    public static function isMerchantNewApiCall($request): bool
    {
        return !empty($request->is_new_api) && StrUtility::contains($request->url(), 'v2');
    }
	
	public static function checkWelcomeEmailSend($userProfileObj, $merchantObj)
	{
		$conditions = !empty($userProfileObj) &&
			BrandConfiguration::isAllowMerchantFirstActivationMail() &&
			$userProfileObj->is_welcome_email_sent == false;
		
		if(BrandConfiguration::call([BackendAdmin::class, 'allowFirstMerchantStatusChangeToActive'])){
			$conditions = $conditions &&
				@$merchantObj->status == \common\integration\Models\Merchant::STATUS_ACTIVE;
		}
		
		return $conditions;
	}

    public function getSaleInstallmentNumber($transaction)
    {
        return !empty($transaction->is_installment_wise_settlement) ? $transaction->sales_installment : "-";
    }

    public function getSaleSettlementsInstallmentNumber($transaction)
    {
        return !empty($transaction->is_installment_wise_settlement)
            ? (empty($transaction->installments_number) ? 1 : $transaction->installments_number)
            : '-';
    }

    public function exportUserAccountActivationHistory($input)
    {
        $status_code = $status_description = '';
        $data = [];
        $log_data['action'] = 'EXPORT_USER_ACCOUNT_ACTIVATION_HISTORY';
        $log_data['data'] = $input;
        try {

            $search = $this->prepareSearch($input);
            $merchantReportHistory = new MerchantReportHistory();
            $mercReportData = $merchantReportHistory->getAllData(null, $search['user_id'], MerchantReportHistory::RT_USER_ACCOUNT_ACTIVATION_HISTORY_REPORT,[MerchantReportHistory::STATUS_PENDING]);
            if (!empty($search)) {
                if (Arr::count($mercReportData) > 0) {
                    throw new \Exception('Your previous request is not processed yet');
                } else {
                    $response = $merchantReportHistory->insertData([
                        'merchant_id' => null,
                        'user_id' => $search['user_id'],
                        'report_type' => MerchantReportHistory::RT_USER_ACCOUNT_ACTIVATION_HISTORY_REPORT,
                        'format' => $input['file_type'] ?? MerchantReportHistory::FORMAT_CSV,
                        'status' => MerchantReportHistory::STATUS_PENDING,
                        'params' => Json::encode($search)
                    ]);
                    if (!empty($response)) {
                        $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
                        $status_description = "Your export request has been created, you will be notified via email";
                    } else {
                        throw new \Exception('Your export request has been failed');
                    }
                }
            } else {
                throw new \Exception('Failed');
            }
            $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
            $status_description = 'Your export request has been created, you will be notified via email';
            $data = $input;
        } catch (\Throwable $e) {
            $status_code =$e->getCode();
            $status_description = $e->getMessage();
            $log_data['error_msg'] =Exception::fullMessage($e);
        }
        $log_data['status_code'] = $status_code;
        $log_data['status_description'] = $status_description;

        (new ManageLogging())->createLog($log_data);
        return [$status_code, $status_description, $data];
    }

    public function prepareSearch($input)
    {
        $search['status'] = $input['status'] ??  [];
        $search['from_date'] = ManipulateDate::toNow()->subDays(7)->format('Y/m/d');
        $search['to_date'] = ManipulateDate::toNow()->format('Y/m/d');
        $search['date_range'] = $search['from_date'] . " - " . $search['to_date'];
        $search['user_id'] = $input['user_id'] ?? 0;
        $search['merchant_ids'] = $input['merchant_ids'] ?? [];
        $search['page_limit'] =!empty($input['page_limit']) ?  $input['page_limit'] : 10;

        if (isset($input['date_range'])) {
            $date_explode = Arr::explode(' - ', $input['date_range']);
            $search['from_date'] = $date_explode[0];
            $search['to_date'] = $date_explode[1];
            $search['date_range'] = $date_explode[0] . " - " . $date_explode[1];
            $search['daterange'] = $date_explode[0] . " - " . $date_explode[1];
        }

        $search['file_type'] = $input['file_type'] ?? MerchantReportHistory::FORMAT_CSV;

        return $search;
    }

    public function processUserAccountActivationHistoryReport($requestData)
    {
        $beforeLogData = [
            'action' => 'BEFORE_GENERATE_CUSTOM_COMMISSION_BY_CRONJOB',
            'request' => $requestData,
            'date_time' => ManipulateDate::toNow(),
        ];
        $flag_exp = false;
        $status_msg = $export_response = $file_path = $attachment = '';
        (new ManageLogging())->createLog($beforeLogData);
        try {
            $requestData = (new Sale())->updateToAwaitingTemporaryly($requestData);
            $language = 'tr';
            $user = (new Profile())->getUserById($requestData->user_id);

            if (!empty($user)) {
                $language = $user->language;
            }
            app()->setLocale($language);
            $search = Json::decode($requestData->params, true);
            $account_activation_history_data = (new UserAccountActivationHistory)->getData($search, false, $this->userAccountActivationHistorySelectedColumns());
            $header = $this->preparedUserAccountActivationHistoryHeader();
            $data = $this->preparedUserAccountActivationHistoryData($account_activation_history_data);
            $file_path = $this->exportReportOnServer($header, $data, $requestData->user_id, $requestData->format, null, null, $user->user_type ?? 2);
            if (!empty($file_path)) {
                $flag_exp = true;
                $file_path = config('app.app_frontend_url') . '/' . $file_path;
                $status_msg = 'Processed';
                if (!empty($user)) {
                    $data['user_name'] = $user->name;
                    $data['requested_date'] = CommonFunction::dateFormat($requestData->created_at);
                    $data['file_path'] = $file_path;
                    $from = \Illuminate\Support\Facades\Config::get('app.SYSTEM_NO_REPLY_ADDRESS');
                    $to = $user->email;
                    $template = 'exported_report.created';
                    $language = $user->language;
                    $subject = 'exported_report_subject';
                    try {
                        //out_going_email
                        $this->setGNPriority(OutGoingEmail::PRIORITY_MEDIUM);
                        $this->sendEmail($data, $subject, $from, $to, $attachment, $template, $language);
                        $email_response = 'Email sent';
                    } catch (\Throwable $ex) {
                        $email_response = Exception::fullMessage($ex);
                    }

                    if ($flag_exp) {
                        $requestData->daterange = $search['date_range'];
                        $requestData->file_url = $file_path;
                        $requestData->status = MerchantReportHistory::STATUS_PROCESSED;
                        (new Sale)->updateReportByCurl($requestData);
                    }
                }
            }
        } catch (\Exception $e) {
            $export_response = Exception::fullMessage($e);

        }

        $afterLogData = [
            'action' => 'AFTER_USER_ACCOUNT_ACTIVATION_HISTORY_REPORT_URL_BY_CRONJOB',
            'request' => $requestData,
            'file_path' => $file_path,
            'status_msg' => $status_msg,
            'export_response' => $export_response,
            'email_response' => $email_response,
            'date_time' => ManipulateDate::toNow(),
        ];
        (new ManageLogging())->createLog($afterLogData);
        return $flag_exp;
    }

    public function preparedUserAccountActivationHistoryHeader(){
        return [
            __('Merchant ID'),
            __('Merchant Name'),
            __('Welcome Email Sent Date'),
            __('Welcome Email Status'),
            __('Email Type')
        ];
    }

    public function preparedUserAccountActivationHistoryData($account_activation_history_data)
    {

        $prepared_data = [];
        foreach ($account_activation_history_data as $data) {
            $prepared_row_data = $this->prepareUserAccountActivationHistoryRows($data);
            Arr::push($prepared_data, $prepared_row_data);

        }
        return collect($prepared_data);
    }

    public function prepareUserAccountActivationHistoryRows($user_account_activation_history_data){

        $prepared_data['merchant_id']=$user_account_activation_history_data->merchant_id ?? '-';
        $prepared_data['name']=$user_account_activation_history_data->name ?? '-';
        $prepared_data['password_created_at']=$user_account_activation_history_data->created_at ?? '-';
        $prepared_data['status']=!empty($user_account_activation_history_data->password_created_at) ? __(UserAccountActivationHistory::MERCHANT_CLICK_STATUS[UserAccountActivationHistory::ACTIVE]) : __(UserAccountActivationHistory::MERCHANT_CLICK_STATUS[UserAccountActivationHistory::INACTIVE]);
        $prepared_data['notification_type']= __(UserAccountActivationHistory::MERCHANT_NOTIFICATION_TYPE[$user_account_activation_history_data->notification_type]) ?? '-';
        return $prepared_data;
    }

    public function getUserAccountActivationHistory($input)
    {
        $log_data['action'] = "GET_MERCHANT_ACTIVATION_REPORT_DATA";
        $status_description = '';
        $data = [];
        $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
        try {
            $data['search'] = self::prepareSearch($input);
            $data['merchants'] = (new Merchant())->getAllMetchants();
            $data['page_limit'] = $data['search']['page_limit'];
            $data['user_account_activation_history_list'] = (new UserAccountActivationHistory)->getData($data['search'],true, $this->userAccountActivationHistorySelectedColumns());
        } catch (\Throwable $th) {
            $log_data['exception'] = Exception::fullMessage($th);
            $status_code = $th->getCode();
            $status_description = $th->getMessage();
        }

        (new ManageLogging())->createLog($log_data);
        return [$status_code, $status_description, $data];
    }

    public function userAccountActivationHistorySelectedColumns()
    {
        return [
            'user_account_activation_histories.id',
            'user_account_activation_histories.user_id',
            'user_account_activation_histories.merchant_id',
            'user_account_activation_histories.status',
            'user_account_activation_histories.notification_type',
            'user_account_activation_histories.event_type',
            'user_account_activation_histories.sent_date',
            'user_account_activation_histories.sent_date',
            'user_account_activation_histories.next_process_date',
            'user_account_activation_histories.password_created_at',
            'user_account_activation_histories.user_agent',
            'user_account_activation_histories.client_ip',
            'user_account_activation_histories.created_at',
            'user_account_activation_histories.updated_at',
            'user_account_activation_histories.updated_at',
            'merchants.name',
            'merchants.id as merchant_primary_id',
            'merchants.status as merchant_status',
        ];
    }

    public function storeAccountActivationHistory($merchant)
    {
        $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
        $status_description = 'Saved successfully';
        $log_data['action'] = 'USER_ACCOUNT_HISTORY_STORE';
        $log_data['data'] = $merchant;
        try {
            if (empty($merchant)) {
                throw new \Exception('Could not find merchant');
            }
            $user_account_activation_history = new UserAccountActivationHistory();
            $prepared_data = $user_account_activation_history->prepareData($merchant);
            $data = $user_account_activation_history->createUserAccountActivationHistory($prepared_data);
            if (empty($data)) {
                throw new \Exception('Could not be saved');
            }
        } catch (\Throwable $e) {
            $status_code = ApiService::API_SERVICE_FAILED_CODE;
            $status_description = $e->getMessage() ? $e->getMessage() : __("Could not be saved");

            $log_data['error_msg'] = Exception::fullMessage($e);
        }
        $log_data['status_code'] = $status_code;
        $log_data['status_description'] = $status_description;

        (new ManageLogging())->createLog($log_data);

        return [$status_code, $status_description];
    }

    public function updateAccountActivationHistory($user_data)
    {
        $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
        $status_description = 'Successfully updated account activation history';
        $log_data['action'] = 'USER_ACCOUNT_HISTORY_UPDATE_AFTER_PASSWORD_CREATE';
        $log_data['data'] = $user_data;
        try {
            if (empty($user_data)) {
                throw new \Exception('Could not find account activation history data');
            }
            $user_account_activation_history_data = [
                'status' => UserAccountActivationHistory::ACTIVE,
                'password_created_at' => ManipulateDate::getSystemDateTime(ManipulateDate::FORMAT_DATE_Y_m_d_H_i_s),
            ];
            $user_account_activation_history_update = (new UserAccountActivationHistory)->updateUserAccountActivationHistory($user_account_activation_history_data, $user_data->id);
            if (empty($user_account_activation_history_update)) {
                throw new \Exception('Could not be updated account activation history');
            }
        } catch (\Throwable $e) {
            $status_code = $e->getCode() ? $e->getCode() : ApiService::API_SERVICE_FAILED_CODE;
            $status_description = $e->getMessage() ? $e->getMessage() : "Could not be updated account activation history";
            $log_data['error_msg'] = Exception::fullMessage($e);
        }
        $log_data['status_code'] = $status_code;
        $log_data['status_description'] = $status_description;

        (new ManageLogging())->createLog($log_data);

        return [$status_code, $status_description];
    }
}
