<?php
/**
 * User: md Yeasin
 * Date: 8/21/2019
 * Time: 4:06 PM
 * Licence: MIT
 *
 * description:
 *  this sample date helper for build dynamic date formate
 */

namespace App\Utils;

use App\Http\Controllers\Traits\CommonLogTrait;
use App\Models\CCPayment;
//use App\Models\CCPayment;
use App\Models\Merchant;
use App\Models\PaymentReceiveOption;
use App\Models\PaymentRecOption;
use App\Models\Sale;
use App\Models\TransactionState;
use App\Models\UserUserGroupSetting;
use App\Models\Withdrawal;
use App\User;
use App\Models\Ipv4;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\CardProgram;
use common\integration\GlobalFunction;
use common\integration\ProviderErrorCodeHandler;
use common\integration\Utility\Arr;
use common\integration\Replication\ReplicationRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

use Illuminate\Support\Facades\Config;
use Route;
use common\integration\BrandConfiguration;
use common\integration\ErrorCodeMapping;
use common\integration\GlobalUser;
use common\integration\SaleTransaction;

class CommonFunction
{
    use CommonLogTrait;

    public static function getFormatedAmount($amount, $currency_symbol='', $decimal_val = 2) {
        $currency_symbol = !empty($currency_symbol) ? ' '.$currency_symbol : '';
        return number_format((float)$amount, $decimal_val, '.', '') . $currency_symbol;
    }

    public static function dateFormat($date){

        date_default_timezone_set("Europe/Istanbul");

        $formated_date = "";

        if (!empty($date) || $date!=null) {
            $formated_date =  date("Y-m-d H:i:s", strtotime($date));
//            $formated_date = date('d.m.Y-h:m',strtotime($date));
        }
        return $formated_date;
    }

    public static function status($status_id, $flow_type='', $sale_type=Sale::Auth , $language = null, $extra=[], $payment_source = -1, $is_cancel =0){
        return GlobalFunction::status($status_id, $flow_type, $sale_type, $language, $extra, $payment_source, $is_cancel);
//        if(!empty($language)){
//            app()->setLocale($language);
//        }
//        $new_status = "";
//        if (!is_null($status_id)) {
//            if ($status_id == 1) {
//                $new_status = "<label class='m-0 p-0' style='color:rgb(42,174,54);'>".__('Completed')."</label>";
//            } elseif ($status_id == 2){
//                $new_status = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__('Rejected')."</label>";
//            } elseif ($status_id == 3){
//                if (!empty($flow_type)
//                    && ($flow_type == Withdrawal::FLOW_TYPE_AUTO || $flow_type == Withdrawal::FLOW_TYPE_ADMIN_AUTO)) {
//                    $label = __('Sent to Finflow');
//                } elseif (!empty($sale_type) && $sale_type == Sale::PREAUTH){
//                    $label = __(config('constants.PRE_AUTHORIZATION'));
//                }else {
//                    $label = __('Pending');
//                }
//                $new_status = "<label class='m-0 p-0' style='color:rgb(243,157,18);'>".$label."</label>";
//            } elseif ($status_id == 4){
//                $new_status = "<label class='m-0 p-0' style='color:rgb(44,196,203);'>".__('Stand By')."</label>";
//            } elseif ($status_id == 5){
//                $new_status = "<label class='m-0 p-0' style='color:rgb(32,90,224);'>".__('Refunded')."</label>";
//            } elseif ($status_id == 6){
//                $new_status = "<label class='m-0 p-0' style='color:rgb(189,195,199);'>".__('Awaiting')."</label>";
//            } elseif ($status_id == 7){
//                $new_status = "<label class='m-0 p-0' style='color:rgb(189,195,199);'>".__('Awaiting Refund')."</label>";
//            }elseif ($status_id == 8) {
//                $new_status = "<label class='m-0 p-0' style='color:rgb(243,157,18);'>".__('Chargeback Requested')."</label>";
//            } elseif ($status_id == 9){
//                $new_status = "<label class='m-0 p-0' style='color:rgb(42,174,54);'>".__('Chargeback Approved')."</label>";
//            } elseif ($status_id == 10){
//                $new_status = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__('Chargeback Rejected')."</label>";
//            } elseif ($status_id == 11){
//                $new_status = "<label class='m-0 p-0' style='color:rgb(32,90,224);'>".__('Chargebacked')."</label>";
//            }elseif ($status_id == 12){
//                $new_status = "<label class='m-0 p-0' style='color:rgb(32,90,224);'>".__('Chargeback Cancelled')."</label>";
//            }elseif ($status_id == 13){
//                $new_status = "<label class='m-0 p-0' style='color:rgb(32,90,224);'>".__('Partial Refund')."</label>";
//            }elseif ($status_id == 14){
//                $new_status = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__('Failed')."</label>";
//            }elseif ($status_id == 15){
//                $new_status = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__('Cancelled')."</label>";
//            }elseif ($status_id == 16){
//                $new_status = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__('PreAuth  Pending')."</label>";
//            }else{
//                $new_status = "<label class='m-0 p-0'>".TransactionState::find($status_id)->name ?? __('Unknown')."</label>";
//            }
//        }
//
//        //dd($new_status);
//        return $new_status;
    }


