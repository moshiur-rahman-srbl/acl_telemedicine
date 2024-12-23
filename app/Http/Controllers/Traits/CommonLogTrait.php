<?php

namespace App\Http\Controllers\Traits;

use App\Exports\ExportExcel;
use App\Exports\ExportExcelMultipleSheets;
use App\Models\BlockCC;
use App\Models\MerchantCommission;
use App\Notifications\SendNotification;
use App\Utils\CommonFunction;
use Carbon\Carbon;
use common\integration\BrandConfiguration;
use common\integration\DataCipher;
use common\integration\GlobalFunction;
use common\integration\ManageFile;
use common\integration\ManageLogging;
use common\integration\Traits\HttpServiceInfoTrait;
use common\integration\Traits\UniqueKeyGeneratorTrait;
use common\integration\Utility\File;
use common\integration\Utility\Url;
use Illuminate\Support\Carbon as IlluminateCarbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use common\integration\CommonNotification;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 3/1/2019
 * Time: 3:46 PM
 */
trait CommonLogTrait {
    use UniqueKeyGeneratorTrait, HttpServiceInfoTrait;

    public function _getCommonLogData($logData = array()) {
        return $logData;
    }

    public function createLog($data) {
        (new ManageLogging())->createLog($data);
    }

    public function getClientIp() {
        return $this->getClientIpAddress();
    }

    public function getUserAgent() {
        return $this->getUserAgentInfo();
    }

    public function make_round($value) {
        return round($value, 2);
    }

    public function validateCompany($id, $table_name) {
        $sql = "SELECT company_id FROM " . $table_name . " WHERE id =" . $id;
        $execute_query = DB::select($sql);
        if (!empty($execute_query)) {
            $company_id = $execute_query[0]->company_id;
            if (\auth()->user()->company_id != $company_id) {
                \auth()->logout();
                return true;
            } else {
                return false;
            }
        } else {
            \auth()->logout();
            return true;
        }
    }

    public function getGeneratedToken($unique_string) {

        return $this->generateUniqueKey($unique_string);
    }


    function getOperatorByMobileNo($mobile_no) {

        $part = substr($mobile_no, 0, 3);

        $operator = "";

        if ($part == 561 || ($part > 530 && $part < 540)) {
            $operator = MerchantCommission::TRUKCELL;
        } else if (($part > 539 && $part < 550)) {
            $operator = MerchantCommission::VODAFONE;
        } else if (($part > 499 && $part < 510) || ($part > 549 && $part < 560)) {
            $operator = MerchantCommission::TRUK_TELEKOM;
        }

        return $operator;
    }

    public function getSystemDate() {
        return Carbon::today();
    }

    public function sendNotification($user, $data, $template, $language = "tr") {
//        $physical_template = $template."_".$language;
        //Getting Template
        $template_en = $template . "_en";
        $template_tr = $template . "_tr";

        $html_en = view('notifications.' . $template_en, compact('data'))->render();
        $html_tr = view('notifications.' . $template_tr, compact('data'))->render();

        $notification_action = null;
        if (is_array($data) && array_key_exists('notification_action', $data) && in_array($data['notification_action'], CommonNotification::NOTIFICATION_ACTIONS)) {
            $notification_action = $data['notification_action'];
        }

        try {

            if ($user) {
                $user->notify(new SendNotification($html_en, $html_tr, $user, $notification_action));
            }
        } catch (\Throwable $e) {

        }
    }

    public function fileWrite($file_name, $data, $write_enable) {

        if ($write_enable && !empty($data)) {
            (new ManageFile())->otpWrite($data);
        }
    }


    public function fileExport($file_type, $data, $file_name, $header = null, $view_blade = null,
                               $should_store = false, $path = null, $disk = 'public', $title = null, $is_multi_sheets = false, $is_full_path_return = true, $extras=[])
    {
        return (new File())->fileExport($file_type, $data, $file_name, $header, $view_blade, $should_store, $path, $disk, $title, $is_multi_sheets, $is_full_path_return, false, false, $extras);
    }

