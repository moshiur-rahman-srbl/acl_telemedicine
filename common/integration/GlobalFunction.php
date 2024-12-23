<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 7/7/2020
 * Time: 6:32 PM
 */

namespace common\integration;


use App\Exports\ExportExcel;
use App\Http\Controllers\Traits\CommonLogTrait;
use App\Http\Controllers\Traits\OTPTrait;
use App\Models\AdminMakerChecker;
use App\Models\Bank;
use App\Models\CCPayment;
use App\Models\Company;
use App\Models\Country;
use App\Models\CurrenciesSettings;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\DepositeMethod;
use App\Models\DPL;
use App\Models\DPLSetting;
use App\Models\HolidaySetting;
use App\Models\Integrator;
use App\Models\Merchant;
use App\Models\MerchantCommission;
use App\Models\MerchantPosCommission;
use App\Models\MerchantPosPFSetting;
use App\Models\MerchantSale;
use App\Models\OutgoingCurlRequestRecords;
use App\Models\PaymentProvider;
use App\Models\PaymentReceiveOption;
use App\Models\PaymentRecOption;
use App\Models\PfHistory;
use App\Models\Pos;
use App\Models\POSRiskyCountry;
use App\Models\Profile;
use App\Models\Purchase;
use App\Models\PurchaseRequest;
use App\Models\Reason;
use App\Models\Receive;
use App\Models\RefundHistory;
use App\Models\RollingBalance;
use App\Models\Sale;
use App\Models\SaleBilling;
use App\Models\SaleIntegrator;
use App\Models\SaleRecurring;
use App\Models\SaleRecurringHistory;
use App\Models\SalesPFRecords;
use App\Models\Sector;
use App\Models\Send;
use App\Models\ServiceType;
use App\Models\Settlement;
use App\Models\TemporaryPaymentRecord;
use App\Models\TmpSaleAutomation;
use App\Models\Transaction;
use App\Models\TransactionState;
use App\Models\UserProfile;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Models\WithdrawalOperation;
use App\User;
use App\Utils\CommonFunction;
use Carbon\Carbon;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Backend\BackendCcpayment;
use common\integration\Brand\Configuration\Backend\BackendMix;
use common\integration\Brand\PaymentFeatureTrait;
use common\integration\CashInOutProcess\CashInOutManager;
use common\integration\Models\CommercialCardCommission;
use common\integration\Models\MerchantIKS;
use common\integration\Models\MerchantPosAcquiringSetting;
use common\integration\Models\OutGoingEmail;
use common\integration\Models\PurchaseRequestData;
use common\integration\Models\ServiceCredential;
use common\integration\Notification\Email\EmailNotification;
use common\integration\Payment\Card;
use common\integration\Payment\Pf;
use common\integration\Payment\Providers\QnbFinans;
use common\integration\Payment\Providers\Vakif;
use common\integration\Utility\Arr;
use common\integration\Utility\Curl;
use common\integration\Utility\Encode;
use common\integration\Utility\Helper;
use common\integration\Utility\Ip;
use common\integration\Utility\Json;
use common\integration\Utility\Str;
use common\integration\Utility\Number;
use common\integration\Utility\Xml;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\BlockTimeSettings;
//use Illuminate\Support\Str;
use App\Http\Controllers\Traits\SendEmailTrait;
use App\Models\BtoC;
use Matrix\Exception;
use common\integration\BrandConfiguration;
use common\integration\BrandCacheConfiguration;
use App\Http\Controllers\Traits\NotificationTrait;
use App\Models\ImportedTransaction;
use common\integration\CommonNotification;
use App\Models\UserSetting;
use common\integration\GlobalUser;
use common\integration\Traits\HttpServiceInfoTrait;


class GlobalFunction
{
    use CommonLogTrait,SendEmailTrait, NotificationTrait;

    const WALLET = "wallet";
    const CUZDAN = "cuzdan"; 
    const MERKEZ = "merkez"; 
    const ISYERI = "isyeri"; 
    public static $max_length_of_kuveyt_pf_description_param = 40;

    public static function isTestTransaction(){

        // "TEST" means  0.1 , For any other valuue, it  is actual amount
        if (config('app.APPLICATION_STATE') == 'TEST') {

            return true;
        }
        return false;
    }

    public static function interchangeOriginalPosDataAndBypassData($bypass_response_data, $original_installment_data)
    {
        if (!empty($bypass_response_data) && !empty($original_installment_data)) {
            foreach ($bypass_response_data as $k => &$bypass_installment_data) {
                $bypass_installment = $bypass_installment_data["installments_number"];
                $found_installment = false;
                foreach ($original_installment_data as &$installment_data) {
                    $installment = $installment_data["installments_number"];
                    if ($installment == $bypass_installment) {
                        $found_installment = true;
                        $bypass_installment_data["pos_id"] = $installment_data["pos_id"];
                        $bypass_installment_data["hash_key"] = $installment_data["hash_key"];
                    }
                }
                if(! $found_installment ){
                    unset($bypass_response_data[$k]);
                }
            }
        }

        return $bypass_response_data;
    }

    public static function get3dPaymentSource(){

        return [
            CCPayment::PAYMENT_SOURCE_PAID_BY_CC_3D_BRANDING,
            CCPayment::PAYMENT_SOURCE_WHITE_LABEL_3D,
            CCPayment::PAYMENT_SOURCE_REDIRECT_WHITE_LABEL_3D,
            CCPayment::PAYMENT_SOURCE_DPL_3D,
            CCPayment::PAYMENT_SOURCE_MP_3D,
            CCPayment::PAYMENT_SOURCE_PAY_BY_CARDTOKEN_3D,
            CCPayment::PAYMENT_SOURCE_PAY_BY_MARKETPLACE_3D,
            CCPayment::PAYMENT_SOURCE_PAY_BY_WIX_3D,
            CCPayment::PAYMENT_SOURCE_PAY_BY_REDIRECT_DIRECTLY,
            CCPayment::PAYMENT_SOURCE_ONE_PAGE_PAYMENT_DPL_3D,
            CCPayment::PAYMENT_SOURCE_BILL_PAYMENT_PAY_3D,
            CCPayment::PAYMENT_SOURCE_WALLET_PAYMENT_PAY_3D,
            CCPayment::PAYMENT_SOURCE_TENANT_3D,
            CCPayment::PAYMENT_SOURCE_POINT_PAYMENT_3D,
            CCPayment::PAYMENT_SOURCE_BRAND_IMPORT_PAYMENT_3D,
        ];

        //return [1,5,7,9,11,13,15,21,23, 24];


    }

    public static function get2dPaymentSource(){

        return [
            CCPayment::PAYMENT_SOURCE_PAID_BY_CC_2D_BRANDING,
            CCPayment::PAYMENT_SOURCE_WALLET_PAYMENT,
            CCPayment::PAYMENT_SOURCE_WHITE_LABEL_2D,
            CCPayment::PAYMENT_SOURCE_REDIRECT_WHITE_LABEL_2D,
            CCPayment::PAYMENT_SOURCE_DPL_2D,
            CCPayment::PAYMENT_SOURCE_MP_2D,
            CCPayment::PAYMENT_SOURCE_PAY_BY_CARDTOKEN_2D,
            CCPayment::PAYMENT_SOURCE_PAY_BY_MARKETPLACE_2D,
            CCPayment::PAYMENT_SOURCE_PAY_BY_WIX_2D,
            CCPayment::PAYMENT_SOURCE_FASTPAY_WALLET_MOBILE_QR_PAYMENT,

            CCPayment::PAYMENT_SOURCE_OXIVO_PAYMENT,
            CCPayment::PAYMENT_SOURCE_ONE_PAGE_PAYMENT_DPL_2D,
            CCPayment::PAYMENT_SOURCE_FASTPAY_SALE_MOBILE_WALLET,
            CCPayment::PAYMENT_SOURCE_FASTPAY_SALE_MOBILE_NON_SECURE,
            CCPayment::PAYMENT_SOURCE_FASTPAY_SALE_MOBILE_3D_SECURE,
            CCPayment::PAYMENT_SOURCE_DENIZ_DCC_CURRENCY_CONVERSION,
            CCPayment::PAYMENT_SOURCE_RECURRING_PAYMENT,
            CCPayment::PAYMENT_SOURCE_BILL_PAYMENT_PAY_2D,
            CCPayment::PAYMENT_SOURCE_WALLET_PAYMENT_PAY_2D,
            CCPayment::PAYMENT_SOURCE_INSURANCE_PAYMENT_VIA_IDENTITY_2D,
            CCPayment::PAYMENT_SOURCE_TEST_TRANSACTION_PAY_2D,
            CCPayment::PAYMENT_SOURCE_TENANT_2D,
            CCPayment::PAYMENT_SOURCE_POINT_PAYMENT_2D,
            CCPayment::PAYMENT_SOURCE_BRAND_IMPORT_PAYMENT_2D,
        ];


    }

    public static function is_payment_3d($payment_source){

        $payment_source_3d_list = self::get3dPaymentSource();
        return in_array($payment_source, $payment_source_3d_list);
    }

    public static function setBrandSession($key, $value, $ref = null){

        if (!empty($ref)){
            $key = $key.'_'.$ref;
        }
        session()->put($key, $value);

    }

    public static function isSameReferer(){
        $status = true;
        $referrer = request()->header('referer');
        if (!empty($referrer) ){
            $referrerArray  = parse_url($referrer);
            if (isset($referrerArray['host'])){
                $host = $referrerArray['host'];
                $selfReferrerArray  = parse_url(config('app.url'));
                $selfHost = $selfReferrerArray['host'];

                $custome_hosts = [
                    Self::WALLET,
                    Self::CUZDAN,
                    Self::MERKEZ,
                    Self::ISYERI,
                ];
               
                foreach($custome_hosts as $custome_host ){
                    if(str_contains($host, $custome_host) == true){
                        return $status;
                    }
                }
                
                // if((str_contains($host, self::WALLET) == true) || (str_contains($host, self::CUZDAN) == true))
                // {
                //     return $status;
                // }

                if ($host != $selfHost){
                    $status = false;

                }

            }
        }
        return $status;
    }

    public static function getBankTestCredentials($actionUrl, $clientId, $user_name, $password,$storekey,
                                            $api_user_name,$api_password, $bank_code, $action_type){

//        if (self::isTestTransaction()){
//            if ($bank_code == config('constants.BANK_CODE.IS_BANK') || $bank_code == config('constants.BANK_CODE.IS_BANK_PF')){
//                $clientId = '700655000300';
//                $user_name = 'apiuser';
//                $password = 'Test123!';
//                $api_user_name = 'apiuser';
//                $api_password = 'Test123!';
//                $storekey = 'TRPS0300';
//                $actionUrlArray = [
//                    '3d' => "https://entegrasyon.asseco-see.com.tr/fim/est3Dgate",
//                    '2d' => "https://entegrasyon.asseco-see.com.tr/fim/api",
//                    'order_status' => "https://entegrasyon.asseco-see.com.tr/fim/api",
//                    'refund' => "https://entegrasyon.asseco-see.com.tr/fim/api"
//                ];
//                $actionUrl = $actionUrlArray[$action_type];
//
//            }elseif ($bank_code == config('constants.BANK_CODE.ziraatbank') || $bank_code == config('constants.BANK_CODE.ziraatbank2')
//                || $bank_code == config('constants.BANK_CODE.ziraat_weekly')){
//                $clientId = '190100000';
//
//                $user_name = 'apiuser';
//                $password = 'Test123!';
//                $api_user_name = 'apiuser';
//                $api_password = 'Test123!';
//                $storekey = '123456';
//                $actionUrlArray = [
//                    '3d' => "https://entegrasyon.asseco-see.com.tr/fim/est3Dgate",
//                    '2d' => "https://entegrasyon.asseco-see.com.tr/fim/api",
//                    'order_status' => "https://entegrasyon.asseco-see.com.tr/fim/api",
//                    'refund' => "https://entegrasyon.asseco-see.com.tr/fim/api"
//                ];
//                $actionUrl = $actionUrlArray[$action_type];
//            }
//
//        }

        return [$actionUrl, $clientId,$user_name,$password,$storekey, $api_user_name,$api_password];
    }

    public static function hasBrandSession($key, $ref = null)
    {
        if (!empty($ref)) {
            $key = $key . '_' . $ref;
        }
        return session()->has($key);
    }

    public static function unsetBrandSession($key, $ref = null){

        if (!empty($ref)){
            $key = $key.'_'.$ref;
        }
        session()->forget($key);
    }

    public static function getBrandSession($key, $ref = null){

        if (!empty($ref)){
            $key = $key.'_'.$ref;
        }
        return session()->get($key);
    }

    public function isSameBank($dbBank, $constantBank, $length){

        return Bank::isSame($dbBank,$constantBank,$length);

    }