    public static function maskedName($masked_name, $card_holder=null){

        mb_internal_encoding('UTF-8');

        $masked_name = !empty($masked_name) ? GlobalFunction::nameCaseConversion($masked_name) : '';
        if (empty($card_holder)){
            return $masked_name;
        }
        $name_masking = '';
        if (!empty($masked_name)) {

            $name_masking_array = explode(" ", $masked_name);
            foreach ($name_masking_array as $value) {
                if(!empty($value)){
                    $str = substr($value,0,2);
                    for ($i = 2; $i < strlen($value); $i++) {
                        $str = $str . "*";
                    }
                    $name_masking .= $str . " ";
                }

            }
        }

        return $name_masking;

    }


    public static function ibanMasking($iban_number){
        return $iban_number;

        if (!is_null($iban_number)) {
            $user = new User();
            if($user->isGlobalAdmin(Auth::id()) || $user->isFinanceDeptUser(Auth::id())) {
                return $iban_number;
            } else {
                if (strlen($iban_number) > 5) {
                    $masked_iban = substr($iban_number, 0, 3);
                    for ($i = 3; $i < strlen($iban_number) - 2; $i++) {
                        $masked_iban .= "*";
                    }
                    $masked_iban .= substr($iban_number, -2);
                    return $masked_iban;
                } else {
                    return $iban_number;
                }
            }
        }
    }

    public static function creditCardNoMasking($credit_card_no, $type = null, $masking_length = ''){

        return GlobalFunction::creditCardNumberMasking($credit_card_no, $type, $masking_length);
    }

    public static function phoneNumberMasking($phone_number){

        $first_letters = substr($phone_number, 0,3);
        $last_letters = substr($phone_number, -4);

        $masked_phone = $first_letters."******".$last_letters;

        return $masked_phone;
    }

    public static function getFormatedUserName($name, $user_category){
        mb_internal_encoding('UTF-8');
        $user_name = mb_convert_case($name, MB_CASE_TITLE);

        return $user_name;
    }

    public static function showDashForBlankField($data){
        $data = empty($data) ? "-":$data;

        return $data;
    }

    public static function amountToBeRefunded($request_amount, $gross, $product_price, $total_refunded_amount){

        if ($product_price == ($total_refunded_amount + $request_amount)){
            $amountToBeRefunded = $gross - $total_refunded_amount;
        }else{
            $amountToBeRefunded = $request_amount;
        }

        return $amountToBeRefunded;
    }

    public static function getTotalRefundedAmount($total_refunded_amount, $product_price, $gross){
        $res = $total_refunded_amount;
        if ($total_refunded_amount == $product_price){
            $res = $gross;
        }
        return $res;
    }

    public static function hasAdminPermission(){

        $result = false;

        $groupIds = [2];

        $userPermissionList = session()->get('user_permission');

        if (!empty($userPermissionList)){

            foreach ($userPermissionList as $permission){

                if (in_array($permission->usergroup_id, $groupIds)){
                    $result =  true;
                    break;
                }

            }

        }

        return $result;

    }

    public static function hasUserRoutePermission($routeNam){
        $routeArray = Route::getRoutes()->getByName($routeNam);
        if (!empty($routeArray)){
            $routeArray =  $routeArray->action;
            $controllerAction = class_basename($routeArray['controller']);

            list($controller, $action) = explode('@', $controllerAction);

            $userPermissionList = session()->get('user_permission');
            $userPermissionList = collect($userPermissionList);
            $userPermissionObj = $userPermissionList->where('controller_name', $controller)
                ->where('method_name', $action)
                ->where('user_id', \auth()->id())
                ->first();
            if (!empty($userPermissionObj)){
                return true;
            }
        }

        return false;

    }

    public function decryptData($data)
    {
        return $this->customEncryptionDecryption($data, config('app.brand_secret_key'),'decrypt');
    }