    public function error_codes($key)
    {
        $bank_errors_en = [
            "ISO8583-01" => "Referral - call bank for manual approval.",
            "ISO8583-02" => "Fake Approval, but should not be used in a VPOS system, check with your bank. ",
            "ISO8583-03" => "Invalid merchant or service provider",
            "ISO8583-04" => "Pick-up card.",
            "ISO8583-05" => "Do not honour",
            "ISO8583-06" => "Error (found only in file update responses).",
            "ISO8583-07" => "Pick up card, special condition.",
            "ISO8583-08" => "Fake Approval, but should not be used in a VPOS system, check with your bank.",
            "ISO8583-11" => "Fake Approved (VIP), but should not be used in a VPOS system, check with your bank.",
            "ISO8583-12" => "Transaction is not valid.",
            "ISO8583-13" => "Invalid amount. ",
            "ISO8583-14" => "Invalid account number",
            "ISO8583-15" => "No such issuer",
            "ISO8583-19" => "Reenter, try again.",
            "ISO8583-20" => "Invalid amount.",
            "ISO8583-21" => "Unable to back out transaction.",
            "ISO8583-25" => "Unable to locate record on file.",
            "ISO8583-26" => "Transaction not found ",
            "ISO8583-27" => "Bank decline",
            "ISO8583-28" => "Original is denied",
            "ISO8583-29" => "Original not found ",
            "ISO8583-30" => "Format error (switch generated) ",
            "ISO8583-32" => "Referral (General)",
            "ISO8583-33" => "Expired card, pick-up",
            "ISO8583-34" => "Suspected fraud, pick-up",
            "ISO8583-36" => "Restricted card, pick-up",
            "ISO8583-37" => "Pick up card. Issuer wants card returned",
            "ISO8583-38" => "Allowable PIN tries exceeded, pick-up",
            "ISO8583-41" => "Lost card, Pick-up",
            "ISO8583-43" => "Stolen card, pick-up",
            "ISO8583-51" => "Insufficient funds",
            "ISO8583-52" => "No checking account",
            "ISO8583-53" => "No savings account",
            "ISO8583-54" => "Expired card.",
            "ISO8583-55" => "Incorrect PIN",
            "ISO8583-56" => "No card record",
            "ISO8583-57" => "Transaction not permitted to cardholder",
            "ISO8583-58" => "Transaction not permitted to terminal",
            "ISO8583-59" => "Fraud ",
            "ISO8583-61" => "Activity amount limit exceeded ",
            "ISO8583-62" => "Restricted card",
            "ISO8583-63" => "Security violation",
            "ISO8583-65" => "Activity limit exceeded",
            "ISO8583-75" => "Allowable number of PIN tries exceeded",
            "ISO8583-76" => "Key synchronization error",
            "ISO8583-77" => "Inconsistent data",
            "ISO8583-80" => "Date is not valid",
            "ISO8583-81" => "Encryption Error",
            "ISO8583-82" => "CVV Failure or CVV Value supplied is not valid",
            "ISO8583-83" => "Cannot verify PIN",
            "ISO8583-84" => "Invalid CVV.",
            "ISO8583-85" => "Declined (General) ",
            "ISO8583-91" => "Issuer or switch is inoperative ",
            "ISO8583-92" => "Timeout, reversal is trying",
            "ISO8583-93" => "Violation, cannot complete (installment, loyalty)",
            "ISO8583-96" => "System malfunction",
            "ISO8583-98" => "Duplicate Reversal",
            "ISO8583-99" => "Transaction Unsuccessful",
            "ISO8583-YK" => "Card in black list.",
            "ISO8583-SF" => "Check HOSTMSG for details",
            "ISO8583-GK" => "Foreign cards not permitted to the terminal.",
            "CORE-1000" => "System error. Request failed to send.",
            "CORE-1001" => "General initialization error",
            "CORE-1002" => "System error. First commit phase general exception",
            "CORE-1003" => "System error. Acquirer phase general exception.",
            "CORE-1004" => "System error. Response parameters general exception. ",
            "CORE-1005" => "System error. Last commit phase general exception.",
            "CORE-1006" => "Invalid value for 'Mode' parameter. Please check API manuals.",
            "CORE-1007" => "Invalid value for 'OrderType' for 'PbOrder'. Please check API manuals.",
            "CORE-1008" => "Invalid value for eci.",
            "CORE-1010" => "'Currency' is unparsable value. Please check and try again.",
            "CORE-1011" => "'User Name' can not be null or empty. Please check and try again.",
            "CORE-1012" => "'User Name' field size is out of limit. Please check and try again",
            "CORE-1013" => "'Merchant Id' can not be null or empty. Please check and try again.",
            "CORE-1014" => "'Merchant Id' field size is out of limit. Please check and try again.",
            "CORE-1015" => "'Order Id' field size is out of limit. Please check and try again.",
            "CORE-1016" => "'Criteria' field size is out of limit. Please check and try again",
            "CORE-1017" => "'Transaction Id' field size is out of limit. Please check and try again.",
            "CORE-1018" => " 'Amount/Total' field size is out of limit. Please check and try again",
            "CORE-1019" => "'Currency Code' field size is out of limit. Please check and try again",
            "CORE-1020" => "'Api Version' field size is out of limit. Please check and try again.",
            "CORE-1021" => "'Description' field size is out of limit. Please check and try again.",
            "CORE-1022" => "'Consumer IP' field size is out of limit. Please check and try again",
            "CORE-1023" => "'Installments' value is not valid. Please check and try again.",
            "CORE-1024" => "'Amount' can not be negative.",
            "CORE-1025" => "'Points' can not be negative.",
            "CORE-1026" => "'Instalment' can not be negative.",
            "CORE-1027" => "'Amount' must be equal to or greater than 'Points'.",
            "CORE-1028" => "'Transaction Id' must be used only for void request.",
            "CORE-1029" => "Instalment should be sent for this query.",
            "CORE-2001" => "This is an invalid transaction type. Auth, PreAuth, PostAuth, Credit, Void are valid.",
            "CORE-2002" => "Insufficient parameter for void.",
            "CORE-2003" => "Transaction type is empty.",
            "CORE-2004" => "Dimuid is empty",
            "CORE-2005" => "Null field problem. Check and try again.",
            "CORE-2006" => "Area text size problem. Check and try again.",
            "CORE-2007" => "Unparsable number usage.",
            "CORE-2008" => "Couldn't find any related transaction.",
            "CORE-2009" => "Zero (0) is not valid amount for sale and preauth transaction.",
            "CORE-2010" => "The credit card is expired.",
            "CORE-2011" => "The credit card expiry date is not in a valid format.",
            "CORE-2012" => "The credit card number is not in a valid format.",
            "CORE-2013" => "Invalid settlement request detected. Please, control the request xml.",
            "CORE-2014" => "Invalid query request detected. Please, control the request xml.",
            "CORE-2015" => "The credit card number is missing or empty.",
            "CORE-2016" => "Order has a waiting transaction.",
            "CORE-2020" => "The recurring period unit is missing or empty.",
            "CORE-2021" => "The recurring period unit is not valid.",
            "CORE-2022" => "The recurring period is not valid.",
            "CORE-2023" => "The recurring duration is not valid",
            "CORE-2024" => "Only sale orders can have recurring",
            "CORE-2025" => "Planned start date is not allowed for 3D transactions.",
            "CORE-2026" => "Pan 2 is not allowed for 3D transactions.",
            "CORE-2027" => "Pan 2 is not allowed for following transactions on an order",
            "CORE-2028" => "Invalid retry period unit.",
            "CORE-2029" => "Recurring or futurerequest is not allowed to plan for long term.",
            "CORE-2034" => "Instalment not allowed for recurring.",
            "CORE-2105" => "Currency not allowed for merchant.",
            "CORE-2106" => "Invalid Currency type. Currency is not in ISO-4217 specification.",
            "CORE-2115" => "A PostAuth can only be performed on a PreAuth transaction.",
            "CORE-2116" => "No successful transaction found for the order.",
            "CORE-2117" => "Credit is not valid because there are no settled or captured transactions with Order '{0}' .",
            "CORE-2118" => " A currency definition couldn't find for request",
            "CORE-2119" => "Currency mismatch between request and related transaction history",
            "CORE-2120" => "Void can only be performed on a successful transaction.",
            "CORE-2121" => "XID, CAVV or ECI did not match",
            "CORE-2122" => "There is no provision transaction in order history.",
            "CORE-2123" => "There is no matching order to refund",
            "CORE-2124" => "Instalment is not a valid option for foreign currencies",
            "CORE-2125" => "Usage of points is not a valid option for foreign currencies",
            "CORE-2126" => "Merchant closed.",
            "CORE-2127" => "Same XID has been used for multiple 3D transactions.",
            "CORE-2128" => "The delivery address provided is not compatible with the provisioning process in order history.",
            "CORE-2129" => "The extra fields provided are not compatible with the provisioning process in order history.",
            "CORE-2130" => "There is no allowed section for merchant. Please check your request or do not use any section information in your request.",
            "CORE-2131" => "Section information mismatch. Please check section information in your request.",
            "CORE-2133" => "There are multiple transactions in the order that can be voided.",
            "CORE-2204" => "Installment transaction is not permitted to the card.",
            "CORE-2205" => "Point transaction is not permitted to the card",
            "CORE-2206" => "Invalid MD Status value.",
            "CORE-2207" => "Points cannot be used for within a installment transaction.",
            "CORE-2208" => "PreAuth/Auth transactions without CVV is not allowed.",
            "CORE-2209" => "User id should be numeric",
            "CORE-2250" => "This request can be fraud. Pan (Credit Card Number) couldn't pass fraud controls. ",
            "CORE-2251" => "This request can be fraud. Customer IP couldn't pass fraud controls",
            "CORE-2252" => "This request is not allowed by merchant day/time allowance settings.",
            "CORE-2253" => "This request was blocked due to rule definitions. Please check the rule / fraud records.",
            "CORE-2254" => "This request is not allowed by merchant IP allowance settings.",
            "CORE-2255" => "This request is not allowed by merchant group IP allowance settings",
            "CORE-2256" => "This request is not allowed by merchant group day/time allowance settings.",
            "CORE-2304" => "There is an ongoing transaction related to the order",
            "CORE-2502" => "Transaction not permitted without expiry.",
            "CORE-2503" => "Cannot refund more than net amount.",
            "CORE-2504" => "Cannot refund on zero net amount.",
            "CORE-2505" => "Cannot post auth more than net amount.",
            "CORE-2506" => "Cannot post auth on zero net amount.",
            "CORE-2507" => "Order has already successful transaction.",
            "CORE-2508" => "Cannot refund, transaction not settled",
            "CORE-2509" => "Cannot post, no matching preauth.",
            "CORE-2510" => "Point transactions permitted only for sale or void sale",
            "CORE-2511" => "Cannot void, settlement required.",
            "CORE-2512" => "Not authorized to exceed pre authorization amount.",
            "CORE-2513" => "Post Authorization / Capturing can not be done after 15 days.",
            "CORE-2514" => "Cannot refund for old transactions or orders",
            "CORE-2515" => "Invalid Cvv.",
            "CORE-2516" => "Expiry should not be sent without pan.",
            "CORE-2517" => "Refund is not allowed for debit card transactions",
            "CORE-2601" => "Taksit/Installment mismatch, only use once.",
            "CORE-2301" => "Invalid Order ID",
            "CORE-2302" => "Order ID is in use",
            "CORE-2311" => "Virtual terminal locked. Try again later",
            "CORE-2312" => "Virtual terminal not defined.",
            "CORE-2313" => "Virtual terminal merchant not defined.",
            "CORE-2314" => "Virtual terminal locked. Try again later.",
            "CORE-2315" => "Virtual terminal STAN reached its own maximum batch capacity count.",
            "CORE-2316" => "Card number is not available according to bank cards list.",
            "CORE-2400" => "Virtual terminal lock check error",
            "CORE-2500" => "System failure. Parameters cannot be produced",
            "CORE-2501" => "System error. Prelockcheck parameters.",
            "CORE-2700" => "System Error. Acquirer transmit.",
            "CORE-2800" => "System Error. Acquirer Response Check.",
            "CORE-2900" => "System Error. Set Response Parameters",
            "CORE-3000" => "System Error. Last Commit.",
            "CORE-4000" => "Settlement can not be initiated manuelly for this merchant",
            "CORE-4001" => "Virtual terminal for settlement not found.",
            "CORE-4002" => "Virtual terminal is locked. Try again later",
            "CORE-4003" => "Virtual terminal is busy. Try again later",
            "CORE-4004" => "Unable to finish settlement",
            "CORE-4005" => "VPosBatch for resettlement not found ",
            "CORE-4007" => "Vpos is not locked",
            "CORE-4008" => "VPosBatchPool not found ",
            "CORE-4009" => "VPosBatchPool is locked.",
            "CORE-4010" => "Successful vposBatch already exists.",
            "CORE-4011" => "DimBatch not found.",
            "CORE-4012" => "Settlement acquirer phase general error.",
            "CORE-4013" => "Settlement initialization error.",
            "CORE-4014" => "Settlement initialization is not finished.",
            "CORE-4015" => "No currency for dim.",
            "CORE-4201" => "VPos for test message not found",
            "CORE-4202" => "System error. Sending test message"
        ];

        if (array_key_exists($key, $bank_errors_en)) {
            return $bank_errors_en[$key];
        } else {
            return "Unknown Error";
        }

//        if(app()->getLocale() == "tr"){
//            if (array_key_exists($key, $bank_errors_tr)) {
////                return $key.' '.$bank_errors_tr[$key];
//                return $bank_errors_tr[$key];
//            } else {
//                return "Unknown Error";
//            }
//        }else{
//            if (array_key_exists($key, $bank_errors_en)) {
////                return $key.' '.$bank_errors_en[$key];
//                return $bank_errors_en[$key];
//            } else {
//                return "Unknown Error";
//            }
//        }
    }