    public function isolateBankCode($dbBank){
        $main_bank_code = '';
        $bank_code_postfix = '';
        $bank_codes = config('constants.BANK_CODE');
        foreach ( $bank_codes as $bank_code){

            if ($this->isSameBank($dbBank, $bank_code, strlen($bank_code))){
                $main_bank_code = $bank_code;
                $bank_code_postfix = substr($dbBank, strlen($bank_code), strlen($dbBank));
            }
        }

        return [$main_bank_code, $bank_code_postfix];
    }
    public function managePFRecords($merchantObj, $bankObj, $posObj, $card_no, $is_3d = false, $order_id = '', $subMerchantPFObj = null, $shouldStore = false, $find_in_sale_pf_records = false, $saleObj = null, $client_identity_number = null, $purchaseRequest = null){

        //to test insurance payment please uncomment the pf mentioned under every bank's scope
        //
        $pfArr = [];
        $brand_name = '';
        $bank_page_display_pf_name = '';
        $group_id_pf_name = '';


        $is_merchant_send_pf = 0;
        $is_send_tckn_vkn = 1;

        if($find_in_sale_pf_records){
            //only consider version 2 pf
            $pfArr = $this->getPfFromSalePfRecord($order_id, $bankObj);
            //if pf found return pf else go with the regular flow
            if(!empty($pfArr)){
                return $pfArr;
            }else{

                if( ! empty($saleObj)){
                    if(empty($merchantObj)){
                        //from refund and postAuth no merchantObj, so get merchantObj with  sale merchant id
                        $merchantObj = (new Merchant())->getMerchant($saleObj->merchant_id);
                    }

                    if(empty($posObj)) {
                        $posObj = (new Pos())->findByPosId($saleObj->pos_id);
                    }

                    if(empty($bankObj) && ! empty($posObj)) {
                        $bankObj = (new Bank())->findBankByID($posObj->bank_id);
                    }

                    if(empty($card_no)){
                        $card_no = $saleObj->credit_card_no ?? "";
                    }

                    $is_3d = GlobalFunction::is_payment_3d($saleObj->payment_source);
                }
                if (empty($saleObj) || empty($posObj) || empty($bankObj)){
                    return $pfArr;
                }
            }
        }

        $sale_pf_record_data = [
            'order_id' => $order_id,
            'identity_nin' => '',
            'sub_merchant_id' => '',
            'pf_merchant_id' => $merchantObj->id ?? "",
            'pf_merchant_name' => '',
            'merchant_id' => $merchantObj->id ?? "",
            'client_identity_number' => $client_identity_number
        ];

        if(!empty($merchantObj) ){
           $is_merchant_send_pf = $merchantObj->send_pf_records;
        }

       if(!empty($merchantObj) && $merchantObj->linked_pf_merchant_id > 0){
          $merchantObjTemp = (new Merchant())->getActiveMerchantById($merchantObj->linked_pf_merchant_id);
          $merchantObj = !empty($merchantObjTemp) ? $merchantObjTemp : $merchantObj;
       }

        if($bankObj->payment_provider == config('constants.PAYMENT_PROVIDER.PAYMIX')){

            return $this->getPaymixPf($merchantObj, $posObj->id, $bankObj , $card_no, $subMerchantPFObj);

        }

        $ispfSettings = false;
        //PF record
            $nin = config('constants.SUBMERCHANT_INFO.NIN');
            $remote_sub_merchant_id = $merchant_id = config('constants.SUBMERCHANT_INFO.ID');
            $name = $brand_name = config('constants.SUBMERCHANT_INFO.NAME');
            $mcc = config('constants.SUBMERCHANT_INFO.MCC');
            $postal_code = config('constants.SUBMERCHANT_INFO.POSTAL_CODE');
            $city = config('constants.SUBMERCHANT_INFO.CITY');
            $iso_country_code = config('constants.SUBMERCHANT_INFO.ISO_COUNTRY_CODE');
            $site_url = config('constants.SUBMERCHANT_INFO.SITE_URL');
            // $remote_sub_merchant_id = $merchant_id;
            $visa_sub_merchant_id = config('constants.SUBMERCHANT_INFO.VISA_SUB_MERCHANT_ID');
            $visa_pf_id = config('constants.SUBMERCHANT_INFO.VISA_PF_ID');
            $visa_mrc_pf_id = config('constants.SUBMERCHANT_INFO.VISA_MRC_PF_ID');
            $facilitator_id = '';



        if (!empty($posObj) && $posObj->send_pf_records == Pos::SEND_PF_RECORDS
            && !empty($merchantObj)
            && $is_merchant_send_pf == Pos::SEND_PF_RECORDS && empty($subMerchantPFObj)) {

            $nin = !empty($merchantObj->vkn) ? $merchantObj->vkn : $merchantObj->tckn;
            $merchant_id = $merchantObj->id;
            $name = $merchant_name = $merchantObj->name;
            $mcc = $merchantObj->mcc;
            $postal_code = $merchantObj->zip_code;
            $city = $merchantObj->city;
            $iso_country_code = $merchantObj->iso_country_code;
            $site_url = $merchantObj->site_url;

            if (!empty($merchantObj->remote_sub_merchant_id)){
                $remote_sub_merchant_id  = $merchantObj->remote_sub_merchant_id;
            }else{
                $remote_sub_merchant_id = $merchant_id;
            }

            $merchantPosPFSetting = new MerchantPosPFSetting();
            $merchantPosPFSettingObj = $merchantPosPFSetting->findByMerchantIdAndPosId($merchantObj->id, $posObj->id, MerchantPosPFSetting::STATUS_ACTIVE);
            if (!empty($merchantPosPFSettingObj)){
                $remote_sub_merchant_id = $merchantPosPFSettingObj->sub_merchant_id;
                $ispfSettings = true;
                $is_send_tckn_vkn = $merchantPosPFSettingObj->is_send_tckn_vkn ?? 1;
                $bank_page_display_pf_name = !empty($merchantPosPFSettingObj->bank_page_display_pf_name) ? $merchantPosPFSettingObj->bank_page_display_pf_name :  '';
                $group_id_pf_name = !empty($merchantPosPFSettingObj->is_send_pf_group_id) && !empty($merchantPosPFSettingObj->group_id_pf_name) ? $merchantPosPFSettingObj->group_id_pf_name :  '';
            }
        }

        if(!empty($subMerchantPFObj)){
            $merchant_id = $subMerchantPFObj->merchant_id;
            $remote_sub_merchant_id = $subMerchantPFObj->remote_sub_merchant_id;
            $merchant_id = $remote_sub_merchant_id;
            $name = $subMerchantPFObj->name;
            $mcc = $subMerchantPFObj->mcc;
            $postal_code = $subMerchantPFObj->post_code;
            $city = $subMerchantPFObj->city;
            $iso_country_code = $subMerchantPFObj->iso_country_code;
            $site_url =$subMerchantPFObj->url;
            $nin = !empty($subMerchantPFObj->vkn) ? $subMerchantPFObj->vkn :$subMerchantPFObj->tckn;
            $merchantObj->tckn = $nin;
            $merchantObj->vkn = $nin;
            $merchantObj->remote_sub_merchant_id = $remote_sub_merchant_id;
            $posObj->remote_sub_merchant_id = $remote_sub_merchant_id;
        }
/*
        if($bankObj->code == config('constants.BANK_CODE.DUMMY_BANK_CODE')){
            $pfArr['nin'] = $nin??null;
            $pfArr['merchant_id'] = $merchant_id??null;
            $pfArr['remote_sub_merchant_id'] = $remote_sub_merchant_id??null;
            $pfArr['name'] = $name??null;
            $pfArr['mcc'] = $mcc??null;
            $pfArr['postal_code'] = $postal_code??null;
            $pfArr['city'] = $city??null;
            $pfArr['iso_country_code'] = $iso_country_code??null;
            $pfArr['site_url'] =$site_url??null;
            $pfArr['visa_sub_merchant_id'] = $visa_sub_merchant_id??null;
            $pfArr['visa_pf_id'] = $visa_pf_id??null;
            $pfArr['visa_mrc_pf_id'] = $visa_mrc_pf_id??null;
            $pfArr['facilitator_id'] = $facilitator_id??null;
            return $pfArr;

        }

*/

        if( ($this->isSameBank( $bankObj->code , config('constants.BANK_CODE.IS_BANK') , 6 )
            || $this->isSameBank( $bankObj->code , config('constants.BANK_CODE.ALTERNATIF_BANK'),strlen(config('constants.BANK_CODE.ALTERNATIF_BANK'))))
            &&  ($posObj->send_pf_records == Pos::SEND_PF_RECORDS)){

            if($this->isSameBank( $bankObj->code , config('constants.BANK_CODE.IS_BANK') , 6 )){
              if(!$ispfSettings) {
                  $remote_sub_merchant_id = $merchant_id;
              }

              //$submerchantName = $merchant_id;
               $submerchantName = Str::truncate($name, 20); // substr($name,0,20);

                if(!empty($bank_page_display_pf_name)){
                    $submerchantName = Str::truncate($bank_page_display_pf_name, 20);
                }

            }elseif ($this->isSameBank( $bankObj->code , config('constants.BANK_CODE.ALTERNATIF_BANK'),strlen(config('constants.BANK_CODE.ALTERNATIF_BANK')))){

                $submerchantName =  $name;
                $remote_sub_merchant_id = $nin;

            }else{

                $submerchantName = $name;

            }

            $pfArr['SUBMERCHANTNAME'] = $submerchantName;
            $pfArr['SUBMERCHANTID'] = $remote_sub_merchant_id;
            $pfArr['SUBMERCHANTPOSTALCODE'] = $postal_code;
            $pfArr['SUBMERCHANTCITY'] = $city;
            $pfArr['SUBMERCHANTCOUNTRY'] = $iso_country_code;
            $pfArr['SUBMERCHANTNIN'] = $nin;
            $pfArr['SUBMERCHANTURL'] = $site_url;
            $pfArr['SUBMERCHANTMCC'] = $mcc;
            $pfArr['GROUPID'] = $name;

            $pfArr = $this->getRandomPfRecord($merchantObj, $pfArr, $bankObj->code, $card_no, $order_id);

            if($this->isSameBank( $bankObj->code , config('constants.BANK_CODE.IS_BANK') , 6 )){
                $pfArr['GROUPID'] =  $brand_name.'/'.$pfArr['GROUPID'];
                $pfArr['SUBMERCHANTURL'] = Str::replace(['https://', 'http://'], '', $pfArr['SUBMERCHANTURL']);
            }

            //$pfArr = Pf::nestpayInsurancePf();


        }else if( $this->isSameBank( $bankObj->code , config('constants.BANK_CODE.HALK_BANK') , 8 )) {

            $sub_merchant_id = BrandConfiguration::call([PaymentFeatureTrait::class, 'shouldApplyMerchantIdForSubMerchantIdOnPFRecords']) ? $merchant_id : $name;

            $pfArr = [
                'SUBMERCHANTNAME' => $name,
                'SUBMERCHANTID' => $sub_merchant_id,
                'SUBMERCHANTPOSTALCODE' => $postal_code,
                'SUBMERCHANTCITY' => $city,
                'SUBMERCHANTCOUNTRY' => $iso_country_code,
                'SUBMERCHANTNIN' => $nin,
                'SUBMERCHANTURL' => $site_url,
                'SUBMERCHANTMCC' => $mcc,
                'GROUPID' => $name,
            ];

        } else if( $this->isSameBank( $bankObj->code , config('constants.BANK_CODE.FINANS_BANK') , 10 ) ||
                $this->isSameBank( $bankObj->code , config('constants.BANK_CODE.FINANS_KATILIM_WEEKLY') , 4 ) ) {

                $subMerchantId = $merchant_id;
                $subMerchantName = $name;

            if (!empty($merchantObj->is_iks_verified)) {
                $iksMerchant = (new MerchantIKS())->findByMerchantId($merchantObj->id);
                if(!empty($iksMerchant->global_merchant_id)){
                    $subMerchantId = $iksMerchant->global_merchant_id;
                }
            }

            if(BrandConfiguration::call([PaymentFeatureTrait::class, 'shouldApplyIsoToCountryCodeOnPFRecords'])) {
                $country_code = $this->countryToIso('', true, $iso_country_code);
                if(!empty($country_code)){
                    $iso_country_code = $country_code;
                }
            }

            $pfArr = [
                'SUBMERCHANTNAME' => $subMerchantName,
                'SUBMERCHANTID' => $subMerchantId,
                'SUBMERCHANTPOSTALCODE' => $postal_code,
                'SUBMERCHANTCITY' => $city,
                'SUBMERCHANTCOUNTRY' => $iso_country_code,
                'SUBMERCHANTNIN' => $nin,
                'SUBMERCHANTURL' => $site_url,
                'SUBMERCHANTMCC' => $mcc,
                'SUBMERCHANTFACILITATORID' => '20000012'
            ];

        }else if( $this->isSameBank( $bankObj->code , config('constants.BANK_CODE.AK_BANK') , 6 )) {

            if(!empty($bank_page_display_pf_name)){
                $name = $bank_page_display_pf_name;
            }

            $pfArr = [
                'SUBMERCHANTNAME' => $name,
                'SUBMERCHANTID' => $merchant_id,
                'SUBMERCHANTPOSTALCODE' => $postal_code,
                'SUBMERCHANTCITY' => $city,
                'SUBMERCHANTCOUNTRY' => $iso_country_code,
                'SUBMERCHANTMCC' => $mcc,
            ];

            $pfArr['VISASUBMERCHANTID'] = $visa_sub_merchant_id;
            $pfArr['VISAPFID'] = $visa_pf_id;

            if( Bank::isAkbank($bankObj->payment_provider) && BrandConfiguration::call([PaymentFeatureTrait::class, 'isBrandForAkbankProvider'])) {
                $pfArr['subMerchantId'] = Str::toString($merchant_id);
                if (BrandConfiguration::call([PaymentFeatureTrait::class, 'shouldApplyRemoteSubMerchantIdForAkbankProvider']))
                {
                    $pfArr['subMerchantId'] = Str::toString($remote_sub_merchant_id);
                }
            }

        }else if( $this->isSameBank( $bankObj->code , config('constants.BANK_CODE.SEKERBANK') , 9 )) {

            $pfArr = [
                'SUBMERCHANTNAME' => $name,
                'SUBMERCHANTID' => $merchant_id,
                'SUBMERCHANTPOSTALCODE' => $postal_code,
                'SUBMERCHANTCITY' => $city,
                'SUBMERCHANTCOUNTRY' => $iso_country_code,
                'SUBMERCHANTMCC' => $mcc,
                'SUBMERCHANTNUMBER' => $nin
            ];

        }else if( $this->isSameBank( $bankObj->code , config('constants.BANK_CODE.TURK_EKONOMI') , 11 )) {

            $pfArr = [
                'SUBMERCHANTNAME' => $name,
                'SUBMERCHANTID' => $merchant_id,
                'SUBMERCHANTPOSTALCODE' => $postal_code,
                'SUBMERCHANTCITY' => $city,
                'SUBMERCHANTCOUNTRY' => $iso_country_code,
                'SUBMERCHANTMCC' => $mcc,
                'SUBMERCHANTNUMBER' => $nin
            ];

            $pfArr['VISASUBMERCHANTID'] = $visa_sub_merchant_id;
            $pfArr['VISAPFID'] = $visa_pf_id;

        }else if( $this->isSameBank( $bankObj->code , config('constants.BANK_CODE.ANADOLU_BANK') , 11 )) {

            $pfArr = [
                'SUBMERCHANTNAME' => $name,
                'SUBMERCHANTID' => $nin,
                'SUBMERCHANTPOSTALCODE' => $postal_code,
                'SUBMERCHANTCITY' => $city,
                'SUBMERCHANTCOUNTRY' => $iso_country_code,
                'SUBMERCHANTMCC' => $mcc,
            ];

        }else if( ($this->isSameBank( $bankObj->code , config('constants.BANK_CODE.ZIRAAT_BANK') , 6 ))
                &&  ($posObj->send_pf_records == Pos::SEND_PF_RECORDS)) {

            if (!empty($merchantPosPFSettingObj)) {
                $pfArr['SUBMERCHANTNUMBER'] = $remote_sub_merchant_id;
             //   $pfArr['SUBMERCHANTNUMBER'] = Str::fill($remote_sub_merchant_id, 15,'0',true, STR_PAD_LEFT);
            }

            $pfArr['SUBMERCHANTNAME'] = $nin;

            if(!empty($bank_page_display_pf_name)){

                $pfArr['SUBMERCHANTID'] = substr($bank_page_display_pf_name,0,25) ;

            }else{

                $pfArr['SUBMERCHANTID'] = substr($name,0,25) ;

            }


            $pfArr = $this->getRandomPfRecord($merchantObj, $pfArr, $bankObj->code, $card_no, $order_id);

            if(! $is_send_tckn_vkn && !empty($pfArr['SUBMERCHANTNAME'])){
                $pfArr['SUBMERCHANTNAME'] = "";
            }

            //$pfArr['SUBMERCHANTID'] = Str::fill($name, 15,'0',true, STR_PAD_LEFT) ;

        }else if(($this->isSameBank( $bankObj->code , config('constants.BANK_CODE.FIBABANKA') , 4 ))
            &&  ($posObj->send_pf_records == Pos::SEND_PF_RECORDS)){

            $sub_merchant_id_length = 10 + Str::len($remote_sub_merchant_id);

            $pfArr['SUBMERCHANTNAME'] = $name;
            $pfArr['SUBMERCHANTID'] = Str::fill($remote_sub_merchant_id, $sub_merchant_id_length, 0,true, STR_PAD_LEFT);
            $pfArr['SUBMERCHANTPOSTALCODE'] = $postal_code;
            $pfArr['SUBMERCHANTCITY'] = $city;
            $pfArr['SUBMERCHANTCOUNTRY'] = $iso_country_code;
            $pfArr['SUBMERCHANTMCC'] = $mcc;

        }else if( ($this->isSameBank( $bankObj->code , config('constants.BANK_CODE.MSU') , 3 ))
          &&  ($posObj->send_pf_records == Pos::SEND_PF_RECORDS)) {

            $pfArr['ISBANK.SUBMERCHANTNAME'] = $name;
            $pfArr['ISBANK.SUBMERCHANTID'] = $remote_sub_merchant_id;
            $pfArr['ISBANK.SUBMERCHANTPOSTALCODE'] = $postal_code;
            $pfArr['ISBANK.SUBMERCHANTCITY'] = $city;
            $pfArr['ISBANK.SUBMERCHANTCOUNTRY'] = $iso_country_code;
            $pfArr['ISBANK.SUBMERCHANTNIN'] = $nin;
            $pfArr['ISBANK.SUBMERCHANTURL'] = $site_url;
            $pfArr['ISBANK.SUBMERCHANTMCC'] = $mcc;

        }else if($bankObj->payment_provider == config('constants.PAYMENT_PROVIDER.VAKIF')){

            $merchant_type = Vakif::REMOTE_MERCHANT_TYPE_DEFAULT;
            if(($posObj->send_pf_records == Pos::DO_NOT_SEND_PF_RECORDS)) {

                $nin = config('constants.SUBMERCHANT_INFO.NIN');
                $subMerchantId = config('constants.SUBMERCHANT_INFO.ID');

            }else {

                if ($ispfSettings) {
                    $subMerchantId = $remote_sub_merchant_id;
                    $nin = !empty($merchantObj->vkn) ? $merchantObj->vkn : $merchantObj->tckn;

                } else if (!empty($merchantObj->remote_sub_merchant_id)) {
                    $nin = !empty($merchantObj->vkn) ? $merchantObj->vkn : $merchantObj->tckn;
                    $subMerchantId = $merchantObj->remote_sub_merchant_id;

                } else if (!empty($posObj->remote_sub_merchant_id)) {
                    $nin = config('constants.SUBMERCHANT_INFO.NIN');
                    $subMerchantId = $posObj->remote_sub_merchant_id;
                }
            }

        $pfArr['Identity'] = $nin;
        $pfArr['SUBMERCHANTID'] = $subMerchantId??config('constants.SUBMERCHANT_INFO.ID');

        if(!empty($bank_page_display_pf_name)){
            $pfArr['Extract'] = $bank_page_display_pf_name;
            $pfArr['TerminalNo'] = $bankObj->terminal_id ?? '';
            $merchant_type = Vakif::REMOTE_MERCHANT_TYPE_EXTRACT;
        }


            if ($is_merchant_send_pf == Pos::DO_NOT_SEND_PF_RECORDS || $posObj->send_pf_records == Pos::DO_NOT_SEND_PF_RECORDS) {
                $pfArr['TerminalNo'] = $bankObj->terminal_id ?? '';
                $merchant_type = Vakif::REMOTE_MERCHANT_TYPE_EXTRACT;
            }

        if(BrandConfiguration::call([PaymentFeatureTrait::class, 'isBrandWhichDoesntWantMerchantTypeManipulationForVakif'])){
            $merchant_type = Vakif::REMOTE_MERCHANT_TYPE_DEFAULT;
        }

        $pfArr["MerchantType"] = $merchant_type;


        // $pfArr = Pf::vakifInsurancePf();

        $sale_pf_record_data = [
            'order_id' => $order_id,
            'identity_nin' => $pfArr['Identity'],
            'sub_merchant_id' => $pfArr['SUBMERCHANTID'] ,
            'pf_merchant_id' => $merchantObj->id,
            'pf_merchant_name' => '',
            'bank_page_display_pf_name' => $bank_page_display_pf_name,
            'merchant_type' => $merchant_type,
        ];

        }else if($bankObj->payment_provider == config('constants.PAYMENT_PROVIDER.YAPI_VE_KREDI')){

            if (($posObj->send_pf_records == Pos::SEND_PF_RECORDS
                && !empty($merchantObj)
                && $is_merchant_send_pf == Pos::SEND_PF_RECORDS)){

                $pfArr = [
                    'mrcPfId' => $visa_mrc_pf_id,
                    'subMrcId' => $merchant_id,
                    'mcc' => $mcc
                ];
                //to store the data in database
                $sale_pf_record_data['sub_merchant_id'] = $pfArr['subMrcId'];
                $sale_pf_record_data['pf_merchant_id'] = $pfArr['mrcPfId'];
                $sale_pf_record_data['mcc'] = $pfArr['mcc'];
            }


        }else if($bankObj->payment_provider == config('constants.PAYMENT_PROVIDER.DENIZ_PTT')){

            if (($posObj->send_pf_records == Pos::SEND_PF_RECORDS
                && !empty($merchantObj)
                && $is_merchant_send_pf == Pos::SEND_PF_RECORDS)){

                if( $this->isSameBank( $bankObj->code , config('constants.BANK_CODE.odea_bank') , 4 )){
                // if ($bankObj->code == config('constants.BANK_CODE.odea_bank')){

                    $pfArr = [];

                }else{
                    if($ispfSettings){
                        $merchant_id = $remote_sub_merchant_id;
                    }
                    $pfArr = [
                        'SubMerchantCode' => str_pad($merchant_id, 15, "0", STR_PAD_LEFT),
                        'SubMerchantName' =>  Str::truncate($name, 25)
                    ];
                }

              //  $pfArr = Pf::denizInsurancePf();

            }

            if(!empty($pfArr)) {
                $sale_pf_record_data =
                    [
                        'order_id' => $order_id,
                        'identity_nin' => '',
                        'sub_merchant_id' => $pfArr['SubMerchantCode'] ?? '',
                        'pf_merchant_id' => $merchant_id,
                        'merchant_id' => $merchantObj->id,
                        'pf_merchant_name' => $pfArr['SubMerchantName'] ?? '',
                        'client_identity_number' => $client_identity_number
                    ];

                    if(!empty($purchaseRequest->data->vpos_type) && $purchaseRequest->data->vpos_type == CCPayment::VPOS_TYPE_INSURANCE) {
                        $sale_pf_record_data += ['masked_c' => $card_no];
                    }
            }

        }else if($bankObj->payment_provider == config('constants.PAYMENT_PROVIDER.KUVEYT_TURK_KATILIM')){

            /*if (($posObj->send_pf_records == Pos::SEND_PF_RECORDS
                && !empty($merchantObj)
                && $is_merchant_send_pf == Pos::SEND_PF_RECORDS)){*/
                
                if(empty($nin)){
                    $nin = config('constants.SUBMERCHANT_INFO.NIN');
                }

                $pfArr = [
                    //'Description' => $name,
                    'Description' => Str::truncate($brand_name . '/' . $name, self::$max_length_of_kuveyt_pf_description_param),
                    'IdentityTaxNumber' => $nin
                ];

                if (($posObj->send_pf_records == Pos::SEND_PF_RECORDS
                && !empty($merchantObj)
                && $is_merchant_send_pf == Pos::SEND_PF_RECORDS)){

                    $params = [
                        "merchantObj" => $merchantObj,
                        "subMerchantPFObj" => $subMerchantPFObj,
                        "bankObj" => $bankObj,
                        "merchant_id" => $merchant_id,
                        "nin" => $nin,
                        "remote_sub_merchant_id" => $remote_sub_merchant_id,
                    ];

                    $pfArr += (new Pf())->getCustomizedPFRecords($params);

                }

                //$pfArr = Pf::kuveytTarimTestPf();

           /* }*/

        }else if($bankObj->payment_provider == config('constants.PAYMENT_PROVIDER.QNB_FINANSBANK')){

            // if (($posObj->send_pf_records == Pos::SEND_PF_RECORDS
            //     && !empty($merchantObj)
            //     && $is_merchant_send_pf == Pos::SEND_PF_RECORDS)){

                $subMerchantCode = $merchant_id;
                if(Helper::isProvServerEnvironment() && Arr::isAMemberOf($client_identity_number,Pf::QNB_INSURANCE_TEST_CLIENT_IDENTITY_NUMBER)){
                    $subMerchantCode = 2731;
                }

                if(BrandConfiguration::call([PaymentFeatureTrait::class, 'isBrand_WantsZeroInSubMerchantCodeForQnbBank'])) {
                    $sub_merchant_code_length = 10 + Str::len((string)$subMerchantCode);
                    $subMerchantCode = Str::fill($subMerchantCode, $sub_merchant_code_length,0,true, STR_PAD_LEFT);
                }

                $card_acceptor_name = (new QnbFinans())->cardAcceptorName($brand_name, $merchant_name ?? $brand_name);

                if(!empty($bank_page_display_pf_name)){
                    $card_acceptor_name = Str::truncate($bank_page_display_pf_name, 25);
                }

                if (BrandConfiguration::call([PaymentFeatureTrait::class, 'isAllowBankWisePFId'])) {
                    $remote_sub_merchant_id = $bankObj->pf_id ?? "";
                }

                $pfArr = [
                    'PaymentFacilitatorId' => $remote_sub_merchant_id,
                    'SubMerchantCode' => $subMerchantCode,
                    'IndSalesOrgId' => $order_id,
                    'SubmerchantMCC' => $mcc,
                    'CardAcceptorName' => $card_acceptor_name,
                    'CardAcceptorStreet' => 'NULL Street',
                    'CardAcceptorCity' => $city,
                    'CardAcceptorPostalCode' => $postal_code,
                    'CardAcceptorState' => 'Turkey',
                    'CardAcceptorCountry' => $iso_country_code
                ];

                if(BrandConfiguration::qnbExtraPfRecordsParams()){
                   $pfArr['MerchantName'] = Str::fill($name, 24, ' ');
                   $pfArr['MerchantCity'] = Str::fill($city, 14, ' ');
                   $pfArr['MerchantCountry '] = $this->countryToIso('', true, $iso_country_code);
                }

            // }

        } else if($bankObj->payment_provider == config('constants.PAYMENT_PROVIDER.T_GARANTI')){

            if (($posObj->send_pf_records == Pos::SEND_PF_RECORDS
                && !empty($merchantObj)
                && $is_merchant_send_pf == Pos::SEND_PF_RECORDS)){

                $pfArr = [
                    'SubMerchantID' => $remote_sub_merchant_id
                ];
                //to store the data in database
                $sale_pf_record_data['sub_merchant_id'] = $pfArr['SubMerchantID'];
            }
        }else if (Bank::isSame($bankObj->code, config('constants.BANK_CODE.SIPAY'), Str::len(config('constants.BANK_CODE.SIPAY')))
            && Bank::isNestpayDirectAcquiring($bankObj)
        ){
            [$acquire_status, $merchant_pos_acquire_settings] = (new MerchantPosAcquiringSetting())->prepareAcquireBankInfo($merchantObj, $posObj->id);
            if ($acquire_status){
                $pfArr = $merchant_pos_acquire_settings;
                $shouldStore = true;
                $sale_pf_record_data['client_identity_number'] = $merchant_pos_acquire_settings['client_id'];
            }
        }

        if (
            !empty($posObj)
            && $this->isSameBank( $bankObj->code, config('constants.BANK_CODE.IS_BANK'), 6 )
            && $posObj->send_pf_records == Pos::DO_NOT_SEND_PF_RECORDS
            && $is_merchant_send_pf == Pos::DO_NOT_SEND_PF_RECORDS
            && BrandConfiguration::call([ PaymentFeatureTrait::class, 'isSendPFGroupIDWhenSendPFRecordInactive' ]) ) {

            if(empty($merchantPosPFSettingObj)){
                $merchantPosPFSetting = new MerchantPosPFSetting();
                $merchantPosPFSettingObj = $merchantPosPFSetting->findByMerchantIdAndPosId($merchantObj->id, $posObj->id,
                    MerchantPosPFSetting::STATUS_ACTIVE);
            }

            if ( !empty($merchantPosPFSettingObj)
                && $merchantPosPFSettingObj->is_send_pf_group_id == MerchantPosPFSetting::IS_SEND_PF_GROUP_ID_YES
            ) {

                $pfArr['GROUPID'] = $merchantObj->name;
                if ( !empty($merchantPosPFSettingObj->group_id_pf_name) ) {
                    $pfArr['GROUPID'] = $merchantPosPFSettingObj->group_id_pf_name;
                }

            }
        }

        if (
            !empty($posObj) && $posObj->send_pf_records == Pos::SEND_PF_RECORDS && $is_merchant_send_pf == Pos::SEND_PF_RECORDS
        ) {
            $purchase_request_data = (isset($purchaseRequest) && is_object($purchaseRequest->data)) ? Arr::objectToArray($purchaseRequest->data) : [];
            $pfArr = Pf::includeVisaPFRecords($bankObj, $purchase_request_data, $pfArr);
        }

        if($shouldStore){
            (new SalesPFRecords())->insert_entry($sale_pf_record_data);
        }




        return $pfArr;

    }