    public function encryptData($data)
    {
        return $this->customEncryptionDecryption($data, config('app.brand_secret_key'),'encrypt');
    }

    public function setIsoError($data)
    {
        $check = strpos($data,"#");
        if ($check !== false) {
            $code = explode("#", $data);
            $key = trim($code[1]);
            $check2 = strpos($key,"SO");
            $check3 = strpos($key,"ORE");

            if ($this->error_codes(trim($key)) == "Unknown Error" && count($code) == 5) {
                if($check2!=false || $check3!=false){
                    return $this->setErrorMessage($code);
                }else{
                    return $this->setErrorMessage($code,true);
                }
            }elseif($this->error_codes(trim($key)) != "Unknown Error" && $key == "ISO8583-SF" && count($code) == 5){

                if($this->setErrorMessage($code) == $key){
                    return $this->error_codes($key);
                }else{
                    return $this->setErrorMessage($code);
                }

            }elseif($this->error_codes(trim($key)) != "Unknown Error" && $key == "ISO8583-99" && count($code) == 5){

                if($this->setErrorMessage($code) == $key){
                    return $this->error_codes($key);
                }else{
                    return $this->setErrorMessage($code);
                }
            }else{

                if($check2 !== false || $check3 !== false){
                    return $this->error_codes($key);
                }else{
                    return $key;
                }

            }

        } else {
            return $data;
        }
    }

    public function setErrorMessage($code,$full=0)
    {
        if (!empty($code[2]) || !empty($code[3]) || !empty($code[4])) {
            $msg = "";
            if($full && !empty($code[1])){
                $msg .= "#";
                $msg .= $code[1];
            }

            if (!empty($code[2])) {
                $msg .= "#";
                $msg .= $code[2];
            }

            if (!empty($code[3])) {
                $msg .= "#";
                $msg .= $code[3];
            }

            if (!empty($code[4])) {
                $msg .= "#";
                $msg .= $code[4];
            }

            return $msg;
        } else {
            return $code[1];
        }
    }

    public function checkUserGroupSettingRecord($user_id)
    {
        $result = UserUserGroupSetting::query()->where('user_id',$user_id)->first();
        if(!empty($result) && $result->is_allow_direct_export == 1){
            return true;
        }
        return false;
    }


    public function checkUserBtoBConfirmStatus($user_id)
    {
        $result = UserUserGroupSetting::query()->where('user_id',$user_id)->first();
        if(!empty($result) && $result->is_allow_b2b_without_doc == 1){
            return true;
        }
        return false;
    }

    public function totalRecords($object)
    {
        $total = 0;
        if(Arr::isOfType($object)){
            $total = count($object);
        }elseif(method_exists($object, 'total')){
            $total = $object->total();
        }

        echo __('Total :number Records Found', ['number' => $total]);
    }

    public static function showDotsAfterLength($val, $len){

        $d_len = strlen($val);
        $d_val = ($d_len <= $len) ? $val : substr($val, 0, $len) . '...';

        return $d_val;
    }

    public static function getNameFromPath($path=null)
    {
        return basename($path);
    }