    public function customEncryptionDecryption($value, $secret_key, $action, $isURL = 0, $iv = null, $salt = null, $is_allow_url_encoding = false) {
        return DataCipher::customEncryptionDecryption($value, $secret_key, $action, $isURL, $iv, $salt, $is_allow_url_encoding);
        if ($action == 'encrypt') {

            if (is_array($value)){
                $value = implode('|', $value);
            }
            $value = $this->encrypt($value, $secret_key, $iv, $salt);
            if ($isURL){
                $value = str_replace('/', '__', $value);
            }

            return $value;

        } elseif ($action == 'decrypt') {

            if ($isURL){
                $value = str_replace('__', '/', $value);
            }

            return $this->decrypt($value, $secret_key);
        }
        return $value;
    }

    private function encrypt($data, $password, $iv = null, $salt = null){
        if (empty($data)){
            return $data;
        }
        if (empty($iv)){
            $iv = substr(sha1(mt_rand()), 0, 16);
        }

        $password = sha1($password);

        if (empty($salt)){
            $salt = substr(sha1(mt_rand()), 0, 4);
        }

        $saltWithPassword = hash('sha256', $password.$salt);

        $encrypted = openssl_encrypt(
            "$data", 'aes-256-cbc', "$saltWithPassword", null, $iv
        );
        $msg_encrypted_bundle = "$iv:$salt:$encrypted";
        return $msg_encrypted_bundle;
    }