    private function getPaymixPf($merchantObj,$pos_id, $bankObj ,$card_no, $subMerchantPFObj = null){

        $merchant_id = $merchantObj->id;
        $merchant_name = $merchantObj->name;
        $password = $this->customEncryptionDecryption($bankObj->password, \config('app.brand_secret_key'), 'decrypt'); //"SiPay1!!!";

        $MerchantPosPfObj = new MerchantPosPFSetting();

        if($merchant_id == 11596 ){

        /*
            $pfHistory = new PfHistory();
            $pfHistoryObj = $pfHistory->findbyIp($this->getClientIp(),config('constants.BANK_CODE.PAYMIX'));

            if (!empty($pfHistoryObj)){

                $merchant_id = $pfHistoryObj->merchant_id;


            } else {

                $merchantIDs = array("18193","11596","92448");
                $random_key = array_rand($merchantIDs, 1);
                $merchant_id = $merchantIDs[$random_key];

                $pfHistory->addPfHistory($merchant_id,config('constants.BANK_CODE.PAYMIX'));
            */

                //new pf logic SVAP-817

                $merchantIDs = array("18193","11596","92448");

                $index = $this->getMerchantIDIndex(3,$card_no);

                $new_merchant_id = $merchantIDs[$index];

                if($new_merchant_id != $merchant_id){

                    $merchant_id = $new_merchant_id;
                    $merchant = new Merchant();
                    $merchant_name = $merchant->getMerchantNameById($merchant_id);
                }

        }

        $merchantPosPf = $MerchantPosPfObj->findByMerchantIdAndPosId($merchant_id,$pos_id);

        if(!empty($merchantPosPf) && !empty($merchantPosPf->sub_merchant_id)){
            $password = $merchantPosPf->sub_merchant_id;
        }

        if(!empty($subMerchantPFObj)){
            return  array(
                ['password' => $password],
                $subMerchantPFObj->merchant_id, $subMerchantPFObj->name
            );
        }

        return array(
            ['password' => $password],
            $merchant_id, $merchant_name
        );

//        return ['password' => $password];

    }

    private function getMerchantIDIndex($number_of_pf ,$card_no){
        $index = 0;
        if(strlen($card_no) > 0) {
            $first_six_digit = substr($card_no, 0, 6);
            $last_four_digit = substr($card_no, -4);
            $sum = ceil(($first_six_digit + $last_four_digit) / 10000);

            if($number_of_pf == 3){

                if ($sum <= 52) {

                    $index = 0;

                } elseif ($sum >= 53 && $sum <= 54) {

                    $index = 1;

                } elseif ($sum > 54) {

                    $index = 2;
                }
            }elseif ($number_of_pf == 4){


                if ($sum <= 51) {

                    $index = 0;

                } elseif ($sum  == 52) {

                    $index = 1;

                } elseif ($sum ==  53) {

                    $index = 2;

                }elseif ($sum >= 54) {

                    $index = 3;
                }
            }
        }
        return $index;
    }


    private function getRandomPfRecord($merchantObj, $pfArr, $bank_code, $card_no, $order_id)
    {
        $merchant_id = $merchantObj->id;

        $pf_merchant_id = $merchant_id;
        $pf_merchant_name = $merchantObj->name;

        $multiPfArray = array();
        $bank_type = '';


        if ($this->isSameBank($bank_code , config('constants.BANK_CODE.IS_BANK') , 6 )) {

            $bank_type = config('constants.BANK_CODE.IS_BANK');

            $multiPfArray = [
                '11596' => [
                    '0' => [
                        'SUBMERCHANTID' => '18193',
                        'SUBMERCHANTNAME' => '18193',
                        'SUBMERCHANTNIN' => '5901090006',
                        'SUBMERCHANTPOSTALCODE' => '34520',
                        'SUBMERCHANTCOUNTRY' => '792',
                        'SUBMERCHANTCITY' => 'İstanbul',
                        'SUBMERCHANTURL' => 'https://www',
                        'SUBMERCHANTMCC' => '5094',
                        'GROUPID' => 'KROM SAATÇİLİK'

                    ],
                    '1' => [
                        'SUBMERCHANTID' => '11596',
                        'SUBMERCHANTNAME' => '11596',
                        'SUBMERCHANTNIN' => '8591166689',
                        'SUBMERCHANTPOSTALCODE' => '34398',
                        'SUBMERCHANTCOUNTRY' => '792',
                        'SUBMERCHANTCITY' => 'İstanbul',
                        'SUBMERCHANTURL' => 'https://www.triapay.com.tr',
                        'SUBMERCHANTMCC' => '5045',
                        'GROUPID' => 'Tria Elektronik'

                    ],
                    '2' => [
                        'SUBMERCHANTID' => '45629',
                        'SUBMERCHANTNAME' => '45629',
                        'SUBMERCHANTNIN' => '3810897159',
                        'SUBMERCHANTPOSTALCODE' => '06520',
                        'SUBMERCHANTCOUNTRY' => '792',
                        'SUBMERCHANTCITY' => 'ANKARA',
                        'SUBMERCHANTURL' => 'https://www.totallookforyou.com',
                        'SUBMERCHANTMCC' => '5621',
                        'GROUPID' => 'Total Look For You'

                    ],
                    '3' => [
                        'SUBMERCHANTID' => '20427',
                        'SUBMERCHANTNAME' => '20427',
                        'SUBMERCHANTNIN' => '6450695161',
                        'SUBMERCHANTPOSTALCODE' => '06520',
                        'SUBMERCHANTCOUNTRY' => '792',
                        'SUBMERCHANTCITY' => 'ANKARA',
                        'SUBMERCHANTURL' => 'https://www.orbisgida.com/',
                        'SUBMERCHANTMCC' => '5812',
                        'GROUPID' => 'Orbis Gida'

                    ]
                ]
            ];

        }/* else if($bank_code == config('constants.BANK_CODE.AK_BANK')
                || $bank_code == config('constants.BANK_CODE.AK_BANK_DAILY') ) {
                    
                    $multiPfArray = [
                        '11596' => [
                            [
                                'SUBMERCHANTNAME' => 'KROM SAATÇİLİK',
                                'SUBMERCHANTID' => '18193',
                                'SUBMERCHANTPOSTALCODE' => '34520',
                                'SUBMERCHANTCITY' => 'İstanbul',
                                'SUBMERCHANTCOUNTRY' => '792',
                                'SUBMERCHANTMCC' => '5094'
                                
                            ],
                            [
                                'SUBMERCHANTNAME' => 'Tria Elektronik',
                                'SUBMERCHANTID' => '11596',
                                'SUBMERCHANTPOSTALCODE' => '34398',
                                'SUBMERCHANTCITY' => 'İstanbul',
                                'SUBMERCHANTCOUNTRY' => '792',
                                'SUBMERCHANTMCC' => '5045'
                                
                            ],
                            [
                                'SUBMERCHANTNAME' => 'Total Look For You',
                                'SUBMERCHANTID' => '45629',
                                'SUBMERCHANTPOSTALCODE' => '06520',
                                'SUBMERCHANTCITY' => 'ANKARA',
                                'SUBMERCHANTCOUNTRY' => '792',
                                'SUBMERCHANTMCC' => '5621'
                                
                            ]
                        ]
                    ];
                
                } */  else if( $this->isSameBank( $bank_code , config('constants.BANK_CODE.ZIRAAT_BANK') , 6 )){

            $bank_type = config('constants.BANK_CODE.ZIRAAT_BANK');

            $ninMerchantIdAssoc = [
                '5901090006' => '18193',
                '8591166689' => '11596',
                '3810897159' => '45629',
                '6450695161' => '20427',
            ];

            $multiPfArray = [
                '11596' => [
                    '0' =>[
                        'SUBMERCHANTNAME' => '5901090006',
                        'SUBMERCHANTID' => substr('KROM SAATÇİLİK', 0, 25)

                    ],
                    '1' => [
                        'SUBMERCHANTNAME' => '8591166689',
                        'SUBMERCHANTID' => substr('Tria Elektronik', 0, 25)

                    ],
                    '2' => [
                        'SUBMERCHANTNAME' => '3810897159',
                        'SUBMERCHANTID' => substr('Total Look For You', 0, 25)

                    ],
                    '3' => [
                        'SUBMERCHANTNAME' => '6450695161',
                        'SUBMERCHANTID' => substr('Orbis Gida', 0, 25)

                    ]
                ]
            ];

        }


        if (isset($multiPfArray[$merchant_id]) && (
                $bank_code == config('constants.BANK_CODE.IS_BANK')
                || $bank_code == config('constants.BANK_CODE.IS_BANK_PF')
                || $bank_code == config('constants.BANK_CODE.IS_BANK_TEST')
                //  || $bank_code == config('constants.BANK_CODE.AK_BANK')
                // || $bank_code == config('constants.BANK_CODE.AK_BANK_DAILY')
                || $bank_code == config('constants.BANK_CODE.ZIRAAT_BANK')
                || $bank_code == config('constants.BANK_CODE.ZIRAAT_BANK2')
                || $bank_code == config('constants.BANK_CODE.ZIRAAT_BANK_WEEKLY')
                || $bank_code == config('constants.BANK_CODE.ZIRAAT_BANK_TEST')

            )
        ) {

/*
            $pfHistory = new PfHistory();
            $pfHistoryObj = $pfHistory->findbyIp($this->getClientIp());
            $add_new_record = true;

            if (!empty($pfHistoryObj)) {

                $collection = collect($multiPfArray[$merchant_id])->where('SUBMERCHANTID', $pfHistoryObj->merchant_id);

                if (count($collection) > 0) {

                    $multiPfArray[$merchant_id] = $collection->toArray();
                    $add_new_record = false;
                }
            }
*/



//            $pfArr = $multiPfArray[$merchant_id] [array_rand( $multiPfArray[$merchant_id] )];
              $index = $this->getMerchantIDIndex(4,$card_no);
              $pfArr = $multiPfArray[$merchant_id][$index] ;
/*
            if ($add_new_record) {
                $pfHistory->addPfHistory($pfArr['SUBMERCHANTID'], $bank_code);
            }
*/
            if ($bank_type == config('constants.BANK_CODE.IS_BANK')){//isbank
                $pf_merchant_id = $pfArr['SUBMERCHANTID'];
                $pf_merchant_name = $pfArr['GROUPID'];

            }elseif($bank_type == config('constants.BANK_CODE.ZIRAAT_BANK')){
                $pf_merchant_id = $ninMerchantIdAssoc[$pfArr['SUBMERCHANTNAME']];
                $pf_merchant_name = $pfArr['SUBMERCHANTID'];
            }

            $data = [
                'order_id' => $order_id,
                'identity_nin' => '',
                'sub_merchant_id' => '',
                'pf_merchant_id' => $pf_merchant_id,
                'pf_merchant_name' => $pf_merchant_name
            ];

            $inserted = (new SalesPFRecords)->insert_entry($data);

//            if (PaymentProvider::getCardType($card_no) == 2) {
//                $pfArr['SUBMERCHANTNAME'] = $pfArr['GROUPID'];
//            }



        }

        return $pfArr;
    }


    public function getBrandBankUniqueCode($order_id, $bank_code  = ""){

        $unique_code  =  $this->getUniqueURLById($order_id);
        $unique_code  = time().$unique_code;

        if(strlen($unique_code) < 16){
            //$unique_code  =   str_pad($unique_code, 16, "X", STR_PAD_RIGHT);
            $unique_code  = $unique_code.substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyzABCDEFGHIJKLMNOPQRSTVWXYZ"), 0, 16-strlen($unique_code));

        }

        $unique_code =   substr($unique_code, 0,16);

        return $unique_code;

    }