    public function formatSaleData($saleTransaction, $is_query_enable = true , $from_backup = null)
    {
        $should_allow_partial_chargeback = BrandConfiguration::call([Mix::class, 'shouldAllowPartialChargeback']);
        $integratorCommissionAmount = 0;
        if (isset($saleTransaction->saleIntegrator->commission_amount)){
            $integratorCommissionAmount = $saleTransaction->saleIntegrator->commission_amount;
        }

        $saleTransaction->sale_integrator_commission = CommonFunction::getFormatedAmount($integratorCommissionAmount, $saleTransaction->currency_symbol);

        $extra['is_loyalty_point_sale'] = !empty($saleTransaction->is_loyalty_point_sale);
        $transaction_state = $saleTransaction->transaction_state_id;
        if ($should_allow_partial_chargeback) {
            if((Arr::isAMemberOf($saleTransaction->transaction_state_id, [TransactionState::CHARGE_BACK_REQUESTED, TransactionState::CHARGE_BACK_REJECTED]))
                && !empty($saleTransaction?->refund_history_transaction_state_id)
                && $saleTransaction->refund_history_transaction_state_id == TransactionState::COMPLETED) {
                $transaction_state = TransactionState::PARTIAL_CHARGEBACK;
            }
        }

        global $global_status, $masked_name, $amount;
        $global_status = CommonFunction::status($transaction_state, '', $saleTransaction->sale_type,'',$extra, $saleTransaction->payment_source, $saleTransaction->is_cancel??0);
        $user_name = GlobalFunction::nameCaseConversion($saleTransaction->user_name);
        $merchant_name = GlobalFunction::nameCaseConversion($saleTransaction->merchant_name);
        $amount = CommonFunction::getFormatedAmount($saleTransaction->gross,
            $saleTransaction->currency_symbol);
        $credit_card = '';

        $commonFunction = new CommonFunction();
        if (!empty($saleTransaction->credit_card_no)) {
            $credit_card = $this->decryptData($saleTransaction->credit_card_no);
        }
        $credit_card = CommonFunction::creditCardNoMasking($credit_card);

        $debit_credit_card = '-';
        if ($saleTransaction->payment_type_id == PaymentReceiveOption::CREDIT_CARD) {
            if (!empty($saleTransaction['saleProperty']['card_type'])) {
                if ($saleTransaction['saleProperty']['card_type'] == 1) {
                    $debit_credit_card = 'Credit Card';
                } elseif ($saleTransaction['saleProperty']['card_type'] == 2) {
                    $debit_credit_card = 'Debit Card';
                } else {
                    $debit_credit_card = 'UnKnown';
                }
            }
        }else {
            $debit_credit_card = '-';
        }

        if (SaleTransaction::isSaleFromFastpayWalletAmount($saleTransaction->payment_source)) {
            //blank the card option when sale is initiated from fastpay wallet amount instead of a card
            $debit_credit_card = '-';
        }

        $bank_installment = $saleTransaction['saleProperty']['bank_installment_number'] ?? 0;
        if ($bank_installment > 0) {
            $input_installment = $saleTransaction->installment;

            $saleTransaction->installment  = SaleTransaction::getPlusInstallment($saleTransaction->sale_property_plus_installment, $input_installment, $bank_installment);

        }

        $method = GlobalUser::getPaymentMethod($saleTransaction);

        if ($saleTransaction->transaction_state_id == TransactionState::COMPLETED
            || $saleTransaction->transaction_state_id == TransactionState::PARTIAL_REFUND
            || $saleTransaction->transaction_state_id == TransactionState::CHARGE_CANCELED) {
            $stateId = $saleTransaction->transaction_state_id;
        } else {
            $stateId = 0;
        }
        $total_commission = $saleTransaction->gross - $saleTransaction->net;
        $revenue = $total_commission - $saleTransaction->cost - $integratorCommissionAmount;

        $revenue = $revenue + $saleTransaction->refunded_chargeback_fee;
        $countryName = "";
        if(!empty($saleTransaction->ip) && $is_query_enable === true){
            $ip4 = new Ipv4();
            $countryName = $ip4->findCountryBYIP($saleTransaction->ip);
        }
        $saleTransaction->total_commission = CommonFunction::getFormatedAmount($total_commission, $saleTransaction->currency_symbol);

        $saleTransaction->pay_by_token_fee = CommonFunction::getFormatedAmount($saleTransaction->pay_by_token_fee, $saleTransaction->currency_symbol);
        $base_url = url(config('constants.defines.ADMIN_URL_SLUG') . '/alltransaction/download') . '/' . $saleTransaction->id;
        if (Auth::user()->hasPermissionOnAction(Config::get('constants.defines.APP_PAYMENT_TRANSACTION_DOWNLOAD')))
        {
            $base_url = url(config('constants.defines.ADMIN_URL_SLUG') . '/payment-transactions/download') . '/' . $saleTransaction->id;
        }
        $http_build_query_for_refund = ['type' => 'refund_receipt'];
        $http_build_query_for_transaction = [];
        if (!empty($from_backup) && $from_backup == ReplicationRepository::TABLE_TYPE_SUCCESS_BACKUP) {
            $backup_table_type = ["backup_table_type" => $from_backup];
            $http_build_query_for_refund = Arr::merge($http_build_query_for_refund, $backup_table_type);
            $http_build_query_for_transaction = Arr::merge($http_build_query_for_transaction, $backup_table_type);
        }
        $saleTransaction->downloadURL = $base_url . '?' . http_build_query($http_build_query_for_transaction);
        $saleTransaction->refundReceiptUrl = $base_url . '?' . http_build_query($http_build_query_for_refund);

        $saleTransaction->wallet_refund = (isset($saleTransaction->gsm_number) && !empty(isset($saleTransaction->gsm_number))) ? true : false;
        $saleTransaction->customer_masked = $saleTransaction->user_name ? GlobalFunction::nameCaseConversion($saleTransaction->user_name) : '-';
        $saleTransaction->merchant_masked = $saleTransaction->merchant_name ? GlobalFunction::nameCaseConversion($saleTransaction->merchant_name) : '-';
        $saleTransaction->gsm_number = $saleTransaction->gsm_number ? $saleTransaction->gsm_number : '-';
        $saleTransaction->operator = $saleTransaction->operator ? $saleTransaction->operator : '-';

        if($saleTransaction->payment_source == CCPayment::PAYMENT_SOURCE_PAVO_PAYMENT && $saleTransaction->transaction_state_id == TransactionState::FAILED){
            $saleTransaction->result = !empty($saleTransaction->sale_property_error_code) ? $saleTransaction->sale_property_error_code . ' ' .__((new ErrorCodeMapping($saleTransaction->sale_property_error_code, config('constants.PAYMENT_PROVIDER.PAVO')))->getMessage()) : '-';
        }else{
            list($saleTransaction->original_bank_error_code, $saleTransaction->original_bank_error_description) = (new ProviderErrorCodeHandler)->formatResultForOriginalBankResponseEntities($saleTransaction->result);
            $saleTransaction->result = $saleTransaction->result ? __($commonFunction->setIsoError($saleTransaction->result)) : '-';
        }

        $saleTransaction->ip = $saleTransaction->ip ? $saleTransaction->ip : '-';
        $saleTransaction->card_program = !empty($saleTransaction->card_program) ? $saleTransaction->card_program : '-';
        $saleTransaction->card_network = $this->getCreditCardType(str_replace('*', '', $commonFunction->decryptData($saleTransaction->credit_card_no)));
        $saleTransaction->card_issuer_name = $saleTransaction->card_issuer_name ? $saleTransaction->card_issuer_name : '-';
        $saleTransaction->country_name = $countryName['country_name'] ?? '-';
        $saleTransaction->order_id = $saleTransaction->order_id ? $saleTransaction->order_id : '-';
        $saleTransaction->card_holder_bank = $saleTransaction->card_holder_bank ? $saleTransaction->card_holder_bank : '-';
        $saleTransaction->stateID = $stateId;
        $saleTransaction->debit_credit_card = !empty($debit_credit_card) ? $debit_credit_card : '-';
        $saleTransaction->created_at2 = Date::format(6, $saleTransaction->created_at);
        $saleTransaction->updated_at2 = Date::format(6, !empty($should_allow_partial_chargeback) ? $saleTransaction->refund_updated_at : $saleTransaction->updated_at);
        $saleTransaction->settlement_date_bank2 = Date::format(6, $saleTransaction->settlement_date_bank);
        $saleTransaction->settlement_date_merchant2 = Date::format(6, $saleTransaction->settlement_date_merchant);
        $saleTransaction->state_label = $global_status;
        $saleTransaction->actual_product_price = $saleTransaction->product_price;

        $saleTransaction->pre_auth_transaction_amount = isset($saleTransaction['saleProperty']['pre_auth_amount']) ? $saleTransaction['saleProperty']['pre_auth_amount'] : 0 ;
        $saleTransaction->merchant_server_id = isset($saleTransaction['saleProperty']['merchant_server_id']) ? $saleTransaction['saleProperty']['merchant_server_id'] : '' ;
        $saleTransaction->referer_url = isset($saleTransaction['saleProperty']['referer_url']) ? $saleTransaction['saleProperty']['referer_url'] : '' ;

        $saleTransaction->product_price = CommonFunction::getFormatedAmount($saleTransaction->product_price, $saleTransaction->currency_symbol);

        $saleTransaction->net_share = CommonFunction::getFormatedAmount(($saleTransaction->net - $saleTransaction->rolling_amount), $saleTransaction->currency_symbol);
        $saleTransaction->actual_gross = $saleTransaction->gross;
        $saleTransaction->gross = CommonFunction::getFormatedAmount(!empty($should_allow_partial_chargeback) ? $saleTransaction->refund_history_amount_to_bank : $saleTransaction->gross, $saleTransaction->currency_symbol);
        $saleTransaction->net = CommonFunction::getFormatedAmount($saleTransaction->net, $saleTransaction->currency_symbol);
        $saleTransaction->actual_user_commission = $saleTransaction->user_commission;
        $saleTransaction->user_commission = CommonFunction::getFormatedAmount($saleTransaction->user_commission, $saleTransaction->currency_symbol);
        $saleTransaction->merchant_commission = CommonFunction::getFormatedAmount($saleTransaction->merchant_commission, $saleTransaction->currency_symbol);
        $saleTransaction->rev = CommonFunction::getFormatedAmount(($revenue), $saleTransaction->currency_symbol);
        $saleTransaction->fee = CommonFunction::getFormatedAmount($saleTransaction->fee, $saleTransaction->currency_symbol);

        $saleTransaction->actual_cost = $saleTransaction->cost;
        $saleTransaction->cost = CommonFunction::getFormatedAmount($saleTransaction->cost, $saleTransaction->currency_symbol);
        $saleTransaction->rolling_amount = CommonFunction::getFormatedAmount($saleTransaction->rolling_amount, $saleTransaction->currency_symbol);
        $saleTransaction->rolling_settlement = Date::format(6, $saleTransaction->rolling_balance['effective_date'] ?? '');
        if(empty($saleTransaction->rolling_settlement)){
            $saleTransaction->rolling_settlement = '0000-00-00';
        }
        $saleTransaction->method = (!empty($method)) ? $method : '-';
        $saleTransaction->pos = $saleTransaction->pos_name . " (" . $saleTransaction->pos_id . ")";
        $saleTransaction->pay_rec_opt = PaymentReceiveOption::CREDIT_CARD;
        $saleTransaction->credit_card = $credit_card;
        $saleTransaction->formated_total_refunded_amount = CommonFunction::getFormatedAmount($saleTransaction->total_refunded_amount, $saleTransaction->currency_symbol);
        $saleTransaction->refunded_chargeback_fee = CommonFunction::getFormatedAmount($saleTransaction->refunded_chargeback_fee, $saleTransaction->currency_symbol);

        $card_holder_name = '';
        $commonFunction = new CommonFunction();
        if (isset($saleTransaction->saleBilling['card_holder_name']) && !empty($saleTransaction->saleBilling['card_holder_name'])) {
            $card_holder_name = $commonFunction->decryptData($saleTransaction->saleBilling['card_holder_name']);
        }
        $extra_card_holder_name = '';
        if (isset($saleTransaction->saleBilling['extra_card_holder_name']) && !empty($saleTransaction->saleBilling['extra_card_holder_name'])) {
            $extra_card_holder_name = $commonFunction->decryptData($saleTransaction->saleBilling['extra_card_holder_name']);
        }

        $saleTransaction->card_holder_name = GlobalFunction::nameCaseConversion($card_holder_name);
        $saleTransaction->extra_card_holder_name = GlobalFunction::nameCaseConversion($extra_card_holder_name);
        if ($saleTransaction->payment_type_id == PaymentReceiveOption::CREDIT_CARD &&
            ($saleTransaction->payment_source == 11 || $saleTransaction->payment_source == 12)){
            if ($saleTransaction->recurring_id > 0){
                $saleTransaction->method = 'MP '.$saleTransaction->method . ' Recurring';
            }else{
                $saleTransaction->method = 'MP '.$saleTransaction->method;
            }

        }elseif ($saleTransaction->payment_type_id == PaymentReceiveOption::CREDIT_CARD && $saleTransaction->recurring_id > 0){
            $saleTransaction->method = $saleTransaction->method . ' Recurring';
        }else{
            if ($saleTransaction->dpl_id > 0) {
                $saleTransaction->method = "DPL " . $saleTransaction->method;
            } elseif ($saleTransaction->payment_type_id == 2) {
                $saleTransaction->method = "Mobile POS";
            }
        }

        if($saleTransaction->card_program == CardProgram::PROGRAM_CODE_TARIM){
            $saleTransaction->maturity_period = $saleTransaction->maturity_period;
        }else{
            $saleTransaction->maturity_period = '';
        }

        $json_data = json_decode($saleTransaction['json_data']);
        // $description = "-";
        // if (isset($json_data->items[0]->description)) {
        //     $description = $json_data->items[0]->description;
        // }

        // if(isset($json_data->items) && !empty($json_data->items)){

        //     $first_item = reset($json_data->items);

        //     if(isset($first_item->description) && !empty($first_item->description)){
        //         $description = $first_item->description;
        //     }

        // }


        $saleTransaction->description = SaleTransaction::getInvoiceDescription($json_data); //$description;

        $saleTransaction->bill_name = isset($saleTransaction->saleBilling['bill_name']) ? $saleTransaction->saleBilling['bill_name'] : '';
        $saleTransaction->bill_surname = isset($saleTransaction->saleBilling['bill_surname']) ? $saleTransaction->saleBilling['bill_surname'] : '';
        $saleTransaction->bill_address = isset($saleTransaction->saleBilling['bill_address1']) ? $saleTransaction->saleBilling['bill_address1'] : '';
        $saleTransaction->bill_email = isset($saleTransaction->saleBilling['bill_email']) ? $saleTransaction->saleBilling['bill_email'] : '';
        $saleTransaction->bill_phone = isset($saleTransaction->saleBilling['bill_phone']) ? $saleTransaction->saleBilling['bill_phone'] : '';
        $saleTransaction->bill_city = isset($saleTransaction->saleBilling['bill_city']) ? $saleTransaction->saleBilling['bill_city'] : '';
        $saleTransaction->bill_state = isset($saleTransaction->saleBilling['bill_state']) ? $saleTransaction->saleBilling['bill_state'] : '';
        $saleTransaction->bill_country = isset($saleTransaction->saleBilling['bill_country']) ? $saleTransaction->saleBilling['bill_country'] : '';
        $saleTransaction->bill_postcode = isset($saleTransaction->saleBilling['bill_postcode']) ? $saleTransaction->saleBilling['bill_postcode'] : '';
        $saleTransaction->sale_currency_conversion_from_currency = CommonFunction::getFormatedAmount($saleTransaction->sale_currency_conversion_original_amount, $saleTransaction->sale_currency_conversion_from_currency);
        $saleTransaction->sale_currency_conversion_to_currency = CommonFunction::getFormatedAmount($saleTransaction->sale_currency_conversion_converted_amount, $saleTransaction->sale_currency_conversion_to_currency);
        $saleTransaction->sale_currency_conversion_conversion_rate = $saleTransaction->sale_currency_conversion_conversion_rate ?? 0;
        $saleTransaction->sale_currency_conversion_currency_converted_created_at = Date::format(6, $saleTransaction->sale_currency_conversion_created_at);
        $extra_card_holder_name = trim(str_replace(' *','',$extra_card_holder_name));
        $status = GlobalFunction::checkRealCardHolderName($card_holder_name,$extra_card_holder_name);
        $saleTransaction->real_card_holder_name = (!empty($status)) ? $extra_card_holder_name.' ('.$status.')': '';

        if ($saleTransaction->remote_transaction_datetime != null) {
            $saleTransaction->remote_transaction_datetime2 = Date::format(6, $saleTransaction->remote_transaction_datetime);
        } else {
            $saleTransaction->remote_transaction_datetime2 = '';
        }

        $saleTransaction->remote_sale_reference_id = $saleTransaction->remote_sale_reference_id ?: '';
        $saleTransaction->remote_product_price = $saleTransaction->remote_sale_reference_id ?: '';
        $saleTransaction->remote_acquirer_reference = $saleTransaction->remote_acquirer_reference ?: '';
        $saleTransaction->remote_operation_name = $saleTransaction->remote_operation_name ?: '';
        if(BrandConfiguration::call([Mix::class, 'isAllowToShowBankTerminalId']) && !SaleTransaction::isPhysicalPosTransaction($saleTransaction->payment_source)) {
            $saleTransaction->merchant_terminal_id = $saleTransaction->bank_terminal_id ?: '';
        }else {
            $saleTransaction->merchant_terminal_id = $saleTransaction->merchant_terminal_id ?: '';
        }

        $saleTransaction->merchant_commission_percentage = isset($saleTransaction->merchantSale['merchant_commission_percentage']) ? $saleTransaction->merchantSale['merchant_commission_percentage'] : '';
        //$saleTransaction->merchant_commission_fixed = isset($saleTransaction->merchantSale['merchant_commission_fixed']) ? $saleTransaction->merchantSale['merchant_commission_fixed'] : '';
        $saleTransaction->cot_percentage = isset($saleTransaction->merchantSale['cot_percentage']) ? $saleTransaction->merchantSale['cot_percentage'] : '';
        //$saleTransaction->cot_fixed = isset($saleTransaction->merchantSale['cot_fixed']) ? $saleTransaction->merchantSale['cot_fixed'] : '';

        $saleTransaction->check_refund_button = false;
        if (BrandConfiguration::conditionallyRefundButtonShow()) {
            $saleTransaction->check_refund_button = true;
        }

        if(SaleTransaction::isPhysicalPosTransaction($saleTransaction->payment_source)) {
            $saleTransaction->transaction_pos_type = \common\integration\Models\Sale::getTransactionPosType()[\common\integration\Models\Sale::PHYSICAL_TRANSACTION_POS_TYPE];
        } else {
            $saleTransaction->transaction_pos_type = \common\integration\Models\Sale::getTransactionPosType()[\common\integration\Models\Sale::VIRTUAL_TRANSACTION_POS_TYPE];
        }

        $saleTransaction->remote_payment_method = $saleTransaction->remote_payment_method ?? '';
        $saleTransaction->remote_internal_merchant_order_id = $saleTransaction->remote_internal_merchant_order_id ?? '';

        if (isset($saleTransaction->payment_source)) {
            $saleTransaction->security_type = SaleTransaction::getSecurityType($saleTransaction->payment_source);

        }
        $saleTransaction->is_disable_refund = (new SaleTransaction())->isDisableImportedRefundTransaction($saleTransaction->payment_source, BrandConfiguration::PANEL_ADMIN, false);

        return $saleTransaction;
    }