    private function decrypt($msg_encrypted_bundle, $password){
        if (empty($msg_encrypted_bundle)){
            return $msg_encrypted_bundle;
        }
        $password = sha1($password);

        $components = explode( ':', $msg_encrypted_bundle );

        if(count($components) < 3){
            return $msg_encrypted_bundle;
        }

        $iv            = $components[0] ?? '';
        $salt = $components[1] ?? '';
        $salt          = hash('sha256', $password.$salt);
        $encrypted_msg = $components[2] ?? '';

        $decrypted_msg = openssl_decrypt(
            $encrypted_msg, 'aes-256-cbc', $salt, null, $iv
        );

        if ( $decrypted_msg === false ){
            return $msg_encrypted_bundle;
        }

//        $msg = substr( $decrypted_msg, 41 );
        return $decrypted_msg;
    }

    public function checkCC($card)
    {
        $blockCC = new BlockCC();
//        $card_status = $blockCC->checkCC($card);
        $card_status = $blockCC->isCardBlocked($card);

        return $card_status;
    }

    public function removeWhiteSpace($string)
    {
        $string = str_replace(' ', '', $string);
        return $string;
    }

    public function unsetKeys($data){

        if (isset($data['action'])){
            $newArr['action'] = $data['action'];
            $data = array_merge($newArr,$data);
        }
        if (isset($data['cc_no'])){
            $data['cc_no'] = CommonFunction::creditCardNoMasking($data['cc_no']);
        }
        if (isset($data['credit_card'])){
            $data['credit_card'] = CommonFunction::creditCardNoMasking($data['credit_card']);
        }

        if (isset($data['credit_card_no'])){
            $data['credit_card_no'] = CommonFunction::creditCardNoMasking($data['credit_card_no']);
        }
        if (isset($data['pan'])){
            $data['pan'] = CommonFunction::creditCardNoMasking($data['pan']);
        }

        if (isset($data['Pan'])){
            $data['Pan'] = CommonFunction::creditCardNoMasking($data['Pan']);
        }

        $removeKeys = array('store_key', 'year', 'cvv', 'month','expiry_year','expiry_month','cvv','app_secret','bankObj', 'cc_holder_name','merchantObj','Expiry',
            'Ecom_Payment_Card_ExpDate_Year','Ecom_Payment_Card_ExpDate_Month','cv2');
//        foreach ($removeKeys as $key) {
//            if (isset($data[$key])){
//                unset($data[$key]);
//            }
//        }
//        if (isset($data['info']) && is_array($data['info'])){
//            foreach ($removeKeys as $key) {
//                if (isset($data['info'][$key])){
//                    unset($data['info'][$key]);
//                }
//            }
//        }
        foreach ($removeKeys as $item){
            $this->removeKey($data, $item);
        }
        return $data;
    }