    private function getUniqueURLById($in, $to_num = false, $pad_up = false){
		
	    $in = Number::getOnlyIntegerNum($in);
		
        $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $base = strlen($index);
        if ($to_num) {
            // Digital number <<-- alphabet letter code
            $in = strrev($in);
            $out = 0;
            $len = strlen($in) - 1;
            for ($t = 0; $t <= $len; $t++) {
                $bcpow = bcpow($base, $len - $t);
                $out = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
            }

            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $out -= pow($base, $pad_up);
                }
            }
        } else {
            // Digital number -->> alphabet letter code
            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $in += pow($base, $pad_up);
                }
            }

            $out = "";
            for ($t = floor(log10($in) / log10($base)); $t >= 0; $t--) {
                $a = floor($in / bcpow($base, $t));
                $out = $out . substr($index, $a, 1);
                $in = $in - ($a * bcpow($base, $t));
            }
            $out = strrev($out); // reverse
        }

        return $out;

    }

    public static function generateBrandOrderid($prefix = 'VP'){
        return $prefix.time() . rand(10000, 9999999);
    }

    public static function generateColumnWiseCode($modelObject, $columnName,$prefix='PREFIX',$length=5)
    {
         $code = '';

            $lastId = $modelObject->latest($columnName)->pluck($columnName)->first();
            $numericPart = (int) substr($lastId, strpos($lastId, '-') + 1);
            $numericPart++;
            $code = $prefix . str_pad($numericPart, $length, '0', STR_PAD_LEFT);

        return $code;
    }

    public static function isTestMerchantKey($merchant_key){
        $testKeys = [
            '$2y$10$xhuceUJALf4Ucw2t.NLQwewDW28.nU67TgEXPFv2o6i1AhN5C3tsm',
            '$2y$10$Ac0YN3IUwe5wdtTWjIhQP.EisL5C3IQ3Hss59CRhFxplgH958XYsy',
            '$2y$10$PZpc.eAQ7Xws4lF2P2ynWuC4Wk9Fl/A/ITjxvqdyg5GzpJlsMn1Hu'
//            '$2y$10$w/ODdbTmfubcbUCUq/ia3OoJFMUmkM1UVNBiIQIuLfUlPmaLUT1he'
        ];

        return in_array($merchant_key, $testKeys);
    }

    public static function isSendMoneyLimitExceed($currency_settings, $user_id, $currency_id, $send_type, $amount, $extra=[]){
        $is_money_transfer_max_exceed = 0;
        $total_gross_amount = $number_of_rows = $total_net_amount = 0;

        if (!empty($currency_settings)) {

            if (isset($extra['receiver_commission']) && $extra['receiver_commission']) {
                $receive = new Receive();
                list($total_gross_amount, $number_of_rows, $total_net_amount) = $receive->getTotalGrossAndNumberRow($user_id, $currency_id, $send_type);
                $total_net = $total_net_amount + $amount;
                $num_of_rows = $number_of_rows + 1;

                if ($total_net > $currency_settings->total_receive_money_max_limit
                    || $num_of_rows > $currency_settings->total_receive_money_max_transaction
                ) {
                    $is_money_transfer_max_exceed = UserProfile::IS_MONEY_TRANSFER_MAX_EXCEED_YES;
                }
            } else {
                $send = new Send();
                list($total_gross_amount, $number_of_rows, $total_net_amount) = $send->getTotalGrossAndNumberRow($user_id, $currency_id, $send_type);
                $total_gross = $total_gross_amount + $amount;
                $num_of_rows = $number_of_rows + 1;

                if ($total_gross > $currency_settings->total_money_transfer_max_limit
                    || $num_of_rows > $currency_settings->total_money_transfer_max_transaction
                ) {
                    $is_money_transfer_max_exceed = UserProfile::IS_MONEY_TRANSFER_MAX_EXCEED_YES;
                }
            }
        }

        return [$is_money_transfer_max_exceed, $total_gross_amount, $number_of_rows, $total_net_amount];
    }

    public static function checkNonVerifiedUerLimitExceed($userObj, $walletObj, $currency_settings, $amount ){
        $is_non_verified_user_limit_exceed = false;

        if ($userObj->user_category == Profile::NOT_VERIFIED) {

            $receive = new Receive();
            list($total_gross, $num_of_rows, $total_net) = $receive->getTotalGrossAndNumberRow($userObj->id,
                $currency_settings->currency_id);
            $receiverWillAmount = $total_net + $amount;
            $num_of_rows = $num_of_rows +1;
//            $receiverWillAmount = ($total_purchase_gross + $walletObj->amount + $amount);

            if ($currency_settings->max_balance_of_non_verified_user < $receiverWillAmount) {
                $is_non_verified_user_limit_exceed = true;
//                            $statusCode = 22;
            }

           //
           $currencySetting = new CurrenciesSettings();
           if ($receiverWillAmount > $currency_settings->{$currencySetting->getColumnName($userObj->user_category,CurrenciesSettings::MAX_RECEIVE_MONEY_LIMIT)}){
              $is_non_verified_user_limit_exceed = true;
           }

           if ($receiverWillAmount > $currency_settings->{$currencySetting->getColumnName($userObj->user_category,CurrenciesSettings::MAX_BALANCE_LIMIT)}){
              $is_non_verified_user_limit_exceed = true;
           }

        }


        return $is_non_verified_user_limit_exceed;

    }

    public static function checkUserMaxTransactionLimitForAllCategory($userObj, $walletObj, $currency_settings, $amount ){
        $status = '';

        $receive = new Receive();

        list($total_gross, $num_of_rows, $total_net) = $receive->getTotalGrossAndNumberRow($userObj->id,
            $currency_settings->currency_id);
        $receiverWillAmount = $total_net + $amount;
        $num_of_rows = $num_of_rows +1;


        $currencySetting = new CurrenciesSettings();
		
        if ($receiverWillAmount > $currency_settings[$currencySetting->getColumnName($userObj->user_category,CurrenciesSettings::MAX_RECEIVE_MONEY_LIMIT)]){
            $status =  52; // User max receive money limit exceed
        }
		
        if ($receiverWillAmount > $currency_settings[$currencySetting->getColumnName($userObj->user_category,CurrenciesSettings::MAX_BALANCE_LIMIT)]){
            $status = 46; // User max balance limit exceed
        }
        return $status;

    }

    public static function getProfilePic(int $gender = null, $path = null){
        $url = \Illuminate\Support\Facades\Storage::url($path);

        if( !empty($path) && File::exists($path)){
            return \Illuminate\Support\Facades\Storage::url($path);
        }else{
            switch ($gender) {
                case 1:
                    return \Illuminate\Support\Facades\Storage::url('assets/images/avatar.png');
                    break;
                case 2:
                    return \Illuminate\Support\Facades\Storage::url('assets/images/female_default_avatar.png');
                    break;
                default:
                    return \Illuminate\Support\Facades\Storage::url('assets/images/gender_neutral.png');
                    break;
            }
        }
    }

    public static function safe_json_encode($value, $options = 0, $depth = 512, $utfErrorFlag = false)
    {
        return Json::safeEncode($value, $options, $depth,$utfErrorFlag);
    }


    public static function getFirstLastNameFromName($name){
        $parts = explode(" ", $name);
        if(count($parts) > 1) {
            $lastname = array_pop($parts);
            $firstname = implode(" ", $parts);
        }else {
            $firstname = $name;
            $lastname = " ";
        }

        return [$firstname, $lastname];
    }

    /**
     * Get location data by ip.
     *
     * @param  string  $ip
     * @return array
     */
    public static function geoLocation($ip)
    {

        if (isset($ip) && !empty($ip)) {

            // Set API access key 
            $access_key = '70d0aa616949e635e7065abc7ff82910';

            $url = 'http://api.ipstack.com/'.$ip.'?access_key='.$access_key.'';
            $json = Curl::withTimeout(3)->get($url,[]);

            // Decode JSON response:
            $api_result = json_decode($json, true);

            return $api_result;
        }

        return false;
    }

    public static function getBrandErrorCodeByBankCode($payment_provider, $bank_error_code){
//51 - Insufficient Funds = ISO8583-51
//41 - Lost Card = ISO8583-41
//43 - Stolen Card = ISO8583-43
//07 - Hold Card = ISO8583-07
//54 - Expired Card = CORE-2010, ISO8583-33
//82 - Declined CVV = ISO8583-84, ISO8583-82
        $array = [
            config('constants.PAYMENT_PROVIDER.NESTPAY') => self::nestpayErrorCodeMapping(),
            config('constants.PAYMENT_PROVIDER.AKBANK') => self::akbankErrorCodeMapping(),
            config('constants.PAYMENT_PROVIDER.NESTPAY_SP_ACQUIRING') => self::nestpaySPAcquiringErrorCodeMapping(),
            config('constants.PAYMENT_PROVIDER.VAKIF') => [
                '51' => '51',
                '41' => '41',
                '43' => '43',
                '07' => '07',
                '54' => '54',
                '82' => '82',
                '05' => '82'
            ],
            config('constants.PAYMENT_PROVIDER.ALBARAKA') => [
                '0051'=> '51',
                '0041'=>'41',
                '0217'=>'43',
                '007' => '07',
                '0054' => '54',
                '0150' => '82',
                '139' => '82',
                '05' => '82'
            ],

//            config('constants.PAYMENT_PROVIDER.ESNEKPOS') => [
//                'ISO8583-51' => '51',
//                'ISO8583-41' => '41',
//                'ISO8583-43' => '43',
//                'ISO8583-07' => '07',
//                'CORE-2010' => '54',
//                'ISO8583-33' => '54',
//                'ISO8583-84' => '82',
//                'ISO8583-82' => '82'
//            ],
            config('constants.PAYMENT_PROVIDER.MSU') => [
                'ISO8583-51' => '51',
                'ISO8583-41' => '41',
                'ISO8583-43' => '43',
                'ISO8583-07' => '07',
                'CORE-2010' => '54',
                'ISO8583-33' => '54',
                'ISO8583-84' => '82',
                'ISO8583-82' => '82'
            ],
            config('constants.PAYMENT_PROVIDER.TURKPOS') => [
                '51' => '51',
                '41' => '41',
                '43' => '43',
                '04' => '07',
                '38' => '07',
                '54' => '54',
                '0005' => '82',
                '12' => '82'
            ],
            config('constants.PAYMENT_PROVIDER.YAPI_VE_KREDI') => [
                '0051' => '51',
                '0041' => '41',
                '0217' => '43',
                '007' => '07',
                '0054' => '54',
                '0150' => '82',
                '139' => '82'
            ],
//            config('constants.PAYMENT_PROVIDER.PAYALL') => [
//                '51' => '51',
//                '41' => '41',
//                'ISO8583-43' => '43',
//                'ISO8583-07' => '07',
//                'CORE-2010' => '54',
//                'ISO8583-33' => '54',
//                'ISO8583-84' => '82',
//                'ISO8583-82' => '82'
//            ],

        ];

        return $array[$payment_provider][$bank_error_code] ?? 0 ;


    }

    private static function nestpayErrorCodeMapping()
    {
        return [
            '51' => '51',
            '41' => '41',
            '43' => '43',
            '07' => '07',
            '2010' => '54',
            '33' => '54',
            '84' => '82',
            '82' => '82',
            '05' => '82'
        ];

    }

    private static function akbankErrorCodeMapping()
    {
        return [
            '51' => '51',
            '41' => '41',
            '43' => '43',
            '07' => '07',
            '2010' => '54',
            '33' => '54',
            '84' => '82',
            '82' => '82',
            '05' => '82'
        ];

    }

    private static function nestpaySPAcquiringErrorCodeMapping()
    {
        return [
            '51' => '51',
            '41' => '41',
            '43' => '43',
            '07' => '07',
            '2010' => '54',
            '33' => '54',
            '84' => '82',
            '82' => '82',
            '05' => '82'
        ];

    }


    public function getSaleExportData($search, $join_table = true , $paginate = false)
    {
        $query = Sale::with('SaleRecurringHistory', 'saleRecurring');
        if ($join_table) {
            $query->leftJoin('users', 'users.id', '=', 'sales.user_id')
                ->leftJoin('merchant_sales', 'merchant_sales.sale_id', '=', 'sales.id')
                ->leftJoin('sale_billings', 'sale_billings.sale_id', '=', 'sales.id')
                ->leftJoin('sale_integrators', 'sale_integrators.sale_id', '=', 'sales.id')
                ->leftJoin('rolling_balances', 'rolling_balances.sale_id', '=', 'sales.id')
                ->leftJoin('integrators', 'sale_integrators.integrator_id', '=', 'integrators.id')
                ->leftJoin('refund_histories', 'refund_histories.sale_id', '=', 'sales.id')
                ->leftJoin('pos', 'pos.pos_id', '=', 'sales.pos_id')
                ->select(
                    'sales.id',
                    'sales.payment_id',
                    'sales.invoice_id',
                    'sales.gsm_number',
                    'sales.user_id',
                    'sales.pos_name',
                    'sales.gross',
                    'sales.created_at',
                    'sales.updated_at',
                    'sales.refund_request_date',
                    'sales.refund_reason',
                    'sales.total_refunded_amount',
                    'sales.payment_type_id',
                    'sales.card_issuer_name',
                    'sales.document',
                    'sales.card_holder_bank',
                    'sales.card_program',
                    'sales.merchant_name',
                    'sales.transaction_state_id',
                    'sales.currency_id',
                    'sales.refunded_chargeback_fee',
                    'sales.installment',
                    'sales.merchant_commission',
                    'sales.net',
                    'sales.pay_by_token_fee',
                    'sales.chargeback_reject_explanation as sale_chargeback_reject_explanation',
                    'users.name',
                    'merchant_sales.merchant_commission_percentage',
                    'merchant_sales.card_type',
                    'sales.dpl_id',
                    'sales.payment_source',
                    'sales.recurring_id',
                    'sales.credit_card_no',
                    'sales.ip',
                    'sales.auth_code',
                    'sales.json_data',
                    'sales.remote_order_id',
                    'merchant_sales.merchant_commission_fixed',
                    'merchant_sales.cot_percentage',
                    'merchant_sales.cot_fixed',
                    'merchant_sales.merchant_rolling_percentage',
                    'sale_billings.card_holder_name',
                    'sales.order_id',
                    'sales.settlement_date_merchant',
                    'sales.result',
                    'sales.settlement_date_bank',
                    'sales.rolling_amount',
                    'merchant_sales.end_user_commission_percentage',
                    'merchant_sales.end_user_commission_fixed',
                    'merchant_sales.merchant_rolling_percentage',
                    'sales.product_price',
                    'sales.sale_type',
                    'sale_integrators.commission_amount',
                    'integrators.integrator_name',
                    'integrators.default_commission_percentage',
                    'integrators.default_commission_fixed',
                    'rolling_balances.effective_date',
                    'refund_histories.refund_reference_number',
                    'refund_histories.amount',
                    'refund_histories.refund_commission',
                    'refund_histories.refund_commission_fixed',
                    'refund_histories.transaction_state_id AS state_id',
                    'refund_histories.net_refund_amount',
                    'refund_histories.rolling_refund_amount',
                    'refund_histories.is_fully_refunded',
                    'refund_histories.created_at AS refund_created_at',
                    'refund_histories.updated_at AS refund_updated_at',
                    'pos.bank_name',
                    'sales.admin_force_chargeback_document',
                    'sales.admin_force_chargeback_explanation'
                );
        } else {
            $query->leftJoin('pos', 'pos.pos_id', '=', 'sales.pos_id')
                  ->leftJoin('sale_reports', 'sale_reports.sale_id', '=', 'sales.id')
                ->select(
                    'sales.id',
                    'sales.payment_id',
                    'sales.gsm_number',
                    'sales.user_name',
                    'sales.merchant_name',
                    'sales.document',
                    'sales.gross',
                    'sales.auth_code',
                    'sales.credit_card_no',
                    'sales.card_issuer_name',
                    'sales.card_program',
                    'sales.created_at',
                    'sales.updated_at',
                    'sales.payment_type_id',
                    'sales.refunded_chargeback_fee',
                    'sales.transaction_state_id',
                    'sales.is_cancel',
                    'sales.currency_id',
                    'sales.order_id',
                    'sales.sale_type',
                    'sales.admin_force_chargeback_document',
                    'sales.admin_force_chargeback_explanation',
                    'sales.json_data',
                    'sales.refund_request_amount',
                    'sales.total_refunded_amount',
                    'pos.bank_name',
                    'sale_reports.is_loyalty_point_sale'
                );
        }


        if (!empty($search['user_id'])){
            $query->where('sales.user_id', $search['user_id']);
        }



            // Customer GSM
            if (!empty($search['customergsm'])) {
                $query->where('sales.gsm_number', $search['customergsm']);
            }

            // Min Amount
            if (!empty($search['amount'])) {
                $query->where('sales.gross', $search['amount']);
            }

            // Min Amount
            if (!empty($search['minamount'])) {
                $query->where('sales.gross', '>=', $search['minamount']);
            }

            // Max Amount
            if (!empty($search['maxamount'])) {
                $query->where('sales.gross', '<=', $search['maxamount']);
            }

            // Payment ID
            if (!empty($search['transid'])) {
                $query->where('sales.payment_id', $search['transid']);
            }

            // Invoice ID
            if (!empty($search['invoiceid'])) {
                $query->where('sales.invoice_id', $search['invoiceid']);
            }

            // Payment Method ID
            if (!empty($search['paymentmethodid'])) {
                list($query, $search) = (new SaleTransaction())->manageByPaymentType($query, $search);
            }

            // Merchant ID
            if (!empty($search['merchantid'])) {
                if (is_array($search['merchantid'])) {
                    $query->whereIn('sales.merchant_id', $search['merchantid']);
                } else {
                    $query->where('sales.merchant_id', '=', $search['merchantid']);
                }
            }

       $query = (new GlobalMerchant())->restrictFilterForMerchantAndUsers($query, 'sales.merchant_id', $search['auth_user_id'] ?? '', $search['is_filter_by_user_id'] ?? false );


       // Transaction State
            if (!empty($search['transactionState'])) {
                if (is_array($search['transactionState'])) {
                    $query->whereIn('sales.transaction_state_id', $search['transactionState']);
                } else {
                    $query->where('sales.transaction_state_id', $search['transactionState']);

                    // Failed Refund
                    if ($search['transactionState'] == TransactionState::AWAITINGREFUND
                        && isset($search['is_bank_refund_failed'])) {
                        $query->where('is_bank_refund_failed', $search['is_bank_refund_failed']);
                    }
                }
            } elseif (!empty($search['transactionStateChargeback'])) {
                $query->whereIn('sales.transaction_state_id', $search['transactionStateChargeback']);
            } else {
                if (empty($search['allTransaction'])) {
                    if (isset($search['is_bank_refund_failed'])) {
                        if ($search['is_bank_refund_failed'] == Sale::REFUND_TRIED_AND_FAILED) {
                            $query->where(function ($q) use ($search) {
                                $q->where(function ($q) use ($search) {
                                    $q->where('transaction_state_id', '=', TransactionState::CHARGE_BACK_REQUESTED)
                                        ->orWhere('transaction_state_id', '=', TransactionState::CHARGE_BACK_REJECTED);
                                })->where('is_bank_refund_failed', $search['is_bank_refund_failed']);
                            });
                        } else {
                            $query->where(function ($q) use ($search) {
                                $q->where(function ($q) use ($search) {
                                    $q->where('transaction_state_id', '=', TransactionState::CHARGE_BACK_APPROVED)
                                        ->orWhere('transaction_state_id', '=', TransactionState::CHARGE_BACK_REJECTED);
                                })->where('is_bank_refund_failed', $search['is_bank_refund_failed']);
                            });
                        }
                    } else {
                        if (isset($search['completedRefunds']) && $search['completedRefunds'] == true) {
                            $query->whereRaw(
                                '(sales.transaction_state_id = "' . TransactionState::REFUNDED . '"
                            OR sales.transaction_state_id = "' . TransactionState::PARTIAL_REFUND . '")');
                        } else {
                            $query->where(function ($q) use ($search) {
                                $q->where('transaction_state_id', '=', TransactionState::CHARGE_BACK_REQUESTED)
                                    ->orWhere('transaction_state_id', '=', TransactionState::CHARGE_BACK_APPROVED)
                                    ->orWhere('transaction_state_id', '=', TransactionState::CHARGE_BACK_REJECTED)
                                    ->orWhere('transaction_state_id', '=', TransactionState::CHARGE_BACKED)
                                    ->orWhere('transaction_state_id', '=', TransactionState::CHARGE_CANCELED);

                                if (!empty($search['transactionStateID'])) {
                                    $q->orWhere('transaction_state_id', '=', TransactionState::COMPLETED);
                                }
                            });
                        }
                    }

                }
            }

            // Date Range
            if (isset($search['both_created_updated_at']) && !empty($search['both_created_updated_at'])) {
                //// for account statement report
                if (!empty($search['daterange'])) {
                    $query->where(function ($q) use ($search, $join_table) {
                        if ($join_table) {
                            $q->whereBetween('refund_histories.created_at', [ManipulateDate::startOfTheDay($search['from_date']), ManipulateDate::endOfTheDay($search['to_date'])])
                                ->orWhereBetween('sales.created_at', [ManipulateDate::startOfTheDay($search['from_date']), ManipulateDate::endOfTheDay($search['to_date'])])
                                ->orWhereBetween('sales.updated_at', [ManipulateDate::startOfTheDay($search['from_date']), ManipulateDate::endOfTheDay($search['to_date'])]);
//
                        } else {
                            $q->whereBetween('sales.created_at', [ManipulateDate::startOfTheDay($search['from_date']), ManipulateDate::endOfTheDay($search['to_date'])])
                                ->orWhereBetween('sales.updated_at', [ManipulateDate::startOfTheDay($search['from_date']), ManipulateDate::endOfTheDay($search['to_date'])]);
//
                        }
                       //if ($join_table) {
//                            $q->orWhereBetween('refund_histories.created_at', [ManipulateDate::startOfTheDay($search['from_date']), ManipulateDate::endOfTheDay($search['to_date'])]);
//                        }
//
                    });

                }
            } elseif (!empty($search['daterange'])) {
                $query->whereBetween('sales.' . $search['order_by'], [ManipulateDate::startOfTheDay($search['from_date']), ManipulateDate::endOfTheDay($search['to_date'])]);

            }

            // Search Keyword
            if (!empty($search['searchkey'])) {
                $query->where(function ($q) use ($search) {
                    $q->where('sales.id', 'like', '%' . $search['searchkey'] . '%')
                        ->orWhere('sales.currency_symbol', 'like', '%' . $search['searchkey'] . '%')
                        ->orWhere('sales.gross', 'like', '%' . $search['searchkey'] . '%')
                        ->orWhere('sales.cost', 'like', '%' . $search['searchkey'] . '%')
                        ->orWhere('sales.gsm_number', 'like', '%' . $search['searchkey'] . '%')
                        ->orWhere('sales.order_id', 'like', '%' . $search['searchkey'] . '%')
                        ->orWhere('sales.payment_id', 'like', '%' . $search['searchkey'] . '%')
                        ->orWhere('sales.invoice_id', 'like', '%' . $search['searchkey'] . '%')
                        ->orWhere('sales.ip', 'like', '%' . $search['searchkey'] . '%')
                        ->orWhere('sales.product_price', 'like', '%' . $search['searchkey'] . '%');
                });
            }

            // Merchant Name
            if (!empty($search['merchantname'])) {
                $query->where('merchant_name','like', '%' .  $search['merchantname'] . '%');
            }

            // User Name
            if (!empty($search['username'])) {
                $query->where('user_name', $search['username']);
            }

            // Pos ID
            if (!empty($search['pos_id'])) {
                $query->whereIn('sales.pos_id', $search['pos_id']);
            }

            // Currencies
            if (!empty($search['currencies']) || !empty($search['currency_id'])) {
                $query->whereIn('sales.currency_id', $search['currencies'] ?? $search['currency_id']);
            }



        $query->orderBy('sales.'.$search['order_by'], 'DESC');

        if ( $paginate && !empty($search['page_limit'])) {
            $query = $query->paginate($search['page_limit']);
        }else{
            $query = $query->get();
        }

        return $query;
    }


    public static function is_url_valid($uri){
        $url = parse_url($uri);
        if (!isset($url["host"])) return false;
        return !(gethostbyname($url["host"]) == $url["host"]);

    }


    //// added on 04 february, 2021

    public function getSaleData($search, $orderBy, $paginate = true, $orderByDesc = 'DESC')
    {
        $saleTransaction = new SaleTransaction();
        $search['order_by'] = $orderBy;
        $search['asc_desc']  = $orderByDesc;
        $search["both_created_updated_at"] = "yes";
        return $saleTransaction->getSaleData($search, $paginate);
        $query = Sale::query();

        if(!empty($search['isExport']) || !empty($search['isAdmin'])) {
            $query->with('payment_type', 'statedata', 'currencydata', 'methoddata', 'merchantSale', 'saleBilling', 'rolling_balance', 'saleRecurring','SaleRecurringHistory', 'refundHistory');
        } elseif (empty($search['noRelation'])) {
            $query->with('payment_type', 'statedata', 'currencydata', 'methoddata', 'merchantSale', 'saleBilling', 'rolling_balance', 'saleRecurring','SaleRecurringHistory');
        }elseif (isset($search['modelRelationArray'])){
            $query->with($search['modelRelationArray']);
        }

        if (isset($search['selectedColumn'])){
            $query->select($search['selectedColumn']);
        }

        if (!empty($search['end_user_id'])) {
            $query->where('end_user_id', $search['end_user_id']);

        } elseif (empty($search['isAdmin'])) {
            $query->where('user_id', $search['user_id']);
        }

        //// for transaction receipt api
        if (!empty($search['transactionId'])) {
            $query = $query->where('payment_id', $search['transactionId'])->first();
            return $query;
        }

        // Transaction ID (Sale ID ?)
        if (!empty($search['transaction_id'])) {
            $query = $query->where('id', $search['transaction_id'])->first();
            return $query;
        }


        if(!empty($search['billing_phone']) || !empty($search['billing_email']) || !empty($search['bill_phone']) || !empty($search['bill_email']) ){
            if (isset($search['bill_email']) || isset($search['bill_phone'])){
                $search['billing_email'] = $search['bill_email'];
                $search['billing_phone'] = $search['bill_phone'];
            }
            $billing = SaleBilling::orWhere('bill_phone', $search['billing_phone'])->orWhere('bill_email', $search['billing_email'])->orderBy('id', 'desc')->first();
            if(!empty($billing) && !is_null($billing)){
                $query->where('id', $billing->sale_id);
            }

        }

        // card number & card holder

       if (!empty($search['first_six_digit_card_number'])) {
          $query->where('credit_card_no', 'like',  $search['first_six_digit_card_number'] . '%');
       }

       if (!empty($search['last_four_digit_card_number'])) {
          $query->where('credit_card_no', 'like', '%' . $search['last_four_digit_card_number'] );
       }

       if (!empty($search['ip_address'])) {
          $query->where('ip', 'like', '%' . $search['ip_address'] . '%');
       }

       if (!empty($search['card_holder'])) {
          $billing = SaleBilling::where('card_holder_name', 'like', '%' . $search['card_holder'] . '%' )->pluck('sale_id')->toArray();
          if(is_array($billing) && count($billing)> 0 ){
             $query->whereIn('id', $billing);
          }
       }


        if (!empty($search['end_user_id']) && !empty($search['purchase_id'])) {
            $res = $query->where('end_user_id', $search['end_user_id'])->where('purchase_id', $search['purchase_id'])->first();
            CommonFunction::setPurchasePopUpData($res);
            return $res;
        }


        if (!empty($search['end_user_id']) && !empty($search['transactionable_id'])) {
            $res = $query->where('end_user_id', $search['end_user_id'])->where('id', $search['transactionable_id'])->first();
            return $res;
        }

        if (!empty($search['transactionable_id'])) {
            $query = $query->where('id', $search['transactionable_id'])->first();
            return $query;
        }


        if (!empty($search['user_created_date'])) {
            $query = $query->where('created_at', '>=', $search['user_created_date']);
        }



        if (!empty($search['merchant_id'])) {
            $query->where('merchant_id', '=', $search['merchant_id']);
        }

        // Merchant ID
        if (!empty($search['merchantid'])) {
            if (is_array($search['merchantid'])) {
                $query->whereIn('merchant_id', $search['merchantid']);
            } else {
                $query->where('merchant_id', '=', $search['merchantid']);
            }
        }

        if (empty($search['refundedTransaction'])) {

            if (!empty($search['transactionState'])) {

                if((is_array($search['transactionState']) && count($search['transactionState']) ==1 && in_array(TransactionState::PROVISION, $search['transactionState'])) || $search['transactionState'] == TransactionState::PROVISION) {
                    $query->where('transaction_state_id',TransactionState::PENDING)->where('sale_type', Sale::PREAUTH);

                }else if((is_array($search['transactionState']) && count($search['transactionState']) ==1 && in_array(TransactionState::PENDING,$search['transactionState'])) || $search['transactionState'] == TransactionState::PENDING) {

                    $query->where('transaction_state_id',TransactionState::PENDING)->where('sale_type', Sale::Auth);

                }else if(is_array($search['transactionState'])) {
                    $query->whereIn('transaction_state_id', $search['transactionState']);
                }else{
                    $query->where('transaction_state_id', $search['transactionState']);
                    if($search['transactionState'] == TransactionState::AWAITINGREFUND){
                        $query->where('is_bank_refund_failed', $search['is_bank_refund_failed']);
                    }
                }
            }else {

                if (!empty($search['allTransaction'])) {
                    $query->where('transaction_state_id', '<=', TransactionState::FAILED);
//                    $query->where('transaction_state_id', '<', TransactionState::CHARGE_BACK_REQUESTED);
                } else {
                    if(isset($search['transactionStateChargeback']) && !empty($search['transactionStateChargeback'])) {
                        $query->whereIn('transaction_state_id', $search['transactionStateChargeback']);
                    } else {
                        $query->where(function ($q) use ($search) {
                            $q->where('transaction_state_id', '=', TransactionState::CHARGE_BACK_REQUESTED)
                                ->orWhere('transaction_state_id', '=', TransactionState::CHARGE_BACK_APPROVED)
                                ->orWhere('transaction_state_id', '=', TransactionState::CHARGE_BACK_REJECTED)
                                ->orWhere('transaction_state_id', '=', TransactionState::CHARGE_BACKED)
                                ->orWhere('transaction_state_id', '=', TransactionState::CHARGE_CANCELED);
                            if (!empty($search['transactionStateID'])) {
                                $q->orWhere('transaction_state_id', '=', TransactionState::COMPLETED);
                            }
                        });
                    }
                }
            }
        } elseif (isset($search['refundedTransaction']) && !empty($search['refundedTransaction'] &&
                $search['refundedTransaction']==Config('constants.TRANSACTION_TYPE.SALE'))) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_state_id', '=', TransactionState::REFUNDED)
                    ->orWhere('transaction_state_id', '=', TransactionState::PARTIAL_REFUND);
            });
            $orderBy = 'refund_request_date';
        }

        if (!empty($search['trans_state'])) {
            $query->where('transaction_state_id', '<', $search['trans_state']);
        }

        if (!empty($search['merchantname'])) {
            $query->where('merchant_name', $search['merchantname']);
        }

        if (!empty($search['username'])) {
            $query->where('user_name', $search['username']);
        }

        if (!empty($search['paymentmethodid'])) {
//            if(is_array($search['paymentmethodid'])) {
//                $query->whereIn('payment_type_id', $search['paymentmethodid']);
//            }else{
//                $query->where('payment_type_id', $search['paymentmethodid']);
//            }

            list($query, $search) = (new SaleTransaction())->manageByPaymentType($query, $search);
        }

        if (!empty($search['transid'])) {
            $query->where('payment_id', $search['transid']);
        }

        if (!empty($search['orderid'])) {
            $query->where('order_id', $search['orderid']);
        }

        if (!empty($search['invoiceid'])) {
            $query->where('invoice_id', 'LIKE', '%'. $search['invoiceid'] . '%');
        }

        if (!empty($search['refundreason'])) {
            if(is_array($search['refundreason'])) {
                $query->whereIn('refund_reason', $search['refundreason']);
            }else{
                $query->where('refund_reason', $search['refundreason']);
            }
        }

        if (!empty($search['pos_id'])) {
            if(is_array($search['pos_id'])) {
                $query->whereIn('pos_id', $search['pos_id']);
            }else{
                $query->where('pos_id', $search['pos_id']);
            }
        }

        if (!empty($search['maxamount'])) {
            $query->where('gross', '<=', $search['maxamount']);
        }

        if (!empty($search['minamount'])) {
            $query->where('gross', '>=', $search['minamount']);
        }

        if (!empty($search['amount'])) {
            $query->where('gross', '=', $search['amount']);
        }

        if (!empty($search['customergsm'])) {
            $query->where('gsm_number', $search['customergsm']);
        }

        // Date Range
        if (isset($search['both_created_updated_at']) && !empty($search['both_created_updated_at'])) {
            //// for account statement report
            if (!empty($search['daterange'])) {
                $query->where(function($q) use ($search){
                    $q->whereBetween('created_at', [ManipulateDate::startOfTheDay($search['from_date']), ManipulateDate::endOfTheDay($search['to_date'])])
                        ->orWhereBetween('updated_at', [ManipulateDate::startOfTheDay($search['from_date']), ManipulateDate::endOfTheDay($search['to_date'])]);
                });
            }
        } elseif (!empty($search['daterange'])) {
            //// for other export report
            $query->whereBetween($orderBy, [ManipulateDate::startOfTheDay($search['from_date']), ManipulateDate::endOfTheDay($search['to_date'])]);
        }