    public function getTokenForAdminUse (User $user, Merchant $merchant) {
        $loginData = json_encode(array(
            "email" => $merchant->user->email,
            "password" => "Nop@ss1234",
            "requested_admin_id" => $this->encryptData($user->id)
        ));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, config('app.app_merchant_url').'/api/corporatelogin');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $http_response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_errno($ch) ? curl_error($ch) : '';
        curl_close($ch);

        if($status == 200) {
            $result = json_decode($http_response);
            $code = $result->code;
            $token = $result->token;
        } else {
            $code = 0;
            $token = '';
        }

        return [$status, $error, $code, $token];
    }

    public static function recurringStatus($status_id ){

        return $status_id ==1 ? "<label class='m-0 p-0 text-success'>" . __('Active') . "</label>" :"<label class='m-0 p-0' style='color:rgb(236,95,104);'>" . __('Inactive') . "</label>";;

    }
    public static function frequencyCycle($payment_cycle ){

        switch ($payment_cycle) {
            case 'D':
                return __('Daily');
                break;
            case 'Y':
                return __('Yearly');
                break;
            case 'M':
                return __('Monthly');
                break;
            case 'W':
                return __('Weekly');
                break;
            default:
                # code...
                break;
        }

    }

    public function maskSecretAnswer($answer) {
        $result = '';

        if(!empty($answer)) {
            $decoded = $this->decryptData($answer);
            $strlen = strlen($decoded);

            if($strlen > 2) {
                for($i=0; $i<$strlen-2; $i++) {
                    $result .= '*';
                }
                $result = $result . $decoded[$strlen-2] . $decoded[$strlen-1];
            } else {
                $result = '**';
            }
        }

        return $result;
    }

    public static function phoneNoMasking($phone_no){
        $masked_phone = $phone_no ? "****" . substr($phone_no, -4) : $phone_no;
        return $masked_phone;
    }

    public static function getFormattedIban($value){
        return str_replace(" ", "", $value);
    }

    public static function getCategoryLabel ($category_id) {


        return GlobalFunction::getCustomerCategory($category_id);

        $category_label = "-";
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



        // if (isset($category_id) && !empty($category_id)) {
        //     if ($category_id == 1) {
        //         $category_label = "<label class='m-0 p-0' style='color:rgb(189,195,199);'>".__('Unknown')."</label>";
        //     } elseif ($category_id == 2){
        //         $category_label = "<label class='m-0 p-0' style='color:rgb(236,95,104);'>".__('Unverified')."</label>";
        //     } elseif ($category_id == 3){
        //         $category_label = "<label class='m-0 p-0' style='color:rgb(42,174,54);'>".__('Verified')."</label>";
        //     } elseif ($category_id == 4){
        //         $category_label = "<label class='m-0 p-0' style='color:rgb(32,90,224);'>".__('Contract')."</label>";
        //     }
        // }


        return $category_label;
    }

    public function datePeriods()
    {
        return [
            'D' => __('Daily'),
            'W' => __('Weekly'),
            'M' => __('Monthly'),
        ];
    }

    public function datePeriodsNames($datePeriod){

        foreach ($this->datePeriods() as $key => $value) {
            if($key == $datePeriod)
            {
                return $value;
            }
        }
    }

    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }


    public function getIsoCodeFromResult($result){
        $check = strpos($result,"#");
        if ($check) {
            $code = explode("#", $result);

            if (count($code) != 2) {
                return $result;
            }

            $check2 = strpos($code[1], "ISO");
            $check3 = strpos($code[1], "CORE");
            if ($check2 || $check3) {
                $key = trim($code[1]);
                return $key;
            }
        }

        return $result;
    }

}