    private function removeKey(&$array, $key)
    {
        if (is_array($array))
        {
            if (isset($array[$key]))
            {
                unset($array[$key]);
            }
            if (count($array) > 0)
            {
                foreach ($array as $k => $arr)
                {
                    $this->removeKey($array[$k], $key);
                }
            }
        }
    }

    public function getXssProtectedvalue($value)
    {

        return strip_tags($value);

    }

    public function countryToIso($code)
    {
        $countries =
            [
                "AF"=>"004",
                "AL"=>"008",
                "DZ"=>"012",
                "AS"=>"016",
                "AD"=>"020",
                "AO"=>"024",
                "AI"=>"660",
                "AQ"=>"010",
                "AG"=>"028",
                "AR"=>"032",
                "AM"=>"051",
                "AW"=>"533",
                "AU"=>"036",
                "AT"=>"040",
                "AZ"=>"031",
                "BS"=>"044",
                "BH"=>"048",
                "BD"=>"050",
                "BB"=>"052",
                "BY"=>"112",
                "BE"=>"056",
                "BZ"=>"084",
                "BJ"=>"204",
                "BM"=>"060",
                "BT"=>"064",
                "BO"=>"068",
                "BQ"=>"535",
                "BA"=>"070",
                "BW"=>"072",
                "BV"=>"074",
                "BR"=>"076",
                "IO"=>"086",
                "BN"=>"096",
                "BG"=>"100",
                "BF"=>"854",
                "BI"=>"108",
                "CV"=>"132",
                "KH"=>"116",
                "CM"=>"120",
                "CA"=>"124",
                "KY"=>"136",
                "CF"=>"140",
                "TD"=>"148",
                "CL"=>"152",
                "CN"=>"156",
                "CX"=>"162",
                "CC"=>"166",
                "CO"=>"170",
                "KM"=>"174",
                "CD"=>"180",
                "CG"=>"178",
                "CK"=>"184",
                "CR"=>"188",
                "HR"=>"191",
                "CU"=>"192",
                "CW"=>"531",
                "CY"=>"196",
                "CZ"=>"203",
                "CI"=>"384",
                "DK"=>"208",
                "DJ"=>"262",
                "DM"=>"212",
                "DO"=>"214",
                "EC"=>"218",
                "EG"=>"818",
                "SV"=>"222",
                "GQ"=>"226",
                "ER"=>"232",
                "EE"=>"233",
                "SZ"=>"748",
                "ET"=>"231",
                "FK"=>"238",
                "FO"=>"234",
                "FJ"=>"242",
                "FI"=>"246",
                "FR"=>"250",
                "GF"=>"254",
                "PF"=>"258",
                "TF"=>"260",
                "GA"=>"266",
                "GM"=>"270",
                "GE"=>"268",
                "DE"=>"276",
                "GH"=>"288",
                "GI"=>"292",
                "GR"=>"300",
                "GL"=>"304",
                "GD"=>"308",
                "GP"=>"312",
                "GU"=>"316",
                "GT"=>"320",
                "GG"=>"831",
                "GN"=>"324",
                "GW"=>"624",
                "GY"=>"328",
                "HT"=>"332",
                "HM"=>"334",
                "VA"=>"336",
                "HN"=>"340",
                "HK"=>"344",
                "HU"=>"348",
                "IS"=>"352",
                "IN"=>"356",
                "ID"=>"360",
                "IR"=>"364",
                "IQ"=>"368",
                "IE"=>"372",
                "IM"=>"833",
                "IL"=>"376",
                "IT"=>"380",
                "JM"=>"388",
                "JP"=>"392",
                "JE"=>"832",
                "JO"=>"400",
                "KZ"=>"398",
                "KE"=>"404",
                "KI"=>"296",
                "KP"=>"408",
                "KR"=>"410",
                "KW"=>"414",
                "KG"=>"417",
                "LA"=>"418",
                "LV"=>"428",
                "LB"=>"422",
                "LS"=>"426",
                "LR"=>"430",
                "LY"=>"434",
                "LI"=>"438",
                "LT"=>"440",
                "LU"=>"442",
                "MO"=>"446",
                "MG"=>"450",
                "MW"=>"454",
                "MY"=>"458",
                "MV"=>"462",
                "ML"=>"466",
                "MT"=>"470",
                "MH"=>"584",
                "MQ"=>"474",
                "MR"=>"478",
                "MU"=>"480",
                "YT"=>"175",
                "MX"=>"484",
                "FM"=>"583",
                "MD"=>"498",
                "MC"=>"492",
                "MN"=>"496",
                "ME"=>"499",
                "MS"=>"500",
                "MA"=>"504",
                "MZ"=>"508",
                "MM"=>"104",
                "NA"=>"516",
                "NR"=>"520",
                "NP"=>"524",
                "NL"=>"528",
                "NC"=>"540",
                "NZ"=>"554",
                "NI"=>"558",
                "NE"=>"562",
                "NG"=>"566",
                "NU"=>"570",
                "NF"=>"574",
                "MP"=>"580",
                "NO"=>"578",
                "OM"=>"512",
                "PK"=>"586",
                "PW"=>"585",
                "PS"=>"275",
                "PA"=>"591",
                "PG"=>"598",
                "PY"=>"600",
                "PE"=>"604",
                "PH"=>"608",
                "PN"=>"612",
                "PL"=>"616",
                "PT"=>"620",
                "PR"=>"630",
                "QA"=>"634",
                "MK"=>"807",
                "RO"=>"642",
                "RU"=>"643",
                "RW"=>"646",
                "RE"=>"638",
                "BL"=>"652",
                "SH"=>"654",
                "KN"=>"659",
                "LC"=>"662",
                "MF"=>"663",
                "PM"=>"666",
                "VC"=>"670",
                "WS"=>"882",
                "SM"=>"674",
                "ST"=>"678",
                "SA"=>"682",
                "SN"=>"686",
                "RS"=>"688",
                "SC"=>"690",
                "SL"=>"694",
                "SG"=>"702",
                "SX"=>"534",
                "SK"=>"703",
                "SI"=>"705",
                "SB"=>"090",
                "SO"=>"706",
                "ZA"=>"710",
                "GS"=>"239",
                "SS"=>"728",
                "ES"=>"724",
                "LK"=>"144",
                "SD"=>"729",
                "SR"=>"740",
                "SJ"=>"744",
                "SE"=>"752",
                "CH"=>"756",
                "SY"=>"760",
                "TW"=>"158",
                "TJ"=>"762",
                "TZ"=>"834",
                "TH"=>"764",
                "TL"=>"626",
                "TG"=>"768",
                "TK"=>"772",
                "TO"=>"776",
                "TT"=>"780",
                "TN"=>"788",
                "TR"=>"792",
                "TM"=>"795",
                "TC"=>"796",
                "TV"=>"798",
                "UG"=>"800",
                "UA"=>"804",
                "AE"=>"784",
                "GB"=>"826",
                "UM"=>"581",
                "US"=>"840",
                "UY"=>"858",
                "UZ"=>"860",
                "VU"=>"548",
                "VE"=>"862",
                "VN"=>"704",
                "VG"=>"092",
                "VI"=>"850",
                "WF"=>"876",
                "EH"=>"732",
                "YE"=>"887",
                "ZM"=>"894",
                "ZW"=>"716",
                "AX"=>"248"
            ];

        $code = array($code);
        if(array_keys_exists($code,$countries)){
            return $countries[$code[0]];
        }else{
            return 0;
        }
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
            'any' => '/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/'
        ];