//// old if condition for daterange
//        if (!empty($search['daterange'])) {
//            $query->whereBetween($orderBy, [ManipulateDate::startOfTheDay($search['from_date']), ManipulateDate::endOfTheDay($search['to_date'])]);
//        }


        if (!empty($search['currency']) || !empty($search['currency_id'])) {
            if (isset($search['currency_id'])){
                $search['currency'] = $search['currency_id'];
            }
            if(is_array($search['currency'])) {
                $query->whereIn('currency_id', $search['currency']);
            }else{
                $query->where('currency_id', $search['currency']);
            }
        }

        //search by installment
       if (!empty($search['installment'])) {
          if(is_array($search['installment'])) {
             $query->whereIn('installment', $search['installment']);
          }else{
             $query->where('installment', $search['installment']);
          }
       }

       // search by integrators
       if (!empty($search['integrator_id'])) {
          $integrator = SaleIntegrator::whereIn('integrator_id', $search['integrator_id'])->pluck('sale_id')->toArray();
          if(is_array($integrator) && count($integrator) > 0 ){
             $query->whereIn('id', $integrator);
          }else{
             $query->where('id', $integrator);
          }
       }

        // Search by refund history data
        if (isset($search['by_refund_history']) && !empty($search['by_refund_history'])) {
            if(is_array($search['by_refund_history'])) {
                $query->orWhereIn('id', $search['by_refund_history']);
            }
        }

        if (!empty($search['searchkey'])) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search['searchkey'] . '%')
                    ->orWhere('currency_symbol', 'like', '%' . $search['searchkey'] . '%')
                    ->orWhere('net', 'like', '%' . $search['searchkey'] . '%')
                    ->orWhere('fee', 'like', '%' . $search['searchkey'] . '%')
                    ->orWhere('gross', 'like', '%' . $search['searchkey'] . '%')
                    ->orWhere('cost', 'like', '%' . $search['searchkey'] . '%')
                    ->orWhere('product_price', 'like', '%' . $search['searchkey'] . '%')
                    ->orWhere('user_commission', 'like', '%' . $search['searchkey'] . '%')
                    ->orWhere('merchant_commission', 'like', '%' . $search['searchkey'] . '%')
                    ->orWhere('merchant_name', 'like', '%' . $search['searchkey'] . '%')
                    ->orWhere('payment_id', 'like', '%' . $search['searchkey'] . '%')
                    ->orWhere('order_id', 'like', '%' . $search['searchkey'] . '%')
                    ->orWhere('merchant_id', 'like', '%' . $search['searchkey'] . '%')
                    ->orWhere('invoice_id', 'like', '%' . $search['searchkey'] . '%')
                    ->orWhereHas('saleBilling', function ($query) use ($search) {
                        $query->where('card_holder_name', 'like', '%'. $search['searchkey'] . '%');
                 });
            });
        }

        $query->orderBy($orderBy, $orderByDesc);

        if ($paginate === true) {
            $query = $query->paginate($search['page_limit']);
//            if(Auth::user()->id == 1297){
//                $res = str_replace(array('?'), array('\'%s\''), $query->toSql());
//                $query = vsprintf($res, $query->getBindings());
//                dd($query);
//            }else{
//                $query = $query->paginate($search['page_limit']);
//            }

        } else {

            if (!empty($search['page_limit'])) {
                $query->limit($search['page_limit']);
            }

            $query = $query->get();
        }

        return $query;
    }

    public static function getCashOutAccountNo($iban){
        return substr($iban, -8);
    }


    public function cashOutAutomationProcess($withdrawal_obj, $userObj, $currencySettingsObj, $action_user_type, $authObj = null)
    {
        $status_code = '';
        $status_message = '';

        $flow_type = 0;
        $shouldUpdate = false;


        if ($action_user_type == Profile::ADMIN){
            $max_limit = $currencySettingsObj->max_automation_cashout_limit_for_approval;
        }else{
            $max_limit = $currencySettingsObj->max_automation_cashout_limit;
        }

        if ($withdrawal_obj->gross <= $max_limit){
            $request_data = [
                "ToIban" => $withdrawal_obj->iban,
                "ToAccountNo" => substr($withdrawal_obj->iban, -8),
                "ToName" => $withdrawal_obj->name,
                "TransactionId" => $withdrawal_obj->payment_id,
                "Amount" => $withdrawal_obj->net,
                "Description" => $userObj->customer_number,
                "NationalNoOrTaxNo" => $userObj->tc_number,
                "GroupId" => null
            ];
            $cashInOut = new CashInOut();
            $cashInOut->cashOut($request_data);


            $status_code = $cashInOut->sipayStatusCode;



            if ($cashInOut->sipayStatusCode == 100) {
                if ($action_user_type != Profile::ADMIN){
                    $flow_type = Withdrawal::FLOW_TYPE_AUTO;
                    $shouldUpdate = true;
                }


                if (!empty($authObj) && $authObj->user_type = Profile::ADMIN){
                    $withdrawal_obj->admin_approve_by_id = $authObj->id;
                    $withdrawal_obj->admin_apprrove_by_name = $authObj->name;
                    $flow_type = Withdrawal::FLOW_TYPE_ADMIN_AUTO;
                    $shouldUpdate = true;
                }

            }else{

                $transactionState = WithdrawalOperation::TYPE_WITHDRAWAL_REJECT;
                if ($withdrawal_obj->transaction_state_id == TransactionState::STANDBY) {
                    $transactionState = WithdrawalOperation::TYPE_AML_REJECT;
                }

                $withdrawOperation = new WithdrawalOperation();
                $withdrawOperation->addWithdrawalOperation($withdrawal_obj, $transactionState);
                $status_code = 31;
            }

            $status_message = $cashInOut->sipayStatusMessage;



        }else{
            if ($action_user_type != Profile::ADMIN){
                $flow_type = Withdrawal::FLOW_TYPE_ADMIN_MANUAL;
                $shouldUpdate = true;
            }else{

            }

        }

        if (!empty($flow_type)){
            $shouldUpdate = true;
            $withdrawal_obj->flow_type = $flow_type;
        }

        if ($shouldUpdate){
            $withdrawal_obj->save();
        }



        return [$status_code, $status_message];


    }

    public static function getDbFormattedAmount($amount, $length = 4, $currency_symbol = '', $decimal_secprator = '.')
    {
        return Number::format($amount, $length, $currency_symbol,$decimal_secprator);
    }

    /**
     * @param $orderList
     */
    public function ConvertFailedTransactionToSuccess($orderList){
        $processStatus = 0;
        $successList = [];
        $failList = [];
        $statusMessage = '';

        if (!empty($orderList)) {

            foreach ($orderList as $order_id) {

                $saleObj = Sale::where('order_id', $order_id)->where('transaction_state_id', TransactionState::FAILED)->first();

                if (!empty($saleObj)) {

                    //take data form log

                    $pos_id = $saleObj->pos_id;
                    $installment = $saleObj->installment;
                    $campaign_id = $saleObj->campaign_id;
                    $allocation_id = $saleObj->allocation_id;
                    $mobile = $saleObj->gsm_number;
                    $userObj = null;
                    $dpl_id = $saleObj->dpl_id; //120
                    $dplObj = DPL::find($dpl_id);
                    $dpl_token = $dplObj->token ?? '';
                    $payment_source = $saleObj->payment_source;

                    if (!empty($mobile)) {
                        $userObj = User::where('user_type', User::CUSTOMER)->where('phone',$mobile)->first();
                    }

                    $purchaseRequestObj = PurchaseRequest::where('ref',$order_id)->first();

                    if (empty($purchaseRequestObj)) {
                        $tmpPaymentRecord = new TemporaryPaymentRecord();
                        $purchaseRequestObj = $tmpPaymentRecord->convertTmpToPurchaseRequest($order_id);
                    }
                    
                    $purchaseRequestObj = (new PurchaseRequestData())->getPurchaseRequestWithData($purchaseRequestObj);
                    
                    $payment_method = PaymentRecOption::CREDITCARD;
                    $transactionable_type = config('constants.TRANSACTION_TYPE.SALE');

                    $ac_title = 'Credit Card';

                    if ($saleObj->payment_type_id == PaymentRecOption::MOBILE) {
                        $payment_method = PaymentRecOption::MOBILE;
                        $ac_title = 'Mobile Payment';
                    } elseif ($saleObj->payment_type_id == PaymentRecOption::SIPAYWALLET) {
                        $payment_method = PaymentRecOption::SIPAYWALLET;
                        $ac_title = config('brand.name') . ' Wallet';
                    }

                    $ccpayment = new CCPayment();

                    if (!empty($purchaseRequestObj)) {

                        $saleObjReplicate = $saleObj->replicate();

                        $saleTransactionObj = Transaction::where('transactionable_type', $transactionable_type)
                            ->where('transactionable_id', $saleObj->id)->first();

                        if ($payment_method == PaymentRecOption::CREDITCARD){
                            $card_holder_name = '';
                            $card = $this->customEncryptionDecryption($saleObjReplicate->credit_card_no, config('app.brand_secret_key'), 'decrypt');

                            $paymentProvider = new PaymentProvider();

                            $cardInfo = $paymentProvider->getCardInfoByCardNo($card);

                            $posObj = Pos::find($pos_id);
                            $bank = new Bank();
                            $bankObj = $bank->findBankByID($posObj->bank_id);

                            $bankOrderStatusObj = new BankOrderStatus();
                            list($bankResponseCode, $ref_number) = $bankOrderStatusObj->getOderStatusFromBank($purchaseRequestObj,
                                1);

                            if ($bankResponseCode == 100) {

                                $extras = [
                                    'payment_method' => $payment_method,
                                    'pos_id' => $posObj->id,
                                    'campaign_id' => $campaign_id,
                                    'allocation_id' => $allocation_id,
                                    'card_type' => $cardInfo["card_type"],
                                    'issuer_bank' => $cardInfo['issuer_name'],
                                    'actual_issuer_bank' => $cardInfo['actual_issuer_name'] ?? '',
                                    'card_holder_bank' => $cardInfo['issuer_name'],
                                    'installment' => $installment,
                                    'posObj' => $posObj,
                                    'credit_card_no' => $card,
                                    'remote_order_id' => $ref_number,
                                    'gsm_number' => $mobile,
                                    'request_ip' => $purchaseRequestObj->ip,
                                    'dpl_token' => $dpl_token,
                                    'card_holder_name' => $card_holder_name,
                                    'result' => 'Approved(' . $ref_number . ')',
                                    'payment_source' => $payment_source,
                                ];

                                // Process Payment for Sale, Purchase and Transaction
                                $processStatus = $ccpayment->processPayment($purchaseRequestObj, $ac_title, true, $userObj, $extras);
                            }
                        }

                        if ($processStatus == 100) {
                            array_push($successList,$order_id);

                            $newSaleObj = Sale::where('order_id',$order_id)->first();
                            $newSaleObj->payment_id = $saleObjReplicate->payment_id;
                            $newSaleObj->save();
                            $newSaleTransactionObj = Transaction::where('transactionable_type', config('constants.TRANSACTION_TYPE.SALE'))
                                ->where('transactionable_id', $newSaleObj->id)->first();

                            $newSaleTransactionObj->payment_id = $saleObjReplicate->payment_id;
                            $newSaleTransactionObj->save();

                            if (!empty($newSaleObj->purchase_id)) {
                                $newPurchaseObj = Purchase::find($newSaleObj->purchase_id);
                                $newPurchaseTransactionObj = Transaction::where('transactionable_type', config('constants.TRANSACTION_TYPE.PURCHASE'))
                                    ->where('transactionable_id', $newPurchaseObj->id)->first();
                                $newPurchaseTransactionObj->payment_id = $saleObjReplicate->payment_id;
                                $newPurchaseTransactionObj->save();
                            }
                        }else{
                            array_push($failList,$order_id);
                        }
                    }
                }
            }
        }

        return $statusMessage.'SuccessList= '.implode(",",$successList).'. FailList = '.implode(",", $failList);
    }

    public function conditionalCotCalculation($pos, $issuerName, $cardProgram, $card_type,
                                              $amount , $card_scheme = null, $card_country_code = null, $posRiskyCountriesCollection = null){
        $cotPercentage = 0;
        $cotFixed = 0;
        $pos_program_column_name = GlobalUser::isAllowedMultiplePosPrograms() ? $pos->pos_program  : $pos->program;


        if ($card_type == 1 && $card_scheme == "amex") {
            if (self::isForeignCard($issuerName)) {
                list($cotPercentage, $cotFixed) = $this->chooseForeignCardCotCommissionsWithPosRiskyCountries($pos->id, $card_country_code, $posRiskyCountriesCollection, $pos->foreign_amex_cot_percentage, $pos->foreign_amex_cot_fixed);
            } else {
                $cotPercentage = $pos->local_amex_cot_percentage;
                $cotFixed = $pos->local_amex_cot_fixed;
            }
        }
        else if (self::isForeignCard($issuerName)) { // foreign card
            list($cotPercentage, $cotFixed) = $this->chooseForeignCardCotCommissionsWithPosRiskyCountries($pos->id, $card_country_code, $posRiskyCountriesCollection, $pos->foreign_cc_cot_percentage, $pos->foreign_cc_cot_fixed);
        }
        // same program same bank
        else if($card_type == 1 && $cardProgram == $pos_program_column_name && $issuerName == $pos->bank_name){

            $cotPercentage = $pos->same_program_same_bank_cot_percentage;
            $cotFixed = $pos->same_program_same_bank_cot_fixed;

        }
        // same program diffrent bank
        else if($card_type == 1 && $cardProgram == $pos_program_column_name && $issuerName != $pos->bank_name){

            $cotPercentage = $pos->same_program_diffrent_bank_cot_percentage;
            $cotFixed = $pos->same_program_diffrent_bank_cot_fixed;
        }
        //for Credit card
        else if ($card_type == 1) {

            if ($issuerName == $pos->bank_name) {
                $cotPercentage = $pos->on_us_cc_cot_percentage;
                $cotFixed = $pos->on_us_cc_cot_fixed;
            } else {
                $cotPercentage = $pos->not_us_cc_cot_percentage;
                $cotFixed = $pos->not_us_cc_cot_fixed;
            }

        }
        //for Debit card
        else if($card_type == 2){
            if ($issuerName == $pos->bank_name) {
                $cotPercentage = $pos->debit_cot_percentage;
                $cotFixed = $pos->debit_cot_fixed;
            } else {
                $cotPercentage = $pos->not_us_debit_cot_percentage;
                $cotFixed = $pos->not_us_debit_cot_fixed;
            }
        }

        $calculateAmount = (($amount * $cotPercentage) / 100) + $cotFixed;

        return [$calculateAmount,$cotPercentage,$cotFixed];
    }

    public function merchantSaleCommission(
        $amount, $issuer_name, $merchant_id, $currency_id, $pos_id, $installment,
        $payment_type_id = PaymentRecOption::CREDITCARD,
        $merchantCommissionObj = null, $card_type = 'DEBIT CARD',
        $merchantPosCommissionObj = null,
        $is_comission_from_user = 0,
        $is_single_payment_commission = false,
        $is_imported_transaction = false,
        $is_pos_based_imported_transaction = false,
        $is_commercial_card = false,
        $card_program = "",
        $is_calculation_from_imported_transaction_setup = false,
        $is_imported_transaction_type_fp_mt = false,
        $card_scheme = ''
    ){
        $merchant_commission_percentage = 0;
        $merchant_commission_fixed = 0;
        $end_user_commission_percentage = 0;
        $end_user_commission_fixed = 0;


       $merchantCommission = new MerchantCommission();



        $is_foreign_card = self::isForeignCard($issuer_name);
        if ($is_foreign_card){
            if (empty($merchantCommissionObj)) {
                $merchantCommissionObj = $merchantCommission->getMCommissionByMIdPType($merchant_id, $payment_type_id, $currency_id);
            }

            if (!empty($merchantCommissionObj) && $merchantCommissionObj->is_foreign_card_commission_enable){
                $merchant_commission_percentage = $merchantCommissionObj->merchant_commission;
                $merchant_commission_fixed = $merchantCommissionObj->merchant_commission_fixed;
                $end_user_commission_percentage = $merchantCommissionObj->user_commission;
                $end_user_commission_fixed = $merchantCommissionObj->user_commission_fixed;
            }else{
                if( !empty($merchantPosCommissionObj) ){
                    $merchant_commission_percentage = $merchantPosCommissionObj->com_percentage;
                    $merchant_commission_fixed = $merchantPosCommissionObj->com_fixed;
                    $end_user_commission_percentage = $merchantPosCommissionObj->end_user_com_percentage;
                    $end_user_commission_fixed = $merchantPosCommissionObj->end_user_com_fixed;
                }
            }
            if (GlobalCommission::isAllowAmexCardCommission($merchantCommissionObj, $card_type, $card_scheme)){
                [$merchant_commission_percentage, $merchant_commission_fixed, $end_user_commission_percentage, $end_user_commission_fixed] = $this->getAmexCardCommission($merchantCommissionObj, $is_foreign_card);
            }
        }else{

            if ($card_type != 'CREDIT CARD'){
                if (empty($merchantCommissionObj)) {
                    $merchantCommissionObj = $merchantCommission->getMCommissionByMIdPType($merchant_id, $payment_type_id, $currency_id);
                }
            }

           if (!empty($merchantCommissionObj) && isset($merchantCommissionObj->is_debit_card_commission_enable)
           && $merchantCommissionObj->is_debit_card_commission_enable && $card_type != 'CREDIT CARD' ){

              $merchant_commission_percentage = $merchantCommissionObj->merchant_debit_card_commsission_percentage;
              $merchant_commission_fixed = $merchantCommissionObj->merchant_debit_card_commsission_fixed;
              $end_user_commission_percentage = $merchantCommissionObj->user_debit_card_commsission_percentage;
              $end_user_commission_fixed = $merchantCommissionObj->user_debit_card_commsission_fixed;
           }else{

              if (empty($merchantPosCommissionObj) || ( $is_imported_transaction && $is_commercial_card ) ){
                  $merchantPosCommission = new  MerchantPosCommission();
                  $merchantPosCommissionObj = $merchantPosCommission->getMerchantPosCommissionByInstallment($merchant_id, $pos_id, $installment, $is_commercial_card, $card_program, $currency_id);
              }


              if (!empty($merchantPosCommissionObj)){
                 $merchant_commission_percentage = $merchantPosCommissionObj->com_percentage;
                 $merchant_commission_fixed = $merchantPosCommissionObj->com_fixed;
                 $end_user_commission_percentage = $merchantPosCommissionObj->end_user_com_percentage;
                 $end_user_commission_fixed = $merchantPosCommissionObj->end_user_com_fixed;
              }
               if (GlobalCommission::isAllowAmexCardCommission($merchantCommissionObj, $card_type, $card_scheme)){
                   [$merchant_commission_percentage, $merchant_commission_fixed, $end_user_commission_percentage, $end_user_commission_fixed] = $this->getAmexCardCommission($merchantCommissionObj, $is_foreign_card);
               }
           }

        }

        // Replacing commission for single installment if it is in SinglePaymentMerchantCommission
        if ($is_single_payment_commission === true) {
            $merchant_commission_percentage = $merchantPosCommissionObj->com_percentage;
            $merchant_commission_fixed = $merchantPosCommissionObj->com_fixed;
            $end_user_commission_percentage = $merchantPosCommissionObj->end_user_com_percentage;
            $end_user_commission_fixed = $merchantPosCommissionObj->end_user_com_fixed;
        }


        if ($is_imported_transaction) {

            /*if($is_pos_based_imported_transaction){
                if(!$is_calculation_from_imported_transaction_setup && !empty($merchantPosCommissionObj)){
                    $merchant_commission_percentage = $merchantPosCommissionObj->com_percentage;
                    $merchant_commission_fixed = $merchantPosCommissionObj->com_fixed;
                }
                if($is_calculation_from_imported_transaction_setup){
                    list($merchant_commission_percentage, $merchant_commission_fixed) = $this->getMerchantCommissionsForImportedTransactions($merchant_id, $payment_type_id, $currency_id, $merchantCommissionObj, $merchant_commission_percentage, $merchant_commission_fixed);
                }

            }else{
                list($merchant_commission_percentage, $merchant_commission_fixed) = $this->getMerchantCommissionsForImportedTransactions($merchant_id, $payment_type_id, $currency_id, $merchantCommissionObj, $merchant_commission_percentage, $merchant_commission_fixed, [
                    'is_foreign_card' => $is_foreign_card,
                    'is_imported_transaction_type_fp_mt' => $is_imported_transaction_type_fp_mt
                ]);
            }*/


            list($merchant_commission_percentage, $merchant_commission_fixed) = $this->getMerchantCommissionsForImportedTransactions($merchant_id, $payment_type_id, $currency_id, $merchantCommissionObj, $merchant_commission_percentage, $merchant_commission_fixed, $is_foreign_card, $is_imported_transaction_type_fp_mt);

        }

        // $user_fee = (($amount / 100) * $end_user_commission_percentage) + $end_user_commission_fixed;
        // $merchant_fee = ((($amount + $user_fee) / 100) * $merchant_commission_percentage) + $merchant_commission_fixed;

        list(
            $merchant_fee,
            $user_fee,
            $merchant_commission_percentage,
            $merchant_commission_fixed,
            $end_user_commission_percentage,
            $end_user_commission_fixed
        ) = GlobalCommission::merchantFeeCalcualtion($is_comission_from_user, $amount, $merchant_commission_percentage, $merchant_commission_fixed, $end_user_commission_percentage, $end_user_commission_fixed);


        return [
            $merchant_fee,
            $user_fee,
            $merchant_commission_percentage,
            $merchant_commission_fixed,
            $end_user_commission_percentage,
            $end_user_commission_fixed
        ];

    }

    public function getMerchantCommissionsForImportedTransactions($merchant_id, $payment_type_id, $currency_id, $merchantCommissionObj = null, $merchant_commission_percentage = 0, $merchant_commission_fixed = 0, $is_foreign_card = false, $is_imported_transaction_type_fp_mt = false){
        if (empty($merchantCommissionObj)) {
            $merchantCommissionObj = (new MerchantCommission())->getMCommissionByMIdPType($merchant_id, $payment_type_id, $currency_id);
        }

        if($merchantCommissionObj->is_enable_imported_transaction_commission) {

            $is_fp_mt_foreign_card = $is_imported_transaction_type_fp_mt && $is_foreign_card && $merchantCommissionObj->is_foreign_card_commission_enable;

            if ($is_fp_mt_foreign_card) {
                $merchant_commission_fixed = $merchantCommissionObj->merchant_commission_fixed;
                $merchant_commission_percentage = $merchantCommissionObj->merchant_commission;
            } else {
                $merchant_commission_fixed = $merchantCommissionObj->imported_transaction_commission_fixed;
                $merchant_commission_percentage = $merchantCommissionObj->imported_transaction_commission_percentage;
            }

        }
        return [$merchant_commission_percentage, $merchant_commission_fixed];
    }

    public static function isForeignCard($issuer_name){
        if (empty($issuer_name) || mb_strtoupper($issuer_name) == "UNKNOWN") {
            return true;
        }
        return false;
    }

    public static function getCardType($cardNo)
    {
        $card = self::getCreditCardType($cardNo);

        if ($card == "visa") {
            return "VISA";
        } else if ($card == "mastercard") {
            return "MASTER CARD";
        } else if ($card == "amex") {
            return "AMEX";
        } else if ($card == "diners") {
            return "DINERS";
        } else if ($card == "discover") {
            return "DISCOVER";
        } else if ($card == "jcb") {
            return "JCB";
        } else if ($card == "any") {
            return "ANY";
        } else {
            return 0;
        }

    }

    public static function getCreditCardType($str, $format = 'string')
    {

        if (empty($str)) {
            return false;
        }

        $matchingPatterns = [
            'visa' => '/^4/',
            'mastercard' => '/^(5[1-5]|6(?!5))/',
            'amex' => '/^3[47]/',
            'diners' => '/^3(?:0[0-5]|[68])/',
            'discover' => '/^6(?:011|5)/',
            'jcb' => '/^(?:2131|1800|35\d{3})/',
            'any' => '/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/',
            'cup' => '/^(62|81)\d+$/',
            'troy' => '/^(?:9792|65\d{2}|36|2205)\d{12}$/',
        ];

        $ctr = 1;
        foreach ($matchingPatterns as $key=>$pattern) {
            if (preg_match($pattern, $str)) {
                return $format == 'string' ? $key : $ctr;
            }
            $ctr++;
        }
    }

    public static function getColumnLang($object)
    {
        $result = 'Undefined';

        if ($object instanceof Country) {
            $result =  app()->getlocale() == 'en' ? $object->country_name : $object->country_name_tr;
        } elseif ($object instanceof Sector) {
            $result =  app()->getlocale() == 'en' ? $object->name : $object->name_tr;
        } elseif ($object instanceof Settlement || $object instanceof \common\integration\Models\Settlement) {
            $result =  app()->getlocale() == 'en' ? $object->name : $object->name_tr;
        }elseif ($object instanceof ServiceType)
        {
            $result =  app()->getlocale() == 'en' ? $object->name : $object->name_tr;
        }
        return $result;
    }

    public static function calculateCommission($amount, $commission_percentage,$commission_fixed, $is_comission_from_user = 0){
        if($is_comission_from_user == 0){
            return ($amount * $commission_percentage/100)  + $commission_fixed;
        }
        return GlobalCommission::reverseCalculation($amount, $commission_fixed, $commission_percentage);
    }

    public static function makeArrayToObject($array, $instance = null){
        $res = null;

        if (is_array($array) && count($array) > 0){
            try {
                if (!empty($instance)){
                    $res = $instance->forceFill($array);
                }else{
                    $res = json_decode(json_encode($array));
                }
            }catch (\Throwable $exception){

            }

        }
        return $res;
    }

    public static function getAllTimeZones(){
       return \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
    }

   public static function convertDatetimeToMerchantTimezone($datetime, $timezone='Europe/Istanbul'){
      return Carbon::parse($datetime)->timezone($timezone);
   }

    public static function encodeToUtf8($string) {

        // TECHNICAL DISSCUSSION WITH RIFAT VAI AND TAREQ VAI
        $string = (string)$string;
        return Encode::toUtf8($string);

        //$string = (string)$string;
        //return mb_convert_encoding($string, "UTF-8", mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", true));

    }

   public static function maskCreditCardFormParams($values,$form){

      usort($values, function($a, $b){
         return strlen($a) < strlen($b);
      });

      if(is_array($values) && !empty($form)){
         foreach ($values as $key=>$value){

            if(strlen($value) > 15){
               $replace = CommonFunction::creditCardNoMasking($value);
            }else{
               $replace = str_repeat('*', strlen($value));
            }

            $form = str_replace($value, $replace,$form);
         }
      }
      return $form;
   }

    public static function getBirthYearToDateOfBirth($birthYear, $type = null): string
    {
       $birthYear =   $birthYear . '-01-01';
        if($type == 'slash'){
           $birthYear =  str_replace('/', '-', $birthYear);
        }
       return $birthYear;
    }

    public function basicAuthVerification ($username, $password, $action_name = '')
    {
        $status = config('apiconstants.API_UNAUTHORIZED');

        if (!empty($username) && !empty($password)) {
            $basic_username = $basic_password = '';

            if (!empty($action_name)) {
                if ($action_name == config('constants.BASIC_AUTH_ACTION_NAMES.SERVICE_CREDENTIAL')) {

                    $serviceCredentialObj = (new ServiceCredential())->getData(
                        ['service_client_id' => $username, 'find_first' => true],
                        false
                    );
                    $basic_username = $serviceCredentialObj->service_client_id ?? '';
                    $basic_password = self::customEncryptionDecryption($serviceCredentialObj->service_client_secret ?? '', config('app.brand_secret_key'), 'decrypt');

                } elseif ($action_name == config('constants.BASIC_AUTH_ACTION_NAMES.USER_VERIFICATION')) {

                    $basic_username = config('constants.USER_VERIFICATION_BASIC_USERNAME');
                    $basic_password = config('constants.USER_VERIFICATION_BASIC_PASSWORD');

                }
            } else {

                $basic_username = config('constants.BASIC_AUTH_USER');
                $basic_password = config('constants.BASIC_AUTH_PSWD');

            }

            if ($username == $basic_username && $password == $basic_password) {
                $status = config('apiconstants.API_SUCCESS');
            }
        }

        return $status;
    }



    public static function setDplSession($dplToken, $ref, $ccpayment = null)
    {
        $dpl = DPL::where('token', $dplToken)
            ->where(function ($query) {
                $query->where('status', DPL::ACTIVE)
                    ->orWhere('status', DPL::USED);
            })->first();

        if (empty($dpl)) {
            abort(404, 'This link is expired');
        }

        $companyObj = new Company();
        $merchant_user = $dpl->merchants->user;
        if (!empty($merchant_user['company_id'])) {
            $company = $companyObj->getCompanyById($merchant_user['company_id']);
            if (!empty($company)) {
                $dpl->company_logo = $company->logo;
            }
        }

        $dpl_settings = (new DPLSetting())->getDPLAgreementByMerchantID($dpl->merchant_id);
        if (!empty($dpl_settings)) {
            GlobalFunction::setBrandSession('dpl_settings', $dpl_settings, $ref);
            $dpl_agreements = $dpl_settings->dplAgreements;
            if (count($dpl_agreements) > 0) {
                GlobalFunction::setBrandSession('dpl_agreements', $dpl_agreements, $ref);
            }
        }

        if($ccpayment instanceof CCPayment){
            $ccpayment->dpl_obj = $dpl;
            $ccpayment->dpl_settings_obj = $dpl_settings;
        }

        GlobalFunction::setBrandSession('dpl_token', $dplToken, $ref);
        ///Checking DPL
        $dpl->unsetRelations();
        GlobalFunction::setBrandSession('dpl', $dpl, $ref);
    }

    public static function checkPaymentSetupHasPermission($payment_type = PaymentReceiveOption::CREDIT_CARD){
        $paymentReceiveOptionIds = (new PaymentReceiveOption)->getPaymentReceiveOptions()
                    ->pluck('id')
                    ->toArray();
        if(in_array($payment_type,$paymentReceiveOptionIds)){
            return true;
        }
        return false;
    }

    public static function nameCaseConversion ($name, $case = 'title') {
        return Str::customCaseConversion($name, $case);
    }

    public static function checkRealCardHolderName($card_holder_name, $extra_card_holder_name)
    {
        $status = '';
        if (!empty($extra_card_holder_name)) {
            if(!empty($card_holder_name)){
                $card_holder_name = Str::removeMultipleSpacesWithinString($card_holder_name);
            }
            $extra_card_holder_name = Str::removeMultipleSpacesWithinString($extra_card_holder_name);

            $card_holder_names = explode(' ', trim($card_holder_name));

            if(BrandConfiguration::checkCardHolderNameWithExtraCardHolderName()){
                $extra_card_holder_names = explode(' ', trim($extra_card_holder_name), count($card_holder_names));
            }else{
                $extra_card_holder_names = explode(' ', trim($extra_card_holder_name));
            }
            
            if (count($card_holder_names) == count($extra_card_holder_names)) {
                $status = 'True';
                foreach ($extra_card_holder_names as $key => $value) {
                    $value =  self::nameCaseConversion($value,'convertTurkishCharactersToEnglish');
                    $card_holder_names[$key] = self::nameCaseConversion($card_holder_names[$key],'convertTurkishCharactersToEnglish');
                    if (substr($value, 0, 2) != substr($card_holder_names[$key], 0, 2)) {
                        $status = 'False';
                        break;
                    }
                }
            } else {
                $status = 'False';
            }
        }
        return $status;
    }

    public static function setBrandCache($key, $data, $time_in_seconds, $reference=null)
    {
        return \common\integration\Utility\Cache::add($key, $data, $time_in_seconds, $reference);
    }

    public static function getBrandCache($key, $reference=null, $default_data = null)
    {
        return \common\integration\Utility\Cache::get($key, $reference, $default_data);
    }

    public static function hasBrandCache($key, $reference = null){
        return \common\integration\Utility\Cache::has($key, $reference);
    }

    public static function unsetBrandCache($key, $reference = null){
        \common\integration\Utility\Cache::forget($key, $reference);
    }

    public static function incrementBrandCache($key, $increment_by = null,$reference = null){
        \common\integration\Utility\Cache::increment($key, $increment_by, $reference);
    }

    public static function decrementBrandCache($key, $decrement_by = null, $reference = null){
        \common\integration\Utility\Cache::decrement($key, $decrement_by, $reference);
    }



    public static function concatenateEntities($first_entity, $second_entity, $separator="#"):string
    {
        return $first_entity.$separator.$second_entity;
    }

    public static function separateEntities($string, $separator="#"):array
    {
        return explode($string, $separator);
    }



    /**
     * for sha256 hashing
     */

    public static function getHashValue ($plainText)
    {
        $hash_secret_key = config("constants.HASH_MAC_SECRET_KEY");
        return DataCipher::hashMac(DataCipher::ALGO_SHA512, $plainText, $hash_secret_key);
//        return hash('sha256', $plainText);
    }

    public static function checkHashValue ($inputText, $dbHashValue)
    {
        $inputHash = self::getHashValue($inputText);
        return $inputHash == $dbHashValue;
    }



    public static function convertToUtf8Recursively($data)
    {
        return Encode::toUtf8($data);
    }

    public static function encodeHtmlSpecialCharacter($stringValue){
        return Encode::htmlSpecialChars($stringValue);
    }


    public static function convertArrayToXml($data, $root,$isSoap=null,$xmlns=null)
    {
        $xml = simplexml_load_string('<'.$root.'/>');
        self::recursiveArrayToXmlParser($data, $xml,$root,$xmlns);

        return $xml;
    }

    public static function recursiveArrayToXmlParser($data, $xml, $root=null, $xmlns=null)
    {
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                if( is_numeric($key) ){
                    $key = 'index'.$key; //dealing with <0/>..<n/> issues
                }
                if($key == $root) {
                    $sub_node = $xml->addChild($key,null,$xmlns);
                }else {
                    $sub_node = $xml->addChild($key);
                }
                self::recursiveArrayToXmlParser($value, $sub_node);
            } else {
                $xml->addChild("$key",htmlspecialchars("$value"));
            }
        }

        return $xml;

    }

    public static function getDefultLanguage($brands = [] , $constants = []){
        $lang = 'tr';
        if(empty($brands) || empty($constants)){
            return $lang;
        }
        if($brands['name_code'] == $constants['BRAND_NAME_CODE_LIST']['MP']){
            $lang = 'lt';
        }
        return $lang;
    }

    public static function convertXmlToObject($response = ''){
        if(empty($response)){
            return $response;
        }
        $response = Xml::stripInvalidChars($response);
        $result = self::convertXmlToArrayOrJson($response, true);
        return self::arrayToAssociativeObject($result);
    }
    public static function convertXmlToArray($response = '', $should_log = false, $action = null){
        if(empty($response)){
            return $response;
        }
        $result =  self::convertXmlToArrayOrJson($response, true);
        if($should_log){ //geting the trace of empty data for kuveyt turk
            if(!$result){
                (new ManageLogging())->createLog(
                    [
                        'action'=>$action,
                        'is_result_empty' =>empty($result)
                    ]
                );
                info($response??''); //since conversion making it error prone we have to get the direct string
            }

        }
        return self::arrayToAssociativeObject($result, true);
    }

    private static function convertXmlToArrayOrJson($response, $toArray = false){
        if(empty($response)){
            return $response;
        }
        libxml_use_internal_errors(TRUE);
        $result = new \SimpleXMLElement($response);
        return json_decode(json_encode($result), $toArray);
    }

    public static function arrayToAssociativeObject($array = [], $toArray = false){
        foreach ($array as $index => $arrayItem) {
            if (is_array($arrayItem)) {
                if(empty($arrayItem)){
                    $array[$index] = null;
                }else{
                    $array[$index] = self::arrayToAssociativeObject($arrayItem, $toArray);
                }

            }
        }

        if ($toArray){
            return $array;
        }
        return (object) $array;
    }

    public static function loginBlockTimeIncrement($user_type, $block_time_panel, $search_param, $max_attempt, $decayMinutes, $session_prefix = 'fail_login_attemps_counter'){

        $attempts = $max_attempt;
        $decayMinutes = $decayMinutes;
        $user_object = '';
        if(\common\integration\BrandConfiguration::allowLoginBlockTime()){

            $blockTimeSetting = (new BlockTimeSettings)->getDetails($block_time_panel);

            if($user_type[0] == User::CUSTOMER){
                $user = User::where('phone', $search_param)->whereIn('user_type', $user_type)->first();
            }else{
                $user = User::where('email', $search_param)->whereIn('user_type', $user_type)->first();
            }
     
            $attempts = 1;

            if(!empty($user) && !empty($blockTimeSetting)){
                $user_object = $user;
                $failedCacheKey = self::createSessionKey($user, $session_prefix );


                if(Self::hasBrandSession($failedCacheKey)){
                    $attempts = Self::getBrandSession($failedCacheKey) + 1;
                }
        
                Self::setBrandSession($failedCacheKey, $attempts);


                $user->failed_login_attempt = $attempts;
                $user->last_failed_login_datetime = Carbon::now();
                $user->save();

                // if($user->failed_login_attempt  <=  $max_attempt){
                //     $decayMinutes =  $blockTimeSetting->first_time;
                // }elseif($user->failed_login_attempt <= ($max_attempt * 2)){
                //     $decayMinutes =  $blockTimeSetting->second_time;
                // }elseif($user->failed_login_attempt <= ($max_attempt * 3)){
                //     $decayMinutes =  $blockTimeSetting->third_time;
                // }elseif($user->failed_login_attempt  > ($max_attempt * 3)){
                //     $decayMinutes =  $blockTimeSetting->third_time;
                //     if(!empty($user) && $user->update(['is_admin_verified' => Profile::LOCK_USER])){
                //         //$rateLimiter->clear($key);
                //         Self::unsetBrandSession($failedCacheKey);
                //     }
                // }



                if($user->failed_login_attempt  <=  $max_attempt){
    
                    $decayMinutes =  !empty($blockTimeSetting->first_time) ? $blockTimeSetting->first_time : $decayMinutes;
					
					if(BrandConfiguration::call([Mix::class, 'sendBlockEmail'])){
					
						$log_data['action'] = 'BLOCK_EMAIL_SMS_SEND';
						
						if($user->email){
							
							(new class {
								use SendEmailTrait;
							})->sendEmail(
								[
									'user_name' => Str::titleCase($user->name),
									'attempts' => $user->failed_login_attempt,
									'date_and_time' => ManipulateDate::format(2, $user->last_failed_login_datetime),
									'ip_address' => request()->ip(),
									'company_name'=> Str::replace('ş.', 'Ş.', Str::titleCase(config('brand.contact_info.company_full_name')))
								],
								'block_email',
								config('app.SYSTEM_NO_REPLY_ADDRESS'),
								$user->email,
								'',
								'block_email.block_email',
								app()->getLocale(),
							);
							
							$log_data['email'] = $user->email;
							
							
						}
						
						if($user->phone){
							
							(new class {
								use OTPTrait;
							})->sendSMS(
								"",
								__("Multiple unsuccessful login attempts were made to your :brand_name account. If this wasn't you, please change your password immediately. Contact us at :contact_no for suspicious activity.",
									[
										'brand_name' => Str::titleCase(config('brand.name')),
										'contact_no' => config('brand.contact_info.phone_number')
									]
								),
								$user->phone,
								0,
								['from_cronjob' => true]
							);
							
							$log_data['phone'] = $user->phone;
						}
						
						(new ManageLogging())->createLog($log_data);
						
					}
					
					
    
                }else{
    
                    // $decayMinutes =  $blockTimeSetting->third_time;
    
                    if(!empty($user) && $user->update(['is_admin_verified' => Profile::LOCK_USER, 'failed_login_attempt' => 0])){
                        //$rateLimiter->clear($key);

                        $panel_name_en = GlobalUser::getUserTypeList()[$user_type[0]];
                        $panel_name_tr = self::getContentForLocalization(GlobalUser::getUserTypeList()[$user_type[0]]); 
                        
                        $notification_text_en = $user->name. ' (' .$panel_name_en. ') is blocked.';
                        $notification_text_tr = $user->name. ' (' .$panel_name_tr. ') blocklandı.';

                        $input_data = [
                            'notification_data' => [
                                'admin_notification_action' => CommonNotification::USER_BLOCK,
                                'notification_action' => CommonNotification::USER_BLOCK,
                                'notification_url' => config('app.APP_ADMIN_URL') . "/block-account-settings?name=".$user->name,
                                'message_en' => $notification_text_en,
                                'message_tr' => $notification_text_tr,
                            ]
                        ];

                        (new class { use NotificationTrait; })->checkAndSendNotification(
                            [],
                            $input_data,
                            UserSetting::EMAIL_DISABLED,
                            UserSetting::SMS_DISABLED,
                            UserSetting::PUSH_NOTIFICATION_ENABLED,
                            $user
                        );

                        // sent mail to the admins user is locked

                        if(BrandConfiguration::allowSentBlockMailToAdmins()){
                            $panel_name = GlobalUser::getUserTypeList()[$user_type[0]];
                            GlobalUser::blockUserSentMail($user, $panel_name);
                        }

                        Self::unsetBrandSession($failedCacheKey);
                        Self::unsetBrandCache($failedCacheKey);

                        if(Self::hasBrandSession(BrandConfiguration::FORGET_PASSWORD)){
                            Self::unsetBrandSession(BrandConfiguration::FORGET_PASSWORD);
                        }
                        Self::setBrandSession(BrandConfiguration::FORGET_PASSWORD,true);
                    }
    
                }
            }
        }

        // info([
        //     'attempts' => $attempts,
        //     'decayMinutes' => $decayMinutes,
        //     'maxAttempts' => $max_attempt,
        // ]);

        return [$attempts, $decayMinutes, $max_attempt, $user_object];
    }

    public static function getContentForLocalization($content, $lan = 'tr'){
        $defult_lang = app()->getLocale();
        app()->setLocale($lan);
        $content = __($content);
        app()->setLocale($defult_lang);
        return $content;
    }

    public static function createSessionKey($user, $failed_login_attempts = 'failed_login_attempts')
    {
        if(!empty($user)){
            return $failed_login_attempts . '_' . $user->id . '_' . $user->email . '_' . $user->user_type;
        }

        return "";
    }

    public static function setFailedLoginAttempts($userObj, $session_prefix){

        $failed_login_attempt_key = Self::createSessionKey($userObj,$session_prefix);
        
        if(Self::hasBrandSession($failed_login_attempt_key)){
            Self::unsetBrandSession($failed_login_attempt_key);
        }

        $failed_login_attempt = 0;

        if(!empty($userObj)){
            $failed_login_attempt = $userObj->failed_login_attempt;
        };

        Self::setBrandSession($failed_login_attempt_key, $failed_login_attempt);
    }

    public static function setSessionLifeTime($user_type){
        $session_life_time = 10;
        
        try{
            if(BrandConfiguration::allowSessionTimeDynamic()){

                $session_life_time = 10;
                $blockTimeSetting = (new BlockTimeSettings)->getDetails($user_type);
                if(!empty($blockTimeSetting)){
                    $session_life_time = $blockTimeSetting->session_time;
                }
            }

            return $session_life_time;
        }catch(\Throwable $e){

        }


    }

    public static function getFullName(string $name = '', string $surname = ''): string
    {
        $full_name = $name;
        $full_name .= !empty($surname)
            ? ' ' . $surname
            : '';
        return $full_name;
    }

    public static function getFunctionCallingTrace():array
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $function_caller_trace = [];
        foreach ($backtrace as $trace) {
            if(array_key_exists('file',$trace) && array_key_exists('line',$trace)){
                if( strpos($trace['file'],'vendor')==false
                    && strpos($trace['file'],'Middleware')==false
                    && strpos($trace['file'],'public')==false
                    && strpos($trace['file'],'server')==false){
                    $function_caller_trace = array_merge($function_caller_trace, [substr($trace['file'],strrpos($trace['file'],DIRECTORY_SEPARATOR)+1,strlen($trace['file'])-strrpos($trace['file'],DIRECTORY_SEPARATOR)) =>  $trace['function'].' at '.$trace['line']]);
                }
            }
        }

        $function_caller_trace = array_reverse($function_caller_trace);
        array_pop($function_caller_trace);

        return $function_caller_trace;

    }



    public static function setOtpAttemptCounter($key){

        $key = BrandConfiguration::WRONG_OTP_ATTEMPT_PREFIX. $key;
        $attempt_count = 1;
        $timer = BrandConfiguration::WRONG_OTP_TIMER;
        if(Self::hasBrandCache($key)){
            $attempt_count = Self::getBrandCache($key) + $attempt_count;
        }
        Self::setBrandCache($key, $attempt_count, $timer);
    }

    public function checkSequerityQuestionAndPassword($data){
        if(!Auth::check()){
            return false;
        }

        $user = Auth::user();
        // dd($data, $user->answer_one, $user->question_one, $this->customEncryptionDecryption($user->answer_one, config('app.brand_secret_key'), 'decrypt'));
        if($user->question_one == $data['question'] && $this->customEncryptionDecryption($user->answer_one, config('app.brand_secret_key'), 'decrypt') == $data['answer'] && (new Profile)->ValidatePasswordOnly($data['password'], $user->password) == config('constants.SUCCESS_CODE')){
            // POP UP SHOULD BE SHOWN WHEN EVER THE IF ON SUCCESS FULL
            $key = BrandConfiguration::WRONG_OTP_ATTEMPT_PREFIX.  $user->id;
            Self::unsetBrandCache($key);
            return true;

        }

        return false;
    }

    public static function checkOtpMaxAttempt($user_id){

        $counter = 0;
        $key = BrandConfiguration::WRONG_OTP_ATTEMPT_PREFIX. $user_id;
        if(Self::hasBrandCache($key)){
            $counter = Self::getBrandCache($key);
        }
       
        if($counter >= BrandConfiguration::MAX_OTP_ATTEMPT){
            // POP UP SHOULD BE SHOWN WHEN EVER THE IF ON SUCCESS FULL
            //Self::unsetBrandCache($key);
            return true;
        }
        return false;
    }

    public static function brandFileNameConvention($content_changed = false){
        if(BrandConfiguration::emailContentChanges() && $content_changed){
            return config('brand.name_code').'_';
        }else{
            return '';
        }
    }

    public static function getFailedLoginInformation($userObj = null, $block_time_panel = []){
        $login_locked_time = config('app.login_locked_time_minutes');
        $failed_login_attempts = config('app.failed_login_attemps');
        $session_prefix = 'fail_login_attemps_counter';
        $max_attempt = config('app.failed_login_attemps');

        if(BrandConfiguration::allowLoginBlockTime() && !empty($userObj)){

            $user_type = Arr::isAMemberOf(
                $userObj->user_type,
                [
                    User::CUSTOMER,
                    User::MERCHANT,
                    User::ADMIN
                ]
            )
            ? $userObj->user_type
            : User::MERCHANT;

            $blockTimeSetting = (new BlockTimeSettings)->getDetails($user_type);
            $failedCacheKey = self::createSessionKey($userObj, $session_prefix);

            if(Self::hasBrandSession($failedCacheKey)){
                $failed_login_attempts = Self::getBrandSession($failedCacheKey) ;
            }

            // if($failed_login_attempts  <= $max_attempt){
            //     $login_locked_time =  $blockTimeSetting->first_time;
            // }elseif($failed_login_attempts <= ($max_attempt * 2)){
            //     $login_locked_time =  $blockTimeSetting->second_time;
            // }elseif($failed_login_attempts <= ($max_attempt * 3)){
            //     $login_locked_time =  $blockTimeSetting->third_time;
            // }elseif($failed_login_attempts  > ($max_attempt * 3)){
            //     $login_locked_time =  $blockTimeSetting->third_time;
            // }
            $login_locked_time =  !empty($blockTimeSetting->first_time) ? $blockTimeSetting->first_time : $login_locked_time;

        }

        return [
            $login_locked_time,
            $failed_login_attempts,
        ];
    }

   public static function creditCardNumberMasking($credit_card_no, $type=null, $masking_length = ''){

      $bin_digit_length = self::getCustomBinDigitLength();

      $masking_length = !empty($masking_length) ? $masking_length : $bin_digit_length;

      if ($type == 'deposit') {
         $masked_ccno = $credit_card_no ? "****" . substr($credit_card_no, -4) : $credit_card_no;
      } else {
         $masked_ccno = $credit_card_no ? substr($credit_card_no, 0, $masking_length) . "****" . substr($credit_card_no, -4) : $credit_card_no;

      }
      return $masked_ccno;
   }

   public function customBinDigit($card_number){

      $card_number = $this->removeWhiteSpace($card_number);

      $bin_digit_length = self::getCustomBinDigitLength();
      return substr($card_number, 0, $bin_digit_length);

   }

   public static function isEnableCustomBinDigit(){
      $is_enable_custom_bin_digit = false;
      if(\config('constants.IS_ENABLE_CUSTOM_DIGIT_BIN')){
         $is_enable_custom_bin_digit = true;
      }

      return $is_enable_custom_bin_digit;
   }

   public static function getCustomBinDigitLength(){

      $bin_digit_length = 6;

      if(self::isEnableCustomBinDigit()){
         $bin_digit_length =  \config('constants.CUSTOM_DIGIT_BIN');
      }

      return $bin_digit_length;
   }


    public static function isMonthlyDepositLimitExceed($currency_settings, $user_id, $currency_id, $amount, $extra =[]){

        $is_monthly_max_deposit_limit_exceed = false;
        $total_gross_amount = $number_of_rows = $total_net_amount = 0;

        if (!empty($currency_settings)) {
            $deposit = new Deposit();
            list($total_gross_amount, $number_of_rows, $total_net_amount) = $deposit->getTotalGrossAndNumberRow($user_id,$currency_id);
            $total_gross = $total_gross_amount + $amount;

            if (isset($extra['user_category']) && !empty($extra['user_category'])){
                $currencySetting = new CurrenciesSettings();
                if ($total_gross > $currency_settings->{$currencySetting->getColumnName($extra['user_category'],CurrenciesSettings::MAX_DEPOSIT_LIMIT)}){
                    $is_monthly_max_deposit_limit_exceed = true;
                }
            }
        }

        return [$is_monthly_max_deposit_limit_exceed, $total_gross_amount, $number_of_rows, $total_net_amount];
    }

    public static function isMonthlyWithdrawLimitExceed($currency_settings, $user_id, $currency_id, $amount, $extra =[]){

        $is_monthly_max_withdraw_limit_exceed = false;
        $total_gross_amount = $number_of_rows = $total_net_amount = 0;

        if (!empty($currency_settings)) {
            $withdrawal = new Withdrawal();
            list($total_gross_amount, $number_of_rows, $total_net_amount) = $withdrawal->getTotalGrossAndNumberRow($user_id, $currency_id);
            $total_gross = $total_gross_amount + $amount;

            if (isset($extra['user_category']) && !empty($extra['user_category'])){
                $currencySetting = new CurrenciesSettings();
                if ($total_gross > $currency_settings->{$currencySetting->getColumnName($extra['user_category'], CurrenciesSettings::MAX_WITHDRAW_LIMIT)}){
                    $is_monthly_max_withdraw_limit_exceed = true;
                }
            }
        }

        return [$is_monthly_max_withdraw_limit_exceed, $total_gross_amount, $number_of_rows, $total_net_amount];
    }


    public static function replaceSpecialCharFromXML($xml)
    {
        $xml = str_replace('a:', '', $xml);
        $xml = str_replace('s:', '', $xml);
        $xml = str_replace('u:', '', $xml);
        $xml = str_replace('i:', '', $xml);
        $xml = str_replace('o:', '', $xml);
        $xml = str_replace('nil="true"', '', $xml);
        $xml = str_replace('nil="false"', '', $xml);
        return $xml;
   }

   public static function isNotKYCVerifiedUser($user_category)
   {
       return Profile::NOT_VERIFIED == $user_category;
   }

    public static function getCustomerCategory($category_id){

        $category_label = "";
        $category_lists = BrandConfiguration::getUserCategoryList(\config('brand.name_code'), \config('constants.BRAND_NAME_CODE_LIST'));

        if (isset($category_id) && !empty($category_id) && isset($category_lists[$category_id])) {

            $category_name = $category_lists[$category_id];

            if ($category_id == User::NOT_VERIFIED) {

                $category_label = "<label class='m-0 p-0' style='color:rgb(189,195,199);'>".__($category_name)."</label>";

            } elseif ( $category_id == User::VERIFIED){

                $category_label = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__($category_name)."</label>";

            } elseif ($category_id == User::VERIFIED_PLUS){

                $category_label = "<label class='m-0 p-0' style='color:rgb(42,174,54);'>".__($category_name)."</label>";

            } elseif ($category_id == User::CONTRACTED){

                $category_label = "<label class='m-0 p-0' style='color:rgb(32,90,224);'>".__($category_name)."</label>";

            }
        }

        return $category_label;
    }

    public static function getSinglePaymentSource($payment_source_id)
    {
        return array_filter(self::getAllPaymentSource(), function ($k) use ($payment_source_id) {
            return $k == $payment_source_id;
        }, ARRAY_FILTER_USE_KEY);
    }

    public static function getAllPaymentSource()
    {

        $payment_source = [
            CCPayment::PAYMENT_SOURCE_PAID_BY_CC_3D_BRANDING => '3D Branding And Paid By CC',
            CCPayment::PAYMENT_SOURCE_PAID_BY_CC_2D_BRANDING => '2D Branding and Paid By CC',
            CCPayment::PAYMENT_SOURCE_MOBILE_PAYMENT => 'Mobile Payment',
            CCPayment::PAYMENT_SOURCE_WALLET_PAYMENT => 'Wallet Payment',
            CCPayment::PAYMENT_SOURCE_WHITE_LABEL_3D => 'White Label 3D',
            CCPayment::PAYMENT_SOURCE_WHITE_LABEL_2D => 'White Label 2D',
            CCPayment::PAYMENT_SOURCE_REDIRECT_WHITE_LABEL_3D => 'Redirected White Label 3D',
            CCPayment::PAYMENT_SOURCE_REDIRECT_WHITE_LABEL_2D => 'Redirected White Label 2D',
            CCPayment::PAYMENT_SOURCE_DPL_3D => 'DPL 3D',
            CCPayment::PAYMENT_SOURCE_DPL_2D => 'DPL 2D',
            CCPayment::PAYMENT_SOURCE_MP_3D => 'MP 3D',
            CCPayment::PAYMENT_SOURCE_MP_2D => 'MP 2D',
            CCPayment::PAYMENT_SOURCE_PAY_BY_CARDTOKEN_3D => 'Pay By Card Token 3D',
            CCPayment::PAYMENT_SOURCE_PAY_BY_CARDTOKEN_2D => 'Pay By Card Token 2D',
            CCPayment::PAYMENT_SOURCE_PAY_BY_MARKETPLACE_3D => 'Pay By Marketplace 3D',
            CCPayment::PAYMENT_SOURCE_PAY_BY_MARKETPLACE_2D => 'Pay By Marketplace 2D',
            CCPayment::PAYMENT_SOURCE_PAY_BY_REDIRECT_DIRECTLY => 'Pay By Redirect Directly',
            CCPayment::PAYMENT_SOURCE_ONE_PAGE_PAYMENT_DPL_3D => 'One Page Payment DPL 3D',
            CCPayment::PAYMENT_SOURCE_ONE_PAGE_PAYMENT_DPL_2D => 'One Page Payment DPL 2D',
            CCPayment::PAYMENT_SOURCE_DENIZ_DCC_CURRENCY_CONVERSION => 'Deniz DCC Currency Conversion',
            CCPayment::PAYMENT_SOURCE_RECURRING_PAYMENT => 'Recurring Payment',
            CCPayment::PAYMENT_SOURCE_TENANT_3D => 'Tenant 3D',
            CCPayment::PAYMENT_SOURCE_TENANT_2D => 'Tenant 2D',
            CCPayment::PAYMENT_SOURCE_PAVO_PAYMENT => 'Payment From Pavo',
            CCPayment::PAYMENT_SOURCE_OXIVO_PAYMENT => 'Payment From Oxivo',
            CCPayment::PAYMENT_SOURCE_HUGIN_PAYMENT => 'Payment From Hugin',
            CCPayment::PAYMENT_SOURCE_FP_MT => 'Payment From FP MT',
            CCPayment::PAYMENT_SOURCE_SARI_TAXI_PAYMENT => 'Payment From Sari Texi',
            CCPayment::PAYMENT_SOURCE_POINT_PAYMENT_2D => 'Point Payment 2D',
            CCPayment::PAYMENT_SOURCE_POINT_PAYMENT_3D => 'Point Payment 3D',
            CCPayment::PAYMENT_SOURCE_TEST_TRANSACTION_PAY_2D => 'Test Transaction 2D',
            CCPayment::PAYMENT_SOURCE_PAX_PAYMENT => 'Payment From Pax',
            CCPayment::PAYMENT_SOURCE_PAYGO_PAYMENT => 'Payment From Paygo'
        ];

        if (config('brand.name_code') == (config('constants.BRAND_NAME_CODE_LIST.SP') || config('constants.BRAND_NAME_CODE_LIST.PIN'))) {
            $payment_source += [
                CCPayment::PAYMENT_SOURCE_PAY_BY_WIX_3D => 'Payment From Wix 3D',
                CCPayment::PAYMENT_SOURCE_PAY_BY_WIX_2D => 'Payment From Wix 2D',
                CCPayment::PAYMENT_SOURCE_TAXI_YAPIKREDI_TOKEN_PAYMENT => 'Taxi Yapikredi Token Payment',
            ];
        } elseif (config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.FP')) {
            $payment_source += [
                CCPayment::PAYMENT_SOURCE_FASTPAY_WALLET_MOBILE_QR_PAYMENT => 'Fastpay Wallet Mobile QR Payment',
                CCPayment::PAYMENT_SOURCE_FASTPAY_SALE_TERMINAL_WALLET => 'Fastpay Sale Terminal Wallet',
                CCPayment::PAYMENT_SOURCE_FASTPAY_SALE_TERMINAL_NON_SECURE => 'Fastpay Sale Terminal Non-secure',
                CCPayment::PAYMENT_SOURCE_FASTPAY_SALE_TERMINAL_3D_SECURE => 'Fastpay Sale Terminal 3D Secure',
                CCPayment::PAYMENT_SOURCE_FASTPAY_SALE_MOBILE_WALLET => 'Fastpay Sale Mobile Wallet',
                CCPayment::PAYMENT_SOURCE_FASTPAY_SALE_MOBILE_NON_SECURE => 'Fastpay Sale Mobile Non-secure',
                CCPayment::PAYMENT_SOURCE_FASTPAY_SALE_MOBILE_3D_SECURE => 'Fastpay sale mobile 3D Secure',

            ];
        }

        if (BrandConfiguration::call([Mix::class, 'isAllowBrandImport']))
        {
            $payment_source += [
                CCPayment::PAYMENT_SOURCE_BRAND_IMPORT_PAYMENT_2D => __("Remote Non Secure"),
                CCPayment::PAYMENT_SOURCE_BRAND_IMPORT_PAYMENT_3D => __("Remote 3d Secure")
            ] ;
        }

        if(BrandConfiguration::call([Mix::class, 'isAllowVerifone'])) {
            $payment_source += [ CCPayment::PAYMENT_SOURCE_VERIFONE_PAYMENT => 'Payment From Verifone' ];
        }

        return $payment_source;
    }

    public static function isNotActivationCodeServiceVerified($user_category)
    {
        return in_array($user_category, [
            Profile::NOT_VERIFIED, Profile::VERIFIED
        ]);
    }
    
    public static function status($status_id, $flow_type='', $sale_type=Sale::Auth , $language = null, $extra=[],$payment_source = -1, $is_cancel =0){
        if(!empty($language)){
            app()->setLocale($language);
        }
        $new_status = "";
        if (!is_null($status_id)) {

            $details = (new TransactionState)->getTrsansactionStateById($status_id);
            $status_name = $details->name;
            if(app()->getLocale() == 'tr' && !empty($details->name_tr)){
                $status_name = $details->name_tr;
            }

            if ($status_id == 1) {
                // $new_status = "<label class='m-0 p-0' style='color:rgb(42,174,54);'>".__('Completed')."</label>";
                if($sale_type == Sale::PREAUTH){
                    $new_status = "<label class='m-0 p-0' style='color:rgb(42,174,54);'>".__("PreAuth Approved")."</label>";
                }
                elseif (!empty($extra['is_loyalty_point_sale'])) {
                    $new_status = "<label class='m-0 p-0' style='color:rgb(42,174,54);'>".__("Loyalty Point Sale")."</label>";
                }
                else{
                    $new_status = "<label class='m-0 p-0' style='color:rgb(42,174,54);'>".__($status_name)."</label>";
                }
            } elseif ($status_id == 2){
                // $new_status = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__('Rejected')."</label>";
                if (isset($extra['is_reversed']) && $extra['is_reversed'] == RefundHistory::REVERSED) {
                    $status_name = __('Reversed');
                }
                $new_status = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__($status_name)."</label>";
            } elseif ($status_id == 3){
                if (!empty($flow_type)
                    && ($flow_type == Withdrawal::FLOW_TYPE_AUTO || $flow_type == Withdrawal::FLOW_TYPE_ADMIN_AUTO)) {
                    $label = __('Sent to Finflow');

                    if (CashInOutManager::isEnabledService()) {
                        $label = __('Sent to Paratek');
                    }
                } else if(!empty($flow_type) && $flow_type == Withdrawal::FLOW_TYPE_IMPORTED_WITHDRAWAL){
                    $label = __('Sent to Bank');
                } elseif (!empty($sale_type) && $sale_type == Sale::PREAUTH){
                    $label = __(config('constants.PRE_AUTHORIZATION'));
                }else {
                    // $label = __('Pending');
                    $label =  $status_name;
                }

                if ($flow_type == Withdrawal::FLOW_TYPE_ADMIN && isset($extra['cashout_type']) 
                    && $extra['cashout_type'] == BtoC::CASHOUT_TO_WALLETGATE) {
                    $label = __('Waiting for Topup');
                }

                // $new_status = "<label class='m-0 p-0' style='color:rgb(243,157,18);'>".$label."</label>";
                $new_status = "<label class='m-0 p-0' style='color:rgb(243,157,18);'>".__($label)."</label>";

            } elseif ($status_id == 4){
                // $new_status = "<label class='m-0 p-0' style='color:rgb(44,196,203);'>".__('Stand By')."</label>";
                $new_status = "<label class='m-0 p-0' style='color:rgb(44,196,203);'>".__($status_name)."</label>";
            } elseif ($status_id == 5){
                // $new_status = "<label class='m-0 p-0' style='color:rgb(32,90,224);'>".__('Refunded')."</label>";

                $status_name = GlobalFunction::refundStatus($payment_source,$is_cancel);
	            
                $new_status = "<label class='m-0 p-0' style='color:rgb(32,90,224);'>".__($status_name)."</label>";
				
				if(BrandConfiguration::allowRefundedTransactionStatusChange()){
					$new_status = "<label class='m-0 p-0' style='color:rgb(42,174,54);'>".__($status_name)."</label>";
				}
				
            } elseif ($status_id == 6){
                // $new_status = "<label class='m-0 p-0' style='color:rgb(189,195,199);'>".__('Awaiting')."</label>";
                $new_status = "<label class='m-0 p-0' style='color:rgb(189,195,199);'>".__($status_name)."</label>";
            } elseif ($status_id == 7){
                // $new_status = "<label class='m-0 p-0' style='color:rgb(189,195,199);'>".__('Awaiting Refund')."</label>";
                $new_status = "<label class='m-0 p-0' style='color:rgb(189,195,199);'>".__($status_name)."</label>";
            }elseif ($status_id == 8) {
                // $new_status = "<label class='m-0 p-0' style='color:rgb(243,157,18);'>".__('Chargeback Requested')."</label>";
                $new_status = "<label class='m-0 p-0' style='color:rgb(243,157,18);'>".__($status_name)."</label>";
            } elseif ($status_id == 9){
                // $new_status = "<label class='m-0 p-0' style='color:rgb(42,174,54);'>".__('Chargeback Approved')."</label>";
                $new_status = "<label class='m-0 p-0' style='color:rgb(42,174,54);'>".__($status_name)."</label>";
            } elseif ($status_id == 10){
                // $new_status = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__('Chargeback Rejected')."</label>";
                $new_status = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__($status_name)."</label>";
            } elseif ($status_id == 11){
                // $new_status = "<label class='m-0 p-0' style='color:rgb(32,90,224);'>".__('Chargebacked')."</label>";
                $new_status = "<label class='m-0 p-0' style='color:rgb(32,90,224);'>".__($status_name)."</label>";
            }elseif ($status_id == 12){
                // $new_status = "<label class='m-0 p-0' style='color:rgb(32,90,224);'>".__('Chargeback Cancelled')."</label>";
                $new_status = "<label class='m-0 p-0' style='color:rgb(32,90,224);'>".__($status_name)."</label>";
            }elseif ($status_id == 13){
                // $new_status = "<label class='m-0 p-0' style='color:rgb(32,90,224);'>".__('Partial Refund')."</label>";
                $new_status = "<label class='m-0 p-0' style='color:rgb(32,90,224);'>".__($status_name)."</label>";
            }elseif ($status_id == 14){
                // $new_status = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__('Failed')."</label>";
                $new_status = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__($status_name)."</label>";
            }elseif ($status_id == 15){
                // $new_status = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__('Cancelled')."</label>";
                $new_status = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__($status_name)."</label>";
            }elseif ($status_id == 16){
                // $new_status = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__('PreAuth  Pending')."</label>";
                $new_status = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__($status_name)."</label>";
            }elseif ($status_id == TransactionState::PARTIAL_CHARGEBACK){
                $new_status = "<label class='m-0 p-0' style='color:rgb(7,175,151);'>".__($status_name)."</label>";
            }else{
                // $new_status = "<label class='m-0 p-0'>".TransactionState::find($status_id)->name ?? __('Unknown')."</label>";
                $new_status = "<label class='m-0 p-0'>".__($status_name)."</label>";
            }
        }

        //dd($new_status);
        return $new_status;
    }

   public static function getSystemSupportedLanguages()
   {

      $systemSupportedLanguage = config('constants.SYSTEM_SUPPORTED_LANGUAGE');

      $allLanguages = [];
      foreach ($systemSupportedLanguage as $language) {
         if ($language == 'tr') {
            $allLanguages['tr'] = 'Turkish';
         } elseif ($language == 'en') {
            $allLanguages['en'] = 'English';
         } elseif ($language == 'lt') {
            $allLanguages['lt'] = 'Lithuanian';
         } else {
            $allLanguages[$language] = $language;
         }
      }

      return $allLanguages;
   }

   public static function isInRange($needle, $start, $end):bool
   {
       return in_array($needle, range($start, $end));
   }


   public static function setLogoutOtherDeviceWarning($previous_session){

      if(BrandConfiguration::isLogoutOtherDeviceWarning() && !empty($previous_session)){
         GlobalFunction::setBrandCache($previous_session, 1, 60,'logoutotherdevicewarning');
      }

   }

   public static function getLogoutOtherDeviceWarning(){

      $logoutOtherDeviceWarning = '';
      if(BrandConfiguration::isLogoutOtherDeviceWarning() && self::hasBrandCache(Session::getId(), 'logoutotherdevicewarning')){

         $logoutOtherDeviceWarning = __('Your session has been terminated for security reasons.');
         self::unsetBrandCache(Session::getId(),'logoutotherdevicewarning');

      }

      return $logoutOtherDeviceWarning;
   }

    /**
     * This function checks working hours
     * it returns boolean
     * @param   Object Date like "2022-04-01 00:00:00"
     * @param   String start time like "09:00"
     * @param   String end time like "17:00"
     * @return  mixed   array of barred foos or FALSE
     */
    public static function checkWorkingHours($date , $start_time = "09:00" , $end_time = "17:00")
    {
        $check_date = false;
        $existing_date =  Carbon::parse($date);
        $check_holiday_date = (new ManageHoliday())->checkHolidaysAndWeekends($date->copy());
        if ($existing_date->isSameDay($check_holiday_date)) {
            $start_date_time = self::getWorkingTime($existing_date->copy(), $start_time);
            $end_date_time =  self::getWorkingTime($existing_date->copy(), $end_time);
            $check = $existing_date->between($start_date_time, $end_date_time);
            if ($check) {
                $check_date = true;
            }
        }
        return $check_date;
    }

    public static function getWorkingTime($date, $time = "09:00"){

        $time  = explode(":",$time);
        if (is_array($time) && count($time) > 1){
            return $date->startOfDay()->addHours($time[0])->addMinutes($time[1]);
        }
        return $date;

    }

    private function chooseForeignCardCotCommissionsWithPosRiskyCountries($pos_id, $card_country_code, $posRiskyCountriesCollection, $default_cot_percentage, $default_cot_fixed): array
    {
        if (BrandConfiguration::allowDifferentCOTForRiskyCountries()) {
            $risky_country_commission = (new POSRiskyCountry())->checkAndGetCommissionForRiskyCountryCard($pos_id, $card_country_code, $posRiskyCountriesCollection);
            if ($risky_country_commission) {
                $default_cot_percentage = $risky_country_commission->foreign_risky_country_cc_cot_percentage;
                $default_cot_fixed = $risky_country_commission->foreign_risky_country_cc_cot_fixed;
            }
        }
        return [$default_cot_percentage, $default_cot_fixed];
    }

    public static function dateSort($a, $b) {
        return strtotime($a) - strtotime($b);
    }

    public static function getAllCustomQueueList(){
        $list = [];
        //This checking is important, because in admin side, this method may not exist.
        if (method_exists(TmpSaleAutomation::class, 'getCronJobList')){
            $list = array_merge(TmpSaleAutomation::getCronJobList(), $list);
        }
//        if (method_exists(ImportedTransaction::class, 'getCronJobList')){
//            $list = array_merge(ImportedTransaction::getCronJobList(), $list);
//        }
        // for outgoing curl request cronjob
        if (method_exists(OutgoingCurlRequestRecords::class, 'getCronJobList')){
            $list = array_merge(OutgoingCurlRequestRecords::getCronJobList(), $list);
        }

        // for outgoing sms and email (express priority) cronjob
        if (method_exists(OutGoingEmail::class, 'getCronJobList')){
            $list = array_merge(OutGoingEmail::getCronJobList(), $list);
        }

        if(BrandConfiguration::isBrandForSaleAsynchCustomQueue()){
            $list = array_merge([CommandManagement::CUSTOM_QUEUE_SALE_ASYNCHRONOUS], $list);
        }


        return $list;
    }

    public static function isLocalEnvironment(){

        return Ip::isLocal();

        //$host = (new class { use HttpServiceInfoTrait; })->getClientIpAddress();
        //
        //if ($host == '::1' || $host == '127.0.0.1' || $host == 'localhost' || $host == 'UNKNOWN') {
        //
        //    return true;
        //
        //}
        //
        //return false;
    }


   public function isJson($string)
   {
      return Json::validate($string);
   }

   public static function validateEmails($input) {
      $is_valid = true;
      $email = '';
      $input = str_replace(' ', '', $input);

      if(!empty($input) && count($input) > 0) {

         for($i = 0; $i < sizeof($input); $i ++) {

            if(isset($input[$i]) && empty($input[$i])){
                continue;
            }

            if(!filter_var($input[$i], FILTER_VALIDATE_EMAIL)) {
               $is_valid = false;
               break;
            }
         }
         $email = $is_valid ? implode(',', $input) : $email;
      }
      return [$is_valid, $email];
   }

    public static function displayMakerCheckerSectionName ($section_name)
    {
        return self::nameCaseConversion(str_replace('_', ' ', $section_name));
    }

    public static function checkMakerCheckerPermission ($section, $action)
    {
        return (new User())->hasPermissionOnAction(AdminMakerChecker::ACTION_PREFIX . "." . $section . "_" . $action)
	        || Helper::isLocalServerEnvironment();
    }

    public static function objectTypeStdClassArray($object){
        return json_decode(json_encode($object), true);
    }

    public function getSalePF($saleObj,$bankObj,$posObj)
    {
        $is_3d = GlobalFunction::is_payment_3d($saleObj->payment_source);

        return $this->managePFRecords(null,$bankObj,$posObj,'',$is_3d, $saleObj->order_id,null,false,true, $saleObj);

    }

    public static function convertToFloat($amount)
    {
        if (BrandConfiguration::convertToFloatForReport()) {
            return floatval($amount);
        } else {
            return $amount;
        }
    }

    public static function addOtherReason ($reasonlist, $reasoncode)
    {
        if (BrandConfiguration::isEnabledOtherReason() && $reasoncode == config('constants.REASON_CODE.SALE_REFUND')) {
            if (empty($reasonlist)) {
                $reasonlist = collect([]);
            }
            $reasonlist->push((object)[
                'id' => Reason::REASON_OTHER_ID,
                'title' => 'Other',
                'title_tr' => 'Diğer',
                'category_id' => 9,
                'category_code' => $reasoncode,
                'status' => 1
            ]);
        }
        return $reasonlist;
    }

    public static function getProcessMoneyTransferValidationMessage($status_code, $extras = []): array
    {
        $status_type = 'danger';
        if ($status_code == 101){
            $status_message = __("Your request to send money has been successfully received. Since the user is not verified, the money will be transferred to the :company wallet when he/she update Kyc information within 24 hours. If no action is taken, the money will be returned to your account after 24 hours.",
                ['company' => config('brand.name')]);
            $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
            $status_type = 'success';
        } else if ($status_code == 100) {
            $status_message = __('Your Send Money has been completed successfully!');
            $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
            $status_type = 'success';
        } else if ($status_code == 7) {
            $status_message = __("Your request to send money has been successfully received. Since the user is not in the system, the money will be transferred to the :company wallet when he/she registers within 72 hours. If no action is taken, the money will be returned to your account after 72 hours.",
                ['company' => config('brand.name')]);
            $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
            $status_type = 'success';
        } else if ($status_code == 1) {
            $status_message = __('You have insufficient funds to send');
        } else if ($status_code == 11) {
            $status_message = __('Receiver wallet not found');
        } else if ($status_code == 12) {
            $status_message = __("You can't send money to yourself");
        } else if ($status_code == 16) {
            $status_message = __("You can't send money because commission fee is greater than send amount");
        } else if ($status_code == 17) {
            $status_message = __("Merchant Commissions are not set yet for wallet payment. please contact support if this error persists !");
        } else if ($status_code == 20) {
            $status_message = __("Commissions are not set yet. please contact support if this error persists !");
        } else if ($status_code == 30) {
            $status_message = __("You send money to bank request created successfully");
        } else if ($status_code == 22) {
            $status_message = __("Receiver can't receive that amount beacuse his amount limit will exceed");
        } else if ($status_code == 23) {
            $status_message = __("You can only send TRY for Non :company User.", ['company' => config('brand.name')]);
        } else if ($status_code == 29) {
            $status_message = __("Receiver not found");
        } else if ($status_code == 14) {
            $status_message = __("Merchant not found");
        } else if ($status_code == 32) {
            $status_message = __("C2C Cashout to Bank request created but cashout server got exception");
        } else if ($status_code == 33) {
            $status_message = __("Commissions are not set yet. please contact support if this error persists !");
        }else if ($status_code == 46) {
            $status_message = __("User max balance limit exceed");
        }else if ($status_code == 50) {
            $status_message = __('User max send money limit exceed');
        }else if ($status_code == 52) {
            $status_message = __("User max receive money limit exceed");
        } else if ($status_code == ApiService::API_SERVICE_ACTIVE_AML_DETECTED) {
            $status_message = __(ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_ACTIVE_AML_DETECTED]);
        } else if ($status_code == 8 || $status_code == ApiService::API_SERVICE_TRANSACTION_WILL_BE_CREATED_AFTER_PRELIMINARY_REVIEW) {
            $status_message = __(ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_TRANSACTION_WILL_BE_CREATED_AFTER_PRELIMINARY_REVIEW]);
            $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
            $status_type = 'info';
        } else if ($status_code == ApiService::API_SERVICE_WALLET_NOT_FOUND_FOR_THIS_CURRENCY) {
            $status_message = __(ApiService::API_SERVICE_STATUS_MESSAGE[$status_code]);
        } else if ($status_code == ApiService::API_SERVICE_TRANSACTION_NOT_ALLOWED) {
            $status_message = __("In order to send money, you must load money from your own bank account into your wallet!");
        } elseif ($status_code == ApiService::API_SERVICE_MAX_LIMIT_EXCEED_AFTER_CALCULATION) {
            $status_message = __(ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_MAX_LIMIT_EXCEED_AFTER_CALCULATION]);
        } else {
            $status_message = __('Unknown error');
        }
        return [$status_code, $status_message, $status_type];
    }

    public static function is_collection($param): bool
    {
        return (bool)(($param instanceof \Illuminate\Support\Collection) || ($param instanceof \Illuminate\Database\Eloquent\Collection));
    }

    public static function detectWithdrawalPendingOrNot($withdrawal)
    {
        $is_pending = false;
            if (
                $withdrawal->transaction_state_id == TransactionState::PENDING &&
                $withdrawal->flow_type != Withdrawal::FLOW_TYPE_AUTO &&
                $withdrawal->flow_type != Withdrawal::FLOW_TYPE_ADMIN_AUTO &&
                $withdrawal->flow_type != Withdrawal::FLOW_TYPE_IMPORTED_WITHDRAWAL
            ) {
                $is_pending = true;
            }
        return $is_pending;
    }


    public function getPfFromSalePfRecord($order_id, $bankObj)
    {
        $pfArr = [];
        $salePFRecords = new SalesPFRecords();
        $salePFRecordsObj = $salePFRecords->findByOrderId($order_id);
        if(!empty($salePFRecordsObj) && $salePFRecordsObj->version == SalesPFRecords::VERSION_TWO) {
            if ($bankObj->payment_provider == config('constants.PAYMENT_PROVIDER.YAPI_VE_KREDI')) {
                $merchant_id = $salePFRecordsObj->sub_merchant_id;
                $visa_mrc_pf_id = $salePFRecordsObj->pf_merchant_id;
                $mcc = $salePFRecordsObj->mcc;
                $pfArr = [
                    'mrcPfId' => $visa_mrc_pf_id,
                    'subMrcId' => $merchant_id,
                    'mcc' => $mcc
                ];
            }

            if ($bankObj->payment_provider == config('constants.PAYMENT_PROVIDER.DENIZ_PTT')) {
                $merchant_id = $salePFRecordsObj->pf_merchant_id;
                $name = $salePFRecordsObj->pf_merchant_name;
                $client_identity_number = $salePFRecordsObj->client_identity_number;
                $masked_c = $salePFRecordsObj?->masked_c;
                $pfArr = [
                    'SubMerchantCode' => str_pad($merchant_id, 15, "0", STR_PAD_LEFT),
                    'SubMerchantName' => $name,
                    'IdentityNumber' => $client_identity_number,
                    'masked_c' => $masked_c,
                ];
            }

            if ($bankObj->payment_provider == config('constants.PAYMENT_PROVIDER.KUVEYT_TURK_KATILIM')) {
                $pfArr = [
                    'IdentityTaxNumber' => $salePFRecordsObj->identity_nin,
                    'VposSubMerchantId' => $salePFRecordsObj->sub_merchant_id,
                    'pfSubmerchantId' => $salePFRecordsObj->pf_merchant_id,
                    'Description' => $salePFRecordsObj->pf_merchant_name,
                    'pfSubMerchantIdentityTaxNumber' => $salePFRecordsObj->client_identity_number
                ];
            }

            if ($bankObj->payment_provider == config('constants.PAYMENT_PROVIDER.T_GARANTI'))
            {
                $pfArr = [
                    "SubMerchantID" => $salePFRecordsObj->sub_merchant_id,
                ];
            }

            if (Bank::isSame($bankObj->code, config('constants.BANK_CODE.SIPAY'), Str::len(config('constants.BANK_CODE.SIPAY')))
                && Bank::isNestpayDirectAcquiring($bankObj))
            {
                $pfArr = [
                    "client_id" => $salePFRecordsObj->client_identity_number,
                ];
            }
        }

        return $pfArr;
    }

    public function getAmexCardCommission($merchantCommissionObj, $is_foreign_card){

        if($is_foreign_card){
            $merchant_commission_percentage = $merchantCommissionObj->foreign_amex_card_commission;
            $merchant_commission_fixed = $merchantCommissionObj->foreign_amex_card_commission_fixed;
            $end_user_commission_percentage = $merchantCommissionObj->foreign_amex_card_user_commission;
            $end_user_commission_fixed = $merchantCommissionObj->foreign_amex_card_user_commission_fixed;
        } else{
            $merchant_commission_percentage = $merchantCommissionObj->local_amex_card_commission;
            $merchant_commission_fixed = $merchantCommissionObj->local_amex_card_commission_fixed;
            $end_user_commission_percentage = $merchantCommissionObj->local_amex_card_user_commission;
            $end_user_commission_fixed = $merchantCommissionObj->local_amex_card_user_commission_fixed;
        }

        return [
            $merchant_commission_percentage,
            $merchant_commission_fixed,
            $end_user_commission_percentage,
            $end_user_commission_fixed
        ];
    }

	
	public static function checkEmailNotification($data)
	{
		return BrandConfiguration::call([BackendMix::class, 'allowSentEmailNotification'])
			&& isset($data['is_sent_email_notification'])
			&& isset($data['userObj'])
			&& isset($data['user_type'])
			&& ($data['user_type'] == Profile::ADMIN || $data['user_type'] == Profile::MERCHANT);
	}
	
	public static function sentEmailNotification($data, $body){
		(new EmailNotification())
			->prepareRequestParams($data['userObj'], $body)
			->sendNotification();
	}

    public static function isVoidStatusApplicable($payment_source, $is_cancel=0)
    {
        $is_applicable = 0;
        if($payment_source != CCPayment::PAYMENT_SOURCE_PAVO_PAYMENT) {
            $is_applicable = $is_cancel;
        }
        return $is_applicable;
    }

    public static function refundStatus($payment_source, $is_cancel=0, $new_status = 'Refunded')
    {
//        $is_void_status_applicable = $is_cancel;
//       $is_void_status_applicable = GlobalFunction::isVoidStatusApplicable($payment_source, $is_cancel);
//        $new_status = $new_status;
        if(BrandConfiguration::call([Mix::class, 'isAllowVoidStatus']) && !empty($is_cancel)){
            $new_status = TransactionState::TRANSACTION_VOID_STATUS;
        }
        return $new_status;
    }
    public static function getBillMailAndPhone()
    {
        $phone = config('constants.DEFAULT_BILL_PHONE');
        $email = config('constants.DEFAULT_BILL_EMAIL');
        $isAllowBillMailAndPhone = BackendCcpayment::isAllowSetBillEmailNPhone();

        if ($isAllowBillMailAndPhone && config('brand.name_code') == (config('constants.BRAND_NAME_CODE_LIST.SP') || config('constants.BRAND_NAME_CODE_LIST.PIN'))) {
            $phone = !empty($phone) ? $phone : '902167062266';
            $email = !empty($email) ? $email : 'test@test.com';
        }

        if ($isAllowBillMailAndPhone && config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.PB')) {
            $phone = !empty($phone) ? $phone : '908502418418';
            $email = !empty($email) ? $email : 'test@test.com';
        }

        if ($isAllowBillMailAndPhone && config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.PC')) {
            $phone = !empty($phone) ? $phone : '908502427298';
            $email = !empty($email) ? $email : 'test@test.com';
        }

        if ($isAllowBillMailAndPhone && config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.VP')) {
            $phone = !empty($phone) ? $phone : '908502418297';
            $email = !empty($email) ? $email : 'test@test.com';
        }

        return [$phone, $email];
    }


}