        $ctr = 1;
        foreach ($matchingPatterns as $key=>$pattern) {
            if (preg_match($pattern, $str)) {
                return $format == 'string' ? $key : $ctr;
            }
            $ctr++;
        }
    }

    public function verifyAccessControl($auth_id, $user_id)
    {
        if ($auth_id != $user_id) {
            exit();
        }
    }

    public function isNonSecureConnection(){

        return Url::isNonSecureConnection();
    }

    public function getAlbarakaOrderid($orderId){
        $orderId = str_pad($orderId, 20, "0", STR_PAD_LEFT);
        return $orderId;
    }

    public function getYapKrediOrderId($order_id, $length = 20){
        if (strlen($order_id) < $length){
            $order_id = str_pad($order_id, $length, "0", STR_PAD_LEFT);
        }
        return $order_id;
    }

    public function getAlbarakaMac($macParams)
    {
        $mac = hash("sha256", $macParams, true);
        $mac = base64_encode($mac);
        return $mac;
    }


    public function arrayToXML(array $data, $rootTag = '', $headerTag = true){
        $xml = new \SimpleXMLElement(!empty($rootTag) ? $rootTag : '<rootTag/>');
        $this->ToXML($xml, $data);

        $result = $xml->asXML();
        if (!$headerTag){
            $result = explode("\n", $result, 2)[1];
            $result = str_replace("\n", "", $result);
        }
        return $result;
    }

    private function ToXML(\SimpleXMLElement $object, array $data){
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $new_object = $object->addChild($key);
                $this->ToXML($new_object, $value);
            } else {
                // if the key is an integer, it needs text with it to actually work.
                if ($key == (int) $key) {
                    $key = "$key";
                }

                $object->addChild($key, GlobalFunction::encodeHtmlSpecialCharacter($value));
            }
        }
    }

    public function GetTurkposOrderId($order_id){
        return 'isem'.$order_id;
    }

    public function RemoveTurkposOrderIdString($order_id){
        return str_replace('isem', '', $order_id);
    }

    public function manipulateDenizPttResponse($response){
        $resultArray = [];
        if(!empty($response)){
            $array = explode(';;', $response);
            foreach ($array as $v){
                $key = explode('=', $v);
                if (isset($key[0]) && isset($key[1])){
                    $resultArray[$key[0]] = $key[1];
                }
            }
        }
        return $resultArray;
    }

    public function getWeekDays()
    {
        return  [
            "Monday" => __("Monday"),
            "Thuesday" => __("Tuesday"),
            "Wednesday" => __("Wednesday"),
            "Thursday" => __("Thursday"),
            "Friday" => __("Friday"),
            "Saturday" => __("Saturday"),
            "Sunday" => __("Sunday"),
        ];
    }



}
