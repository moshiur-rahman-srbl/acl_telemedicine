<?php

namespace App\Http\Controllers\Traits;

use App\Exports\ExportExcel;
use App\Exports\ReportingExport;
use App\Models\Bank;
use App\Models\BlockCC;
use App\Models\Cashout;
use App\Models\CCPayment;
use App\Models\Currency;
use App\Models\ImportedTransaction;
use App\Models\Merchant;
use App\Models\MerchantCommission;
use App\Models\MerchantSettings;
use App\Models\MerchantTransactionLimit;
use App\Models\MerchantWebHookKeys;
use App\Models\NotificationAutomation;
use App\Models\PaymentProvider;
use App\Models\PaymentRecOption;
use App\Models\Pos;
use App\Models\PurchaseRequest;
use App\Models\Sale;
use App\Models\SaleCurrencyConversion;
use App\Models\SaleRecurringCard;
use App\Models\SinglePaymentMerchantCommission;
use App\Models\TransactionState;
use App\Notifications\SendNotification;
use App\Utils\CommonFunction;
use Carbon\Carbon;
use common\integration\BrandConfiguration;
use common\integration\CraftgateApi;
use common\integration\CurrencyExchangeApi;
use common\integration\DataCipher;
use common\integration\FastpayPayment;
use common\integration\FastpayWalletServices;
use common\integration\GlobalExtras;
use common\integration\GlobalFunction;
use common\integration\InformationMasking;
use common\integration\ManageLogging;
use common\integration\MdStatus;
use common\integration\CommonNotification;
use common\integration\Payment\PaymentApiHandler;
use common\integration\Payment\PaymentHashKeyHandler;
use common\integration\Payment\PaymentRequestParameterHandler;
use common\integration\ProviderErrorCodeHandler;
use common\integration\Safe2PayApi;
use common\integration\SaleTransaction;
use common\integration\Traits\HttpServiceInfoTrait;
use common\integration\Traits\UniqueKeyGeneratorTrait;
use common\integration\Payment\PaymentResponse;
use common\integration\Utility\Arr;
use common\integration\Utility\Json;
use common\integration\Utility\Language;
use common\integration\Utility\Str;
use common\integration\Utility\Xml;
use common\integration\Wix;
use Illuminate\Support\Facades\File;
use Matrix\Exception;
use function Couchbase\defaultDecoder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
//use Excel;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Config;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 3/1/2019
 * Time: 3:46 PM
 */
trait CommonLogTrait
{
    use UniqueKeyGeneratorTrait, HttpServiceInfoTrait;

    public $is_transaction_cancelled_by_end_user = false;
    public $is_refund_empty_amount = false;


    public function getClientIp()
    {
        return $this->getClientIpAddress();

    }

    public function getMerchantServerIp()
    {
        return $this->getServerIpAddress();
    }

    public function getUserAgent()
    {
        return $this->getUserAgentInfo();
    }

    public function make_round($value)
    {
        return round($value, 2);
    }

    public function validateCompany($id, $table_name)
    {
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

    public function export($file_type, $data, $file_name, $header = null, $view_blade = null, $isMPDF = false , $output_destination = 'D')
    {
		
		
	    $file_name = \common\integration\Utility\File::manipulateBrandResourceDynamicPath($file_name);
		
		
        if ($file_type == 'pdf' || $file_type == "pdf_attacment"){
            ini_set('max_execution_time', '500');
            ini_set("pcre.backtrack_limit", "5000000");
        }

        if ($file_type == "pdf") {
            $html = view($view_blade, compact('data', 'header', 'file_name'))->render();
            $htmlContentLength = strlen($html) + 1;
            ini_set("pcre.backtrack_limit", $htmlContentLength);

            $pdf = new \Mpdf\Mpdf();
           if ($this->isNonSecureConnection()){
              $pdf->curlAllowUnsafeSslRequests = true;
           }
            $pdf->WriteHTML($html);
            $pdf->Output($file_name . "." . $file_type, $output_destination);

            return true;

        } else if ($file_type == "xls" || $file_type == "xlsx" || $file_type == "csv") {
            return Excel::download(new ExportExcel($data, $header), $file_name . '.' . $file_type);
        } else if ($file_type == "pdf_attacment") {
            $html = \View::make($view_blade)->with('data', $data)->render();
            $htmlContentLength = strlen($html) + 1;
            ini_set("pcre.backtrack_limit", $htmlContentLength);

            $pdf = new \Mpdf\Mpdf();
            if ($this->isNonSecureConnection()){
                $pdf->curlAllowUnsafeSslRequests = true;
            }
            $pdf->WriteHTML($html);
            $path = $file_name . '.pdf';

            $directory = dirname($path);

            if (!empty($directory) || $directory != '.'){
                $storagePath = \common\integration\Utility\File::getStoragePath();

                if (!File::exists($storagePath . "/" . $directory)) {
                    File::makeDirectory($storagePath . "/" . $directory, $mode = 0777, true, true);
                }
            }

            $pdfloc = Storage::disk('public')->path($path);
            $pdf->Output($pdfloc, 'F');
            return $pdfloc;
        }else if($file_type == "zip"){
            $path = $file_name . '.zip';

            $directory = dirname($path);

            if (!empty($directory) || $directory != '.'){
                $storagePath = \common\integration\Utility\File::getStoragePath();

                if (!File::exists($storagePath . "/" . $directory)) {
                    File::makeDirectory($storagePath . "/" . $directory, $mode = 0777, true, true);
                }
            }

            $ziploc = Storage::disk('public')->path($path);
            $zip = new \ZipArchive();
            if ($zip->open($ziploc, \ZipArchive::CREATE)){
                foreach ($data as $realPath){
                    $zip->addFile($realPath,basename($realPath));
                }
            }

            $zip->close();
            return $ziploc;
        }
    }

    public function sendNotification($user, $data, $template, $language = "tr")
    {
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

    public function fileWrite($file_name, $data, $write_enable)
    {

        if ($write_enable && !empty($data)) {
            $file_path = public_path('files/' . $file_name);

            if (!is_dir(public_path('files/'))) {
                mkdir(public_path('files'));
                chmod(public_path('files'), 0777);
            }
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            $file = fopen($file_path, "w+");
            fwrite($file, $data);
            chmod($file_path, 0777);


            fclose($file);
        }

    }


    public function getOperatorByMobileNo($mobile_no)
    {
        $cur_mobile = str_replace('+90', '', $mobile_no);
        $part = substr($cur_mobile, 0, 3);
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

    public function getSystemDate()
    {
        return Carbon::today();
    }

    /**
     * Send a POST request without using PHP's curl functions.
     *
     * @param string $url The URL you are sending the POST request to.
     * @param array $postVars Associative array containing POST values.
     * @return string The output response.
     * @throws Exception If the request fails.
     */
    function post($url, $postVars = array())
    {
        //Transform our POST array into a URL-encoded query string.
        $postStr = http_build_query($postVars);
        //Create an $options array that can be passed into stream_context_create.
        $options = array(
            'http' =>
                array(
                    'method' => 'POST', //We are using the POST HTTP method.
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postStr //Our URL-encoded query string.
                )
        );
        //Pass our $options array into stream_context_create.
        //This will return a stream context resource.
        $streamContext = stream_context_create($options);
        //Use PHP's file_get_contents function to carry out the request.
        //We pass the $streamContext variable in as a third parameter.
        $result = file_get_contents($url, false, $streamContext);
        //If $result is FALSE, then the request has failed.
        if ($result === false) {
            //If the request failed, throw an Exception containing
            //the error.
            $error = error_get_last();
            throw new Exception('POST request failed: ' . $error['message']);
        }
        //If everything went OK, return the response.
        return $result;
    }

    public function getFirstLastName($name)
    {


        $firstName = "";
        $lastName = "";

        $name = trim($name);

        $nameArray = explode(" ", $name);


        if (count($nameArray) > 1) {
            $lastName = $nameArray[count($nameArray) - 1];
            for ($i = 0; $i < count($nameArray) - 1; $i++) {
                $firstName = $firstName . " " . $nameArray[$i];
            }
        } else {
            $firstName = $nameArray[0];
        }


        return array($firstName, $lastName);

    }

    public function getInvoiceId($id = '')
    {
        return time() . rand(1000, 9999) . $id;
    }

    public function _getCommonLogData($logData = array())
    {
        return $logData;
    }

    public function createLog($data)
    {
        (new ManageLogging())->createLog($data);
    }

    public function removeWhiteSpace($string)
    {
//        $string = str_replace(' ', '', $string);
        return Str::replace(' ','', $string);
    }


    public function unsetCardPAN($logData)
    {

        if (!empty($logData)) {

            if (isset($logData['bankObj'])) {
                unset($logData['bankObj']);
            }
            if (isset($logData['client_id'])) {
                unset($logData['client_id']);
            }
            if (isset($logData['store_key'])) {
                unset($logData['store_key']);
            }

            if (isset($logData['card_no'])) {
                unset($logData['card_no']);
            }

            if (isset($logData['month'])) {
                unset($logData['month']);
            }

            if (isset($logData['expiry_month'])) {
                unset($logData['expiry_month']);
            }

            if (isset($logData['expiry_year'])) {
                unset($logData['expiry_year']);
            }

            if (isset($logData['year'])) {
                unset($logData['year']);
            }

            if (isset($logData['cvv'])) {
                unset($logData['cvv']);
            }

            if (isset($logData['name'])) {
                unset($logData['name']);
            }
            if (isset($logData['card_holder_name'])) {
                unset($logData['card_holder_name']);
            }

            if (isset($logData['merchantObj'])) {
                unset($logData['merchantObj']);
            }


        }


        return $logData;

    }


    public function unsetLogData($data)
    {

        if (isset($data['info'])) {
            if (isset($data['info']['bankObj'])) {
                unset($data['info']['bankObj']);
            }
            if (isset($data['info']['client_id'])) {
                unset($data['info']['client_id']);
            }
            if (isset($data['info']['store_key'])) {
                unset($data['info']['store_key']);
            }
            if (isset($data['info']['card_no'])) {
                unset($data['info']['card_no']);
            }

            if (isset($data['info']['expiry_month'])) {
                unset($data['info']['expiry_month']);
            }
            if (isset($data['info']['month'])) {
                unset($data['info']['month']);
            }

            if (isset($data['info']['expiry_year'])) {
                unset($data['info']['expiry_year']);
            }
            if (isset($data['info']['year'])) {
                unset($data['info']['year']);
            }

            if (isset($data['info']['cvv'])) {
                unset($data['info']['cvv']);
            }

            if (isset($data['name'])) {
                unset($data['name']);
            }
            if (isset($data['card_holder_name'])) {
                unset($data['card_holder_name']);
            }


            if (isset($data['info']['merchantObj'])) {
                unset($data['info']['merchantObj']);
            }
        }

        return $data;
    }

    public function remove_success_fail_url($logData)
    {

        if (isset($logData['request']['invoice'])) {
            $invoice_data = $logData['request']['invoice'];
            if (Json::isValid($invoice_data)){
                $item = Json::decode($invoice_data,true);
            }else if (Arr::isOfType($invoice_data)){
                $item = (array)$invoice_data;
            }else{
                $item = [];
            }
//            $item = (array)(json_decode($logData['request']['invoice']));
            unset($logData['request']['invoice']);

            if (array_keys_exists(['success_url'], $item))
                unset($item["success_url"]);

            if (array_keys_exists(['return_url'], $item))
                unset($item["return_url"]);

            if (array_keys_exists(['cancel_url'], $item))
                unset($item["cancel_url"]);

            if (array_keys_exists(['fail_url'], $item))
                unset($item["fail_url"]);
            $item = json_encode($item);
            $logData['request']['invoice'] = $item;
        } else {
            if (isset($logData['success_url'])) {
                unset($logData['success_url']);
            }

            if (isset($logData['return_url'])) {
                unset($logData['return_url']);
            }

            if (isset($logData['cancel_url'])) {
                unset($logData['cancel_url']);
            }

            if (isset($logData['fail_url'])) {
                unset($logData['fail_url']);
            }

            if (isset($logData['info']['success_url_3d'])) {
                unset($logData['info']['success_url_3d']);
            }

            if (isset($logData['info']['failed_url_3d'])) {
                unset($logData['info']['failed_url_3d']);
            }

            if (isset($logData['success_url_3d'])) {
                unset($logData['success_url_3d']);
            }

            if (isset($logData['failed_url_3d'])) {
                unset($logData['failed_url_3d']);
            }
        }

        return $logData;
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
            "ISO8583-14" => "Incorrect card number",
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
            "ISO8583-61" => "Please call your bank",
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


    public function customEncryptionDecryption($value, $secret_key, $action, $isURL = 0, $iv = null, $salt = null,  $is_allow_url_encoding = false)
    {
        return DataCipher::customEncryptionDecryption($value, $secret_key, $action, $isURL, $iv, $salt, $is_allow_url_encoding);
        /*
        if ($action == 'encrypt') {
            if (is_array($value)){
                $value = implode('|', $value);
            }
            $value = $this->encrypt($value, $secret_key, $iv, $salt);
            if ($isURL) {
                $value = str_replace('/', '__', $value);
            }

            return $value;

        } elseif ($action == 'decrypt') {

            if ($isURL) {
                $value = str_replace('__', '/', $value);
            }
            $result = $this->decrypt($value, $secret_key);

            if (strpos($result, '|') !== false){
                $array = explode('|', $result);
                return $array;
            }
            return $result;
        }
        return $value;
        */
    }

    /*
    private function encrypt($data, $password, $iv = null, $salt = null)
    {
        if (empty($data)) {
            return $data;
        }
        if (empty($iv)){
            $iv = substr(sha1(mt_rand()), 0, 16);
        }

        $password = sha1($password);

        if(empty($salt)){
            $salt = substr(sha1(mt_rand()), 0, 4);
        }


        $saltWithPassword = hash('sha256', $password . $salt);

        $encrypted = openssl_encrypt(
            "$data", 'aes-256-cbc', "$saltWithPassword", null, $iv
        );
        $msg_encrypted_bundle = "$iv:$salt:$encrypted";
        return $msg_encrypted_bundle;
    }


    private function decrypt($msg_encrypted_bundle, $password)
    {
        if (empty($msg_encrypted_bundle)) {
            return $msg_encrypted_bundle;
        }
        $password = sha1($password);

        $components = explode(':', $msg_encrypted_bundle);
        if (count($components) < 3) {
            return $msg_encrypted_bundle;
        }

        $iv = $components[0] ?? '';
        $salt = $components[1] ?? '';
        $salt = hash('sha256', $password . $salt);
        $encrypted_msg = $components[2] ?? '';

        $decrypted_msg = openssl_decrypt(
            $encrypted_msg, 'aes-256-cbc', $salt, null, $iv
        );

        if ($decrypted_msg === false) {
            return $msg_encrypted_bundle;
        }

//        $msg = substr( $decrypted_msg, 41 );
        return $decrypted_msg;
    }
    */

    public function generatePaymentHashKey($installments, $merchant_key, $currency_code, $total, $credit_card = null){

        if (count($installments) > 0){
            $newInstallments = [];

            foreach ($installments as $installment){
                $pos_id = $installment['pos_id'];
/*                $data =
                    [
                        $total,
                        $installment['installments_number'],
                        $currency_code,
                        $merchant_key,
                        $pos_id
                    ];*/

                $data = (new PaymentHashKeyHandler())
                    ->getHashKeyParamsForPayment(
                        PaymentApiHandler::TYPE_OF_PAY_API_OLD,
                        $total,
                        $installment['installments_number'],
                        $currency_code,
                        $merchant_key,
                        null,
                        $credit_card,
                        $pos_id
                    );
                $hashKey = $this->customEncryptionDecryption($data, config('app.brand_secret_key'), 'encrypt', 1);

                $installment['hash_key'] = $hashKey;
                $newInstallments[]  = $installment;
            }
            return $newInstallments;

        }

        return $installments;

    }

    public function validateCashOutHashKey($hash_key, $input, $app_secret= ''){
        $status_code = '';
        $status_message = '';



        if (!empty($app_secret)){
            $password = $app_secret;
        }else{
            $password = config('app.brand_secret_key');
        }

        $data = $this->customEncryptionDecryption($hash_key, $password, 'decrypt', 1);

        $sum_of_amount_column = $data[0] ?? 0.00;
        if (!is_numeric($sum_of_amount_column)){
            $sum_of_amount_column = 0.00;
        }

        $first_row_iban = $data[1] ?? '';
        $first_row_amount = $data[2] ?? 0.00;
        if (!is_numeric($first_row_amount)){
            $first_row_amount = 0.00;
        }
        $first_row_currency = $data[3] ?? '';
        $first_row_gsm = $data[4] ?? '';

        $sum_of_amount_column = number_format($sum_of_amount_column, 2, '.', '');
        $input['sum_of_amount_column'] = number_format($input['sum_of_amount_column'], 2, '.', '');

        if ($sum_of_amount_column != $input['sum_of_amount_column']) {
            $status_code = 68;
            $status_message = "Summation of amount column mismatch with hash key";
        }

        if (empty($status_code) && $first_row_iban != $input['first_row_iban']){
            $status_code = 68;
            $status_message = "First row iban mismatch with hash_key";
        }

        if (empty($status_code) && $first_row_amount != $input['first_row_amount']){
            $status_code = 68;
            $status_message = "First row amount mismatch with hash_key";
        }

        if (empty($status_code) && $first_row_currency != $input['first_row_currency']){
            $status_code = 68;
            $status_message = "First row currency mismatch with hash_key";
        }

        if (empty($status_code) && $first_row_gsm != $input['first_row_gsm']){
            $status_code = 68;
            $status_message = "First row gsm mismatch with hash_key";
        }

        if (empty($status_code)){
            $status_code = 100;
        }
        $status_message = Language::isLocalize($status_message);
        return [$status_code, $status_message];
    }

    public function restrictVisaCardPaymix($bankObj, $cardNo){

       $status_code = '';
       $status_message = '';

       $cardType = PaymentProvider::getCardType($cardNo);

       if (!empty($bankObj) && $bankObj->payment_provider == config('constants.PAYMENT_PROVIDER.PAYMIX')
       && $cardType == config('constants.CARD_TYPE.VISA')) {
         /* $status_code = 102;
          $status_message = "Pos is not defined for your card scheme";*/
       }

       $status_message = Language::isLocalize($status_message);
       return [$status_code, $status_message];
    }


    public function validatePaymentHashKey($hash_key, $input, $app_secret= '', $type = 0){//type 1 = refund, type 2 = orderStatus

        $status_code = '';
        $status_message = '';

        try {
            if (!empty($app_secret)){
                $password = $app_secret;
            }else{
                $password = config('app.brand_secret_key');
            }

            $data = $this->customEncryptionDecryption($hash_key, $password, 'decrypt', 1);

            //if ($type == 2){//Order Status
            if (is_array($data) && count($data) == count($input)) {
                $i = 0;
                foreach ($input as $key => $value) {

                    if ($key == 'total' && !$this->is_refund_empty_amount) {
                        $data[$i] = number_format($data[$i], 4, '.', '');
                        $value = number_format($value, 4, '.', '');

                    }

                    if ($key == 'total' && $this->is_refund_empty_amount) {
                        $data[$i] = $value = number_format(0, 4, '.', '');
                    }
                    if ($data[$i] != $value) {
                        $status_code = 68;
                        $status_message = 'Invalid hash key';
                        break;
                    }

                    $i++;
                }
            } else {
                $status_code = 68;
                $status_message = 'Invalid hash key';
            }
        } catch (\Throwable $th) {
            $status_code = 68;
            $status_message = 'Invalid hash key';

            \common\integration\Utility\Exception::log($th);
        }

        if (empty($status_code)) {
            $status_code = 100;
        }

        return [$status_code, $status_message];
//        }



//
//        $total = $data[0] ?? 0;
//        if (!is_numeric($total)){
//            $total = 0.00;
//        }
//
//        $installments = [];
//        if (isset($data[1])){
//
//            if (strpos($data[1], ',') !== false) {
//                $installments =  explode(',', $data[1]);
//            }else{
//                $installments = [$data[1]];
//            }
//        }
//
//
//        $currency_code = $data[2] ?? '';
//
//        if ($type == 1){
//            $merchant_key = $data[2] ?? '';
//        }else{
//            $merchant_key = $data[3] ?? '';
//        }
//
//        if (empty($app_secret)){
//            $pos_id = $data[4] ?? 0;
//        }else{
//            if ($type == 1){
//                $invoice_id = $data[1] ?? 0;
//            }else{
//                $invoice_id = $data[4] ?? 0;
//            }
//        }
//
//        if (isset($input['total'])){
//            $total = number_format($total, 2, '.', '');
//            $input['total'] = number_format($input['total'], 2, '.', '');
//
//            if ($total != $input['total']) {
//                $status_code = 68;
//                $status_message = "Total Amount mismatch with hash key";
//            }
//        }
//
//        if (empty($status_code) && isset($input['currency_code']) && $currency_code != $input['currency_code'] ){
//            $status_code = 68;
//            $status_message = "Currency mismatch with hash_key";
//        }
//
//        if (empty($status_code) && isset($merchant_key) && $merchant_key != $input['merchant_key']){
//            $status_code = 68;
//            $status_message = "Merchant key mismatch with hash key";
//        }
//
//        if (empty($status_code) && isset($input['installments_number']) && !in_array($input['installments_number'], $installments)){
//            $status_code = 68;
//            $status_message = "Installment number mismatch with hash key";
//        }
//
//        if (empty($status_code) && isset($input['pos_id']) && $pos_id != $input['pos_id']){
//            $status_code = 68;
//            $status_message = "Pos Id mismatch with hash key";
//        }
//
//        if (empty($status_code) && isset($invoice_id) && $invoice_id != $input['invoice_id']){
//            $status_code = 68;
//            $status_message = "Invoice id mismatch with hash key";
//        }
//
//        if (empty($status_code)){
//            $status_code = 100;
//        }
//
//
//        return [$status_code, $status_message];
    }

    public function checkCC($card, $merchant_id)
    {
        $blockCC = new BlockCC();
//        $card_status = $blockCC->checkCC($card);
        $card_status = $blockCC->isCardBlocked($card, $merchant_id);

        return $card_status;
    }

    public function verifyAccessControl($auth_id, $user_id)
    {
        if ($auth_id != $user_id) {
            exit();
        }
    }

    public function unsetKeys($data, $masking_card = false)
    {
        return InformationMasking::unsetKeys($data, $masking_card);
/*
        if (isset($data['action'])) {
            $newArr['action'] = $data['action'];
            $data = array_merge($newArr, $data);
        }

        $removeKeys = array('store_key', 'year', 'cvv', 'month', 'expiry_year', 'expiry_month', 'cvv',
            'app_secret', 'bankObj','merchantObj', 'credit_card_no','cc_no', 'credit_card','Pan', 'Expiry', 'card_no', 'Password');


        foreach ($removeKeys as $item){
            $this->removeKey($data, $item, $masking_card);
        }
        return $data;
*/
    }
/*
    private function removeKey(&$array, $key, $masking_card)
    {
      //  InformationMasking::removeKey($array,$key,$masking_card);
        /*
        $creditCardArray = ['cc_no', 'credit_card', 'credit_card_no', 'expiry_year','expiry_month'];

        if (is_array($array))
        {
            if (isset($array[$key]))
            {
                if (!is_array($array[$key]) && in_array($key, $creditCardArray) ){
                    if ($key == 'expiry_month'){
                        if (strlen($array[$key]) < 5){
                            $array['part_1'] = $this->customEncryptionDecryption($array[$key], config('app.brand_secret_key'),'encrypt', 0, \config('constants.ENCRYPTION_FIXED_IV'), \config('constants.ENCRYPTION_FIXED_SALT'));
                            unset($array[$key]);
                        }

                    }elseif ($key == 'expiry_year'){
                        if (strlen($array[$key]) < 5){
                            $array['part_2'] = $this->customEncryptionDecryption($array[$key], config('app.brand_secret_key'),'encrypt', 0, \config('constants.ENCRYPTION_FIXED_IV'), \config('constants.ENCRYPTION_FIXED_SALT'));
                            unset($array[$key]);
                        }

                    }else{
                        if ($masking_card){
                            $array[$key] = CommonFunction::creditCardNoMasking($array[$key]);
                        }else{
                            $array[$key] = $this->customEncryptionDecryption($array[$key], config('app.brand_secret_key'),'encrypt', 0, \config('constants.ENCRYPTION_FIXED_IV'), \config('constants.ENCRYPTION_FIXED_SALT'));

                        }
                    }

                }else{
                    unset($array[$key]);
                }

            }
            if (count($array) > 0)
            {
                foreach ($array as $k => $arr)
                {
                    $this->removeKey($array[$k], $key, $masking_card);
                }
            }
        }

    }
*/

    public function getXssProtectedvalue($value)
    {

        return strip_tags($value);

    }

    public function countryToIso($code, $get_country_code = false, $country_iso_code='')
    {
        $countries =
            [
                "AF" => "004",
                "AL" => "008",
                "DZ" => "012",
                "AS" => "016",
                "AD" => "020",
                "AO" => "024",
                "AI" => "660",
                "AQ" => "010",
                "AG" => "028",
                "AR" => "032",
                "AM" => "051",
                "AW" => "533",
                "AU" => "036",
                "AT" => "040",
                "AZ" => "031",
                "BS" => "044",
                "BH" => "048",
                "BD" => "050",
                "BB" => "052",
                "BY" => "112",
                "BE" => "056",
                "BZ" => "084",
                "BJ" => "204",
                "BM" => "060",
                "BT" => "064",
                "BO" => "068",
                "BQ" => "535",
                "BA" => "070",
                "BW" => "072",
                "BV" => "074",
                "BR" => "076",
                "IO" => "086",
                "BN" => "096",
                "BG" => "100",
                "BF" => "854",
                "BI" => "108",
                "CV" => "132",
                "KH" => "116",
                "CM" => "120",
                "CA" => "124",
                "KY" => "136",
                "CF" => "140",
                "TD" => "148",
                "CL" => "152",
                "CN" => "156",
                "CX" => "162",
                "CC" => "166",
                "CO" => "170",
                "KM" => "174",
                "CD" => "180",
                "CG" => "178",
                "CK" => "184",
                "CR" => "188",
                "HR" => "191",
                "CU" => "192",
                "CW" => "531",
                "CY" => "196",
                "CZ" => "203",
                "CI" => "384",
                "DK" => "208",
                "DJ" => "262",
                "DM" => "212",
                "DO" => "214",
                "EC" => "218",
                "EG" => "818",
                "SV" => "222",
                "GQ" => "226",
                "ER" => "232",
                "EE" => "233",
                "SZ" => "748",
                "ET" => "231",
                "FK" => "238",
                "FO" => "234",
                "FJ" => "242",
                "FI" => "246",
                "FR" => "250",
                "GF" => "254",
                "PF" => "258",
                "TF" => "260",
                "GA" => "266",
                "GM" => "270",
                "GE" => "268",
                "DE" => "276",
                "GH" => "288",
                "GI" => "292",
                "GR" => "300",
                "GL" => "304",
                "GD" => "308",
                "GP" => "312",
                "GU" => "316",
                "GT" => "320",
                "GG" => "831",
                "GN" => "324",
                "GW" => "624",
                "GY" => "328",
                "HT" => "332",
                "HM" => "334",
                "VA" => "336",
                "HN" => "340",
                "HK" => "344",
                "HU" => "348",
                "IS" => "352",
                "IN" => "356",
                "ID" => "360",
                "IR" => "364",
                "IQ" => "368",
                "IE" => "372",
                "IM" => "833",
                "IL" => "376",
                "IT" => "380",
                "JM" => "388",
                "JP" => "392",
                "JE" => "832",
                "JO" => "400",
                "KZ" => "398",
                "KE" => "404",
                "KI" => "296",
                "KP" => "408",
                "KR" => "410",
                "KW" => "414",
                "KG" => "417",
                "LA" => "418",
                "LV" => "428",
                "LB" => "422",
                "LS" => "426",
                "LR" => "430",
                "LY" => "434",
                "LI" => "438",
                "LT" => "440",
                "LU" => "442",
                "MO" => "446",
                "MG" => "450",
                "MW" => "454",
                "MY" => "458",
                "MV" => "462",
                "ML" => "466",
                "MT" => "470",
                "MH" => "584",
                "MQ" => "474",
                "MR" => "478",
                "MU" => "480",
                "YT" => "175",
                "MX" => "484",
                "FM" => "583",
                "MD" => "498",
                "MC" => "492",
                "MN" => "496",
                "ME" => "499",
                "MS" => "500",
                "MA" => "504",
                "MZ" => "508",
                "MM" => "104",
                "NA" => "516",
                "NR" => "520",
                "NP" => "524",
                "NL" => "528",
                "NC" => "540",
                "NZ" => "554",
                "NI" => "558",
                "NE" => "562",
                "NG" => "566",
                "NU" => "570",
                "NF" => "574",
                "MP" => "580",
                "NO" => "578",
                "OM" => "512",
                "PK" => "586",
                "PW" => "585",
                "PS" => "275",
                "PA" => "591",
                "PG" => "598",
                "PY" => "600",
                "PE" => "604",
                "PH" => "608",
                "PN" => "612",
                "PL" => "616",
                "PT" => "620",
                "PR" => "630",
                "QA" => "634",
                "MK" => "807",
                "RO" => "642",
                "RU" => "643",
                "RW" => "646",
                "RE" => "638",
                "BL" => "652",
                "SH" => "654",
                "KN" => "659",
                "LC" => "662",
                "MF" => "663",
                "PM" => "666",
                "VC" => "670",
                "WS" => "882",
                "SM" => "674",
                "ST" => "678",
                "SA" => "682",
                "SN" => "686",
                "RS" => "688",
                "SC" => "690",
                "SL" => "694",
                "SG" => "702",
                "SX" => "534",
                "SK" => "703",
                "SI" => "705",
                "SB" => "090",
                "SO" => "706",
                "ZA" => "710",
                "GS" => "239",
                "SS" => "728",
                "ES" => "724",
                "LK" => "144",
                "SD" => "729",
                "SR" => "740",
                "SJ" => "744",
                "SE" => "752",
                "CH" => "756",
                "SY" => "760",
                "TW" => "158",
                "TJ" => "762",
                "TZ" => "834",
                "TH" => "764",
                "TL" => "626",
                "TG" => "768",
                "TK" => "772",
                "TO" => "776",
                "TT" => "780",
                "TN" => "788",
                "TR" => "792",
                "TM" => "795",
                "TC" => "796",
                "TV" => "798",
                "UG" => "800",
                "UA" => "804",
                "AE" => "784",
                "GB" => "826",
                "UM" => "581",
                "US" => "840",
                "UY" => "858",
                "UZ" => "860",
                "VU" => "548",
                "VE" => "862",
                "VN" => "704",
                "VG" => "092",
                "VI" => "850",
                "WF" => "876",
                "EH" => "732",
                "YE" => "887",
                "ZM" => "894",
                "ZW" => "716",
                "AX" => "248"
            ];

       if($get_country_code && !empty($country_iso_code)){
          $country_code = array_search($country_iso_code, $countries);
          if ($country_code !== false) {
             return $country_code;
          }else{
             return '';
          }
       }

        $code = array($code);
        if (array_keys_exists($code, $countries)) {
            return $countries[$code[0]];
        } else {
            return 0;
        }
    }

    private function determineCurrencyConversion($errorCode, $currency_code, $installment,$cardType,
                                                 $merchantObj, $is_pay_by_marketplace, $is_russian_bin = false){
        $isAllowCurrencyConversion = false;
        $toCurrencyID = '';
        $toCurrencyCode = '';

        if($is_russian_bin){
            return (new SaleCurrencyConversion())->determineRussianCurrencyConversion($currency_code, $installment);
        }

        if (!empty($merchantObj)
            && $currency_code != Currency::TRY_CODE
            && $installment == 1
            && $is_pay_by_marketplace != 1
            ){

            if ($merchantObj->allow_foreign_currency_to_tl == 1 && GlobalFunction::isInRange($errorCode, 44, 49)){
                $toCurrencyID = Currency::TRY;
                $toCurrencyCode = Currency::TRY_CODE;
            }elseif ($merchantObj->allow_foreign_currency_to_tl == 2){
                $toCurrencyID = Currency::TRY;
                $toCurrencyCode = Currency::TRY_CODE;
            }elseif ($merchantObj->allow_foreign_currency_to_tl == 3){
                if ($cardType == 2 && $currency_code == Currency::USD_CODE ){
                    $toCurrencyID = Currency::EUR;
                    $toCurrencyCode = Currency::EUR_CODE;
                }elseif($cardType == 2 && $currency_code == Currency::EUR_CODE ){
                    $toCurrencyID = Currency::EUR;
                    $toCurrencyCode = Currency::EUR_CODE;
                }else{
                    $toCurrencyID = Currency::TRY;
                    $toCurrencyCode = Currency::TRY_CODE;
                }
            }elseif ($merchantObj->allow_foreign_currency_to_tl == 4 && $currency_code == Currency::USD_CODE){
                $toCurrencyID = Currency::EUR;
                $toCurrencyCode = Currency::EUR_CODE;
            }

            if (!empty($toCurrencyCode) && $currency_code != $toCurrencyCode){
                $isAllowCurrencyConversion = true;
            }

        }

        return [$isAllowCurrencyConversion, $toCurrencyID, $toCurrencyCode];
    }


    public function reassignPosId($errorCode, $errorMessage, $data, $merchantObj, $posObj,
                                  $is_recurring, $is_2d, $bankObj, $currencyObj, $payable_amount)
    {


//        $eurConversionMerchantIds = [11596, 69782];
//        $exceptMerchantIds = [1819318193];

        if (isset($data['cc_no'])) {
            $cardNo = $data['cc_no'];
        } else {
            $cardNo = $data['card'];
        }
        if (isset($data['installments_number'])) {
            $installmentNo = $data['installments_number'];
        } else {
            $installmentNo = $data['installment'];
        }

        $is_russian_bin = $data["is_russian_bin"] ?? "";


        $cardType = PaymentProvider::getCardType($cardNo);

        list($isAllowCurrencyConversion, $to_currency_id, $to_currency_code) = $this->determineCurrencyConversion(
            $errorCode, $data['currency_code'], $installmentNo, $cardType, $merchantObj,
            $data['is_pay_by_marketplace'] ?? false, $is_russian_bin);

        if (empty($this->ccpayment->merchantSettingsObj)){
            $this->ccpayment->merchantSettingsObj = (new MerchantSettings())->getMerchantSettingByMerchantId($merchantObj->id);
        }


        if ($isAllowCurrencyConversion) {
            $logData['action'] = 'REASSIGN_POS_USING_CURRENCY_CONVERSION';
            $logData['invoice_id'] = $data['invoice_id'] ?? '';

//            $isConvertCurrency = true;
//            if(in_array($merchantObj->id, $exceptMerchantIds) && $cardType == 2 ) {
//                $isConvertCurrency = false;
//            }elseif (in_array($merchantObj->id, $eurConversionMerchantIds) && $data['currency_code'] == Currency::EUR_CODE){
//                $isConvertCurrency = false;
//            }

            // dd($cardNo,$exceptMerchantIds,$isMasterCard);


//            $to_currency_code = Currency::TRY_CODE;
//            $to_currency_id = Currency::TRY;

//            if (in_array($merchantObj->id, $eurConversionMerchantIds) && $data['currency_code'] == Currency::USD_CODE) {
//                $to_currency_code = Currency::EUR_CODE;
//                $to_currency_id = Currency::EUR;
//            }

            //        if (!empty($errorCode) && $merchantObj->allow_foreign_currency_to_tl == 1 && $posObj->currency_id != Currency::TRY){

            //Currency Exchange
            $currencyExchangeApi = new CurrencyExchangeApi($data['total'], $data['currency_code'], $to_currency_code);
            // $currencyExchangeApi->convertByXeAPI();

            if ($currencyExchangeApi->status_code == 100) {

                $bank = new Bank();
               // for restrict recurring pos searching except MSU
                $is_recurring = $this->manipulateIsRecurring($is_recurring);
//                $ccpayment = new CCPayment();
                $installmentList = $this->ccpayment->determineInstallmentByCardNew($cardNo, $to_currency_id,
                    $currencyExchangeApi->exchange_amount, $merchantObj, $this->ccpayment->merchantSettingsObj,
                    $is_recurring, $is_2d);

                if (count($installmentList) > 0) {
                    $installmentObj = (object)collect($installmentList)->where('installments_number', $installmentNo)->first();
                    if (!empty($installmentObj)) {

                        list($errorCode, $errorMessage) = $this->checkMerchantTransactionLimit($merchantObj, $to_currency_id,
                            PaymentRecOption::CREDITCARD, $installmentObj->payable_amount, !$is_2d);
                        if (empty($errorCode)) {
                            $data['currency_exchange'] = true;
                            $data['currency_exchange_rate'] = $currencyExchangeApi->exchange_rate;
                            $data['currency_exchange_amount'] = $currencyExchangeApi->exchange_amount;

                            if (isset($data['installments_number'])) {
                                $data['old_installments_number'] = $data['installments_number'];
                                unset($data['installments_number']);
                                $data['installments_number'] = $installmentObj->installments_number;
                            } else {
                                $data['old_installment'] = $data['installment'];
                                unset($data['installment']);
                                $data['installment'] = $installmentObj->installments_number;
                            }

                            $data['old_total'] = $data['total'];
                            unset($data['total']);
                            $data['total'] = $currencyExchangeApi->exchange_amount;

                            $data['old_pos_id'] = $data['pos_id'];
                            unset($data['pos_id']);
                            $data['pos_id'] = $installmentObj->pos_id;

                            $data['old_currency_code'] = $data['currency_code'];
                            unset($data['currency_code']);
                            $data['currency_code'] = $to_currency_code;

                            if (isset($data['currency_id'])) {
                                $data['old_currency_id'] = $data['currency_id'];
                                unset($data['currency_id']);
                                $data['currency_id'] = $to_currency_id;
                            }

                            if (isset($data['items'])) {
                                $data['old_items'] = $data['items'];
                                unset($data['items']);
                            }

                            $data['items'] = [[
                                'name' => "Updated Product by Provider",
                                'price' => $currencyExchangeApi->exchange_amount,
                                'quantity' => 1,
                                'description' => "Updated items array after currency conversion",
                            ]];
                            //                            $data['hash_key'] = $installmentObj->hash_key;
                            $payable_amount = $installmentObj->payable_amount;

                            $this->ccpayment->setPosObjectFromPosListByPosId($data['pos_id']);

                            $this->ccpayment->bankObj = $bank->findBankByID($this->ccpayment->posObj->bank_id);

                            $currency = new Currency();
                            $this->ccpayment->currencyObj = $currency->getCurrencyById($this->ccpayment->posObj->currency_id);



                            $this->ccpayment->setMerchantPosCommissionFromMerchantPosCommissionList($merchantObj->id, $data['pos_id'],$installmentNo );

                            $this->ccpayment->setPosInstallmentObjFromPosInstallmentList($data['pos_id'],$installmentNo);


                            if ($installmentNo < 2){
                                $this->ccpayment->setSinglePaymentCommissionObj($merchantObj->id, $this->ccpayment->posObj->currency_id,  $this->ccpayment);
                            }



                        } else {
                            $logData['message'] = $errorMessage;
                        }
                        //

                    } else {
                        if (empty($errorCode)) {
                            $errorCode = 36;
                            $errorMessage = $to_currency_code.' POS not found for this installment at currency conversion';
                        }
                        $logData['message'] = $to_currency_code.' POS not found for this installment at currency conversion';
                    }
                } else {

                    if (empty($errorCode)) {
                        $errorCode = 36;
                        $errorMessage = $to_currency_code.' POS not found for this installment at currency conversion';
                    }

                    $logData['message'] = $to_currency_code.' Installment Not Found at currency conversion';
                }


            } else {

                //For force currency conversion, error code was null. But for limit exceed, error code range 44 to 49..
                if (empty($errorCode)) {
                    $errorCode = 81;
                    $errorMessage = 'Currency conversion failed from API';
                }
                $logData['message'] = 'Failed from currency exchange Api';

            }

            if (isset($logData['message'])) {
                $this->createLog($logData);
            }


        }


//        }
        $errorMessage = Language::isLocalize($errorMessage);
        return [$errorCode, $errorMessage, $data, $this->ccpayment->posObj,
            $this->ccpayment->bankObj, $this->ccpayment->currencyObj, $payable_amount];
    }

    public function checkMerchantTransactionLimit($merchantObj, $currency_id, $payment_type_id, $payable_amount ,$is_3d)
    {
        $merchant_id = $merchantObj->id;
        $payment_source_array = GlobalFunction::get3dPaymentSource();
        $transaction_type = 1;
        if(!$is_3d){
            $transaction_type = 2; // 2D
        }

        $first_day_of_month = Carbon::now()->firstOfMonth()->toDateTimeString();
        $last_day_of_month = Carbon::now()->endOfMonth()->toDateTimeString();
        $day_start = Carbon::now()->startOfDay()->toDateTimeString();
        $day_end = Carbon::now()->endOfDay()->toDateTimeString();

        $merchantTransLimitObjList = MerchantTransactionLimit::query()
            ->where('merchant_id', $merchant_id)
            ->where('currency_id', $currency_id)
            ->where('transaction_type', $transaction_type)
            ->where( function ($query) use ($payment_type_id) {
                $query->where('payment_type_id', $payment_type_id)
                    ->orWhere('payment_type_id', 0);
            })->get();



        $merchantTransLimitObjAll = $merchantTransLimitObjList->where('payment_type_id', 0)->first();
        $merchantTransLimitObj = $merchantTransLimitObjList->where('payment_type_id', $payment_type_id)->first();


        //Initialize variables
        $checkLimitAll = false;
        $checkLimit = false;

        $checkDailyTranAmountAll = false;
        $checkDailyTranCountAll = false;
        $checkMonthlyTranAmountAll = false;
        $checkMonthlyTranCountAll = false;
        $checkDailyExpireDateAll = false;
        $checkMonthlyExpireDateAll = false;

        $checkDailyTranAmount = false;
        $checkDailyTranCount = false;
        $checkMonthlyTranAmount = false;
        $checkMonthlyTranCount = false;
        $checkDailyExpireDate = false;
        $checkMonthlyExpireDate = false;

        $checkTransactionWise = false;
        $checkTransactionWiseExpireDate = false;

        if (!empty($merchantTransLimitObjAll)) {

            //    $is_all_option_chosen  = true;

            //set for checking limit
            if (!empty($merchantTransLimitObjAll->daily_max_amount)) {
                $checkDailyTranAmountAll = true;
            }


            if (!empty($merchantTransLimitObjAll->daily_max_no)) {
                $checkDailyTranCountAll = true;
            }

            if (!empty($merchantTransLimitObjAll->daily_expire_date)) {
                $checkDailyExpireDateAll = true;
            }

            if (!empty($merchantTransLimitObjAll->monthly_max_amount)) {
                $checkMonthlyTranAmountAll = true;
            }


            if (!empty($merchantTransLimitObjAll->monthly_max_no)) {
                $checkMonthlyTranCountAll = true;
            }

            if (!empty($merchantTransLimitObjAll->monthly_expire_date)) {
                $checkMonthlyExpireDateAll = true;
            }


            //Decide to validate or NOT
            if ($checkDailyTranAmountAll || $checkDailyTranCountAll || $checkMonthlyTranAmountAll || $checkMonthlyTranCountAll) {
                $checkLimitAll = true;
            }
        }


        if (!empty($merchantTransLimitObj)) {
            //set for checking limit
            if (!empty($merchantTransLimitObj->daily_max_amount)) {
                $checkDailyTranAmount = true;
            }


            if (!empty($merchantTransLimitObj->daily_max_no)) {
                $checkDailyTranCount = true;
            }

            if (!empty($merchantTransLimitObj->daily_expire_date)) {
                $checkDailyExpireDate = true;
            }

            if (!empty($merchantTransLimitObj->monthly_max_amount)) {
                $checkMonthlyTranAmount = true;
            }


            if (!empty($merchantTransLimitObj->monthly_max_no)) {
                $checkMonthlyTranCount = true;
            }

            if (!empty($merchantTransLimitObj->monthly_expire_date)) {
                $checkMonthlyExpireDate = true;
            }

            if (!empty($merchantTransLimitObj->transaction_wise_min_amount) || !empty($merchantTransLimitObj->transaction_wise_max_amount)) {
                $checkTransactionWise = true;
            }

            if (!empty($merchantTransLimitObj->transaction_wise_expire_date)) {
                $checkTransactionWiseExpireDate = true;
            }


            //Decide to validate or NOT
            if ($checkDailyTranAmount || $checkDailyTranCount || $checkMonthlyTranAmount || $checkMonthlyTranCount || $checkTransactionWise) {
                $checkLimit = true;
            }
        }


        $saleList = [];


        //  if(){
        /*

          if($checkMonthlyTranAmountAll || $checkMonthlyTranCountAll || $checkMonthlyTranAmount || $checkMonthlyTranCount ){

                  // write montly query
                  if($checkMonthlyTranAmountAll || $checkMonthlyTranCountAll){
                      $saleList = Sale::query()
                          ->select('payment_type_id','gross', 'created_at')
                          ->where('merchant_id', $merchant_id)
                          ->where('transaction_state_id','!=', TransactionState::FAILED)
                          ->where('currency_id', $currency_id)
                          ->where('created_at','>=', $first_day_of_month)
                          ->where('created_at','<=', $last_day_of_month)
                          ->get();


                  } else {
                      $saleList = Sale::query()
                          ->select('payment_type_id','gross', 'created_at')
                          ->where('merchant_id', $merchant_id)
                          ->where('transaction_state_id','!=', TransactionState::FAILED)
                          ->where('currency_id', $currency_id)
                          ->where('payment_type_id', $payment_type_id)
                          ->where('created_at','>=', $first_day_of_month)
                          ->where('created_at','<=', $last_day_of_month)
                          ->get();

                  }

              } else if($checkDailyTranAmountAll || $checkDailyTranCountAll || $checkDailyTranAmount || $checkDailyTranCount)  {
                  //Write daily query
                  if($checkDailyTranAmountAll || $checkDailyTranCountAll){

                      $saleList = Sale::query()
                          ->select('payment_type_id','gross', 'created_at')
                          ->where('merchant_id', $merchant_id)
                          ->where('transaction_state_id','!=', TransactionState::FAILED)
                          ->where('currency_id', $currency_id)
                          ->where('created_at','>=', $day_start)
                          ->where('created_at','<=', $day_end)
                          ->get();
                  } else {

                      $saleList = Sale::query()
                          ->select('payment_type_id','gross', 'created_at')
                          ->where('merchant_id', $merchant_id)
                          ->where('transaction_state_id','!=', TransactionState::FAILED)
                          ->where('currency_id', $currency_id)
                          ->where('payment_type_id', $payment_type_id)
                          ->where('created_at','>=', $day_start)
                          ->where('created_at','<=', $day_end)
                          ->get();
                  }
              }
         // }

  */

        $status = '';
        $message = '';


        if ($checkLimit) {

            if ($checkDailyTranAmount || $checkDailyTranCount) {

                $total_daily_sale_amount = 0;
                $total_daily_sale_count = 0;
                // SQL calculate DAILY sum(gross) and count(sum) for merchant_id, currency_id, payment_type id

                $daily_sales = DB::table('sales')
                    ->select(DB::raw('sum(gross) as gross, count(id) as count'))
                    ->where('merchant_id', $merchant_id)
                    ->where('transaction_state_id', '!=', TransactionState::FAILED)
                    ->where('currency_id', $currency_id)
                    ->where('payment_type_id', $payment_type_id)
                    ->where('created_at', '>=', $day_start)
                    ->where('created_at', '<=', $day_end);

                if ($is_3d) {
                    $daily_sales->whereIn('payment_source', $payment_source_array);
                } else {
                    $daily_sales->whereNotIn('payment_source', $payment_source_array);
                }
                $daily_sales = $daily_sales->first();

                if (!empty($daily_sales)) {
                    $total_daily_sale_amount = $daily_sales->gross;
                    $total_daily_sale_count = $daily_sales->count;
                }

                $total_daily_sale_amount = $total_daily_sale_amount + $payable_amount;
                $total_daily_sale_count = $total_daily_sale_count + 1;

                if($checkDailyExpireDate){
                    $merchantTransLimitDailyObj = $merchantTransLimitObj->where('daily_expire_date', '>', Carbon::now()->toDateTimeString())->first();
                }else{
                    $merchantTransLimitDailyObj = $merchantTransLimitObj;
                }

                if(!empty($merchantTransLimitDailyObj)){

                    if ($checkDailyTranAmount && $merchantTransLimitDailyObj->daily_max_amount < $total_daily_sale_amount) {
                        $status = 45;
                        $message = __('Merchant daily transaction amount limit is crossed');
                    } else if ($checkDailyTranCount && $merchantTransLimitDailyObj->daily_max_no < $total_daily_sale_count) {
                        $status = 44;
                        $message = __('Merchant daily number of transaction limit is crossed');
                    }
                }
            }


            if (empty($message) && ($checkMonthlyTranAmount || $checkMonthlyTranCount)) {

                $total_monthly_sale_amount = 0;
                $total_monthly_sale_count = 0;

                // SQL calculate MONTHLY sum(gross) and count(sum) for merchant_id, currency_id, payment_type id
                $monthly_sales = DB::table('sales')
                    ->select(DB::raw('sum(gross) as gross, count(id) as count'))
                    ->where('merchant_id', $merchant_id)
                    ->where('transaction_state_id', '!=', TransactionState::FAILED)
                    ->where('currency_id', $currency_id)
                    ->where('payment_type_id', $payment_type_id)
                    ->where('created_at', '>=', $first_day_of_month)
                    ->where('created_at', '<=', $last_day_of_month);

                if ($is_3d) {
                    $monthly_sales->whereIn('payment_source', $payment_source_array);
                } else {
                    $monthly_sales->whereNotIn('payment_source', $payment_source_array);
                }
                $monthly_sales = $monthly_sales->first();


                if (!empty($monthly_sales)) {
                    $total_monthly_sale_amount = $monthly_sales->gross;
                    $total_monthly_sale_count = $monthly_sales->count;
                }


                $total_monthly_sale_amount = $total_monthly_sale_amount + $payable_amount;
                $total_monthly_sale_count = $total_monthly_sale_count + 1;

                if($checkMonthlyExpireDate){
                    $merchantTransLimitMonthlyObj = $merchantTransLimitObj->where('monthly_expire_date', '>', Carbon::now()->toDateTimeString())->first();
                }else{
                    $merchantTransLimitMonthlyObj = $merchantTransLimitObj;
                }
                if(!empty($merchantTransLimitMonthlyObj)){

                    if ($checkMonthlyTranAmount && $merchantTransLimitMonthlyObj->monthly_max_amount < $total_monthly_sale_amount) {
                        $status = 47;
                        $message = __('Merchant monthly transaction amount limit is crossed');
                    } else if ($checkMonthlyTranCount && $merchantTransLimitMonthlyObj->monthly_max_no < $total_monthly_sale_count) {
                        $status = 46;
                        $message = __('Merchant monthly number of transaction limit is crossed');
                    }
                }


            }

            if (empty($message) && $checkTransactionWise) {

                if($checkTransactionWiseExpireDate){
                    $merchantTransLimitTransactionWiseObj = $merchantTransLimitObj->where('transaction_wise_expire_date', '>', Carbon::now()->toDateTimeString())->first();
                }else{
                    $merchantTransLimitTransactionWiseObj = $merchantTransLimitObj;
                }

                if(!empty($merchantTransLimitTransactionWiseObj)){

                    if ($payable_amount < $merchantTransLimitTransactionWiseObj->transaction_wise_min_amount) {
                        $status = 48;
                        $message = 'Minimum transaction limit per transaction has been violated';
                    } else if ($payable_amount > $merchantTransLimitTransactionWiseObj->transaction_wise_max_amount) {
                        $status = 49;
                        $message = 'Maximum transaction limit  per transaction has been violated';
                    }
                }
            }


        }


        if (empty($message) && $checkLimitAll) {

            if ($checkDailyTranAmountAll || $checkDailyTranCountAll) {

                // SQL calculate DAILY sum(gross) and count(sum) for merchant_id, currency_id

                $total_daily_sale_amount = 0;
                $total_daily_sale_count = 0;

                $daily_sales = DB::table('sales')
                    ->select(DB::raw('sum(gross) as gross, count(id) as count'))
                    ->where('merchant_id', $merchant_id)
                    ->where('transaction_state_id', '!=', TransactionState::FAILED)
                    ->where('currency_id', $currency_id)
//                        ->where('payment_type_id', $payment_type_id)
                    ->where('created_at', '>=', $day_start)
                    ->where('created_at', '<=', $day_end);

                if ($is_3d) {
                    $daily_sales->whereIn('payment_source', $payment_source_array);
                } else {
                    $daily_sales->whereNotIn('payment_source', $payment_source_array);
                }
                $daily_sales = $daily_sales->first();

                if (!empty($daily_sales)) {
                    $total_daily_sale_amount = $daily_sales->gross;
                    $total_daily_sale_count = $daily_sales->count;
                }


                $total_daily_sale_amount = $total_daily_sale_amount + $payable_amount;
                $total_daily_sale_count = $total_daily_sale_count + 1;

                if($checkDailyExpireDateAll){

                    $merchantTransLimitObjDailyAll = $merchantTransLimitObjAll->where('daily_expire_date', '>', Carbon::now()->toDateTimeString())->first();
                }else{
                    $merchantTransLimitObjDailyAll = $merchantTransLimitObjAll;
                }

                if(!empty($merchantTransLimitObjDailyAll)){

                    if ($checkDailyTranAmountAll && $merchantTransLimitObjDailyAll->daily_max_amount < $total_daily_sale_amount) {
                        $status = 45;
                        $message = __('Merchant daily transaction amount limit is crossed');
                    } else if ($checkDailyTranCountAll && $merchantTransLimitObjDailyAll->daily_max_no < $total_daily_sale_count) {
                        $status = 44;
                        $message = __('Merchant daily number of transaction limit is crossed');
                    }
                }
            }


            if (empty($message) && ($checkMonthlyTranAmountAll || $checkMonthlyTranCountAll)) {

                // SQL calculate MONTHLY sum(gross) and count(sum) for merchant_id, currency_id

                $total_monthly_sale_amount = 0;
                $total_monthly_sale_count = 0;

                $monthly_sales = DB::table('sales')
                    ->select(DB::raw('sum(gross) as gross, count(id) as count'))
                    ->where('merchant_id', $merchant_id)
                    ->where('transaction_state_id', '!=', TransactionState::FAILED)
                    ->where('currency_id', $currency_id)
//                        ->where('payment_type_id', $payment_type_id)
                    ->where('created_at', '>=', $first_day_of_month)
                    ->where('created_at', '<=', $last_day_of_month);
                if ($is_3d) {
                    $monthly_sales->whereIn('payment_source', $payment_source_array);
                } else {
                    $monthly_sales->whereNotIn('payment_source', $payment_source_array);
                }
                $monthly_sales = $monthly_sales->first();

                if (!empty($monthly_sales)) {
                    $total_monthly_sale_amount = $monthly_sales->gross;
                    $total_monthly_sale_count = $monthly_sales->count;
                }

                $total_monthly_sale_amount = $total_monthly_sale_amount + $payable_amount;
                $total_monthly_sale_count = $total_monthly_sale_count + 1;

                if($checkMonthlyExpireDateAll){
                    $merchantTransLimitMonthlyObjAll = $merchantTransLimitObjAll->where('monthly_expire_date', '>', Carbon::now()->toDateTimeString())->first();
                }else{
                    $merchantTransLimitMonthlyObjAll = $merchantTransLimitObjAll;
                }

                if(!empty($merchantTransLimitMonthlyObjAll)){

                    if ($checkMonthlyTranAmountAll && $merchantTransLimitMonthlyObjAll->monthly_max_amount < $total_monthly_sale_amount) {
                        $status = 47;
                        $message = __('Merchant monthly transaction amount limit is crossed');
                    } else if ($checkMonthlyTranCountAll && $merchantTransLimitMonthlyObjAll->monthly_max_no < $total_monthly_sale_count) {
                        $status = 46;
                        $message = __('Merchant monthly number of transaction limit is crossed');
                    }
                }

            }


        }

        if (!empty($message)) {
            $mercObj = $merchantObj;
            $emailSend = false;
            $data['email_data']['merchant_name'] = $data['sms_data']['merchant_name'] = $mercObj->name;
            $data['email_data']['expired_date'] = $data['sms_data']['expired_date'] = Carbon::now();

            if ($status == 44 || $status == 45) {
                $emailSend = true;
                $data['email_template'] = $data['sms_template'] = 'limit.daily_limit';
                $data['subject'] = 'daily_limit_subject';

            } elseif ($status == 46 || $status == 47) {
                $emailSend = true;
                $data['email_template'] = $data['sms_template'] = 'limit.monthly_limit';
                $data['subject'] = 'monthly_limit_subject';
            }

            if ($emailSend){
                $data['language'] = $mercObj->user->language ? $mercObj->user->language : 'tr';
                $data['user_id'] = $mercObj->user_id;
                $data['merchant_id'] = $mercObj->id;
                $data['sender_email'] = config('app.SYSTEM_NO_REPLY_ADDRESS');
                $data['receiver_email'] = $mercObj->user->email;
                $data['bcc'] = $mercObj->brand_accountant_email ? $mercObj->brand_accountant_email : config('app.ACCOUNTANT_EMAIL');
                $data['delete_at'] = date('Y-m-d 00:00:00', strtotime('+1 day'));
                $data['phone'] = $mercObj->user->phone;
                $notiAutoObj = new NotificationAutomation();
                $search['user_id'] = $data['user_id'];
                $search['merchant_id'] = $data['merchant_id'];
                $search['from_date'] = date('Y-m-d 00:00:00');
                $search['to_date'] = date('Y-m-d 23:59:59');

                $old_lang = app()->getLocale();
                app()->setLocale('en');
                $search['subject_en'] = __('subject.'.$data['subject']);
                app()->setLocale('tr');
                $search['subject_tr'] = __('subject.'.$data['subject']);
                app()->setLocale($old_lang);

                $checkIfExisted = $notiAutoObj->findEntry($search);
                if(empty($checkIfExisted)) {
                    $notiAutoObj->insertEntry($data, true, true);
                }
            }


        }

        $message = Language::isLocalize($message);
        return [$status, $message];

    }

    public function getMerchantTransactionAmountAndNumber($merchant_id)
    {
        $first_day_of_month = Carbon::now()->firstOfMonth()->toDateTimeString();
        $last_day_of_month = Carbon::now()->endOfMonth()->toDateTimeString();
        $day_start = Carbon::now()->startOfDay()->toDateTimeString();
        $day_end = Carbon::now()->endOfDay()->toDateTimeString();

        $merchantTransLimitObjList = MerchantTransactionLimit::query()
            ->where('merchant_id', $merchant_id)
            ->orWhere('payment_type_id', 0)
            ->get();


        $saleList = Sale::query()->select('gross', 'created_at')->where('created_at', '>=', $first_day_of_month)
            ->where('created_at', '<=', $last_day_of_month)->get();

        $todaySaleList = $saleList->where('created_at', '>=', $day_start)
            ->where('created_at', '<=', $day_end);
        $daily_sale_count = count($todaySaleList);
        $dailySum = $todaySaleList->sum('gross');


    }

    public function getAlbarakaOrderid($orderId)
    {
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

    public function arrayToXML(array $data, $rootTag = '', $headerTag = true)
    {
        return Xml::fromArr($data, $rootTag, $headerTag);
    }

    private function ToXML(\SimpleXMLElement $object, array $data)
    {
        return Xml::recurse($object, $data);
    }

    private function stripInvalidXml($value)
    {
        $ret = "";
//        $current;
        if (empty($value))
        {
            return $ret;
        }

        $length = strlen($value);
        for ($i=0; $i < $length; $i++)
        {
            $current = ord($value.$i);
            if (($current == 0x9) ||
                ($current == 0xA) ||
                ($current == 0xD) ||
                (($current >= 0x20) && ($current <= 0xD7FF)) ||
                (($current >= 0xE000) && ($current <= 0xFFFD)) ||
                (($current >= 0x10000) && ($current <= 0x10FFFF)))
            {
                $ret .= chr($current);
            }
            else
            {
                $ret .= " ";
            }
        }
        return $ret;
    }

    public function isNonSecureConnection()
    {
        return GlobalFunction::isLocalEnvironment() || BrandConfiguration::isDisableSSLverify();
    }

    public function recurringValidation($input, $merchant_id,$merchantSettingsObj)
    {

        if(empty($merchantSettingsObj)){
            $merchantSettingsObj = (new MerchantSettings())->getMerchantSettingByMerchantId($merchant_id);
            $this->ccpayment->merchantSettingsObj = $merchantSettingsObj;
        }

        $is_allow_recurring_payment = $merchantSettingsObj->is_allow_recurring_payment;
        $status_code = '';
        $status_message = '';


        if (isset($input['order_type']) && $input['order_type'] == 1) {
            if ((bool)$is_allow_recurring_payment == false) {
                $status_code = 113;
                $status_message = 'This merchant is not allowed for recurring payment';
            }

            if (empty($status_code) && empty($merchantSettingsObj->save_card_bank_id)) {
                $status_code = 113;
                $status_message = 'Save Card Provider is invalid for recurring payment';
            }
            

            if (empty($status_code) && isset($input['installment']) && $input['installment'] > 1) {
                $status_code = 55;
                $status_message = "Recurring payment can not be installment sale.";

            }
//            elseif ($input['currency_code'] != 'TRY') {
//                $status_code = 55;
//                $status_message = "Recurring does not support for your currency";
//            }


            if (empty($status_code)) {

                if (!isset($input['recurring_payment_number']) || empty($input['recurring_payment_number'])) {
                    $status_code = 55;
                    $status_message = "Recurring number can not be empty";
                } elseif (!ctype_digit($input['recurring_payment_number']) && !is_int($input['recurring_payment_number'])) {
                    $status_code = 55;
                    $status_message = "Recurring number must be an integer";
                } elseif ($input['recurring_payment_number'] < 2) {
                    $status_code = 55;
                    $status_message = "Recurring number must be grater than 1";
                } elseif ($input['recurring_payment_number'] > 121) {
                    $status_code = 55;
                    $status_message = "Recurring number should not be grater than 121";
                }

            }

            if (empty($status_code)) {

                $recurringCycle = array_keys(config('constants.OCCURRENCE'));

                if (!isset($input['recurring_payment_cycle']) || empty($input['recurring_payment_cycle'])) {
                    $status_code = 55;
                    $status_message = "The recurring cycle can not be empty";
                } elseif (!in_array($input['recurring_payment_cycle'], $recurringCycle)) {
                    $status_code = 55;
                    $status_message = "The recurring cycle unit is not valid. It should be 'D', 'M' or  'Y'";
                }

            }

            if (empty($status_code)) {

                if (!isset($input['recurring_payment_interval']) || empty($input['recurring_payment_interval'])) {
                    $status_code = 55;
                    $status_message = "Recurring interval can not be empty";
                } elseif (!ctype_digit($input['recurring_payment_interval']) && !is_int($input['recurring_payment_interval'])) {
                    $status_code = 55;
                    $status_message = "Recurring interval must be an integer";
                } elseif ($input['recurring_payment_interval'] < 1) {
                    $status_code = 55;
                    $status_message = "Recurring interval must be grater than 0";
                } elseif ($input['recurring_payment_interval'] > 99) {
                    $status_code = 55;
                    $status_message = "Recurring interval should not be grater than 99";
                }

            }

            if (empty($status_code)) {

//                recurring_web_hook_key
                $merchantWebHookKey = new MerchantWebHookKeys();

                if (isset($input['recurring_web_hook_key']) && !empty($input['recurring_web_hook_key'])){

                    $merchantWebHookKeyObj = $merchantWebHookKey->findMerchantWebHookKeyByMerchant($merchant_id,
                        $input['recurring_web_hook_key'], MerchantWebHookKeys::RECURRING_WEB_HOOK);
                    if (empty($merchantWebHookKeyObj)){
                        $status_code = 55;
                        $status_message = "Invalid recurring web hook key! Please check key name on :varCompany";
                        $status_message .= $merchant_id.'=>'.$input['recurring_web_hook_key'].'=>'.MerchantWebHookKeys::RECURRING_WEB_HOOK;
                    }

                }else{

                    if (!isset($input['payment_from_merchant_panel']) || !$input['payment_from_merchant_panel']){
                        $status_code = 55;
                        $status_message = "Recurring web hook key can not be empty. Please assign your web hook key on :varCompany";
                    }



//                    $merchantWebHookKeyObj = $merchantWebHookKey->findMerchantWebHookKeyByMerchant($merchant_id,
//                        '', MerchantWebHookKeys::RECURRING_WEB_HOOK);
//                    if (empty($merchantWebHookKeyObj)){
//                        $status_code = 55;
//                        $status_message = "Recurring web hook key can not be empty. Please assign your web hook key on Sipay";
//                    }else{
//                        $input['recurring_web_hook_key'] = $merchantWebHookKeyObj->key_name;
//                    }
                }

            }

        }
        $localizeVariables = [
            'varCompany' => config('brand.name')
        ];
        $status_message = Language::isLocalize($status_message, $localizeVariables);
        return [$status_code, $status_message, $input];

    }

    public function generateResponseHashKey(
        $secret_key,
        $status,
        $total,
        $invoice_id,
        $order_id,
        $currency_code,
        $credit_card_no = ""
    ){
        $encryptParams = [
            $status,
            $total,
            $invoice_id,
            $order_id,
            $currency_code,
        ];

        $encryptParams = PaymentResponse::includeConditionalParams($encryptParams, [PaymentResponse::PARAM_KEY_HASH_CREDIT_CARD_NO => $credit_card_no]);

        return $this->customEncryptionDecryption($encryptParams, $secret_key, 'encrypt', 1);
    }

    public function preparePaymentResponseData($response_type,$order_id, $invoice_id, $payment_type,
                                               $status, $status_message, $error_code,
                                               $sale_type, $merchant_id, $product_price,
                                               $credit_card_no, $currency_code, $card_token = '',$md_status = -1, $auth_code = '',$is_pay_by_market_place = false){

        //$response_type = 0 //3d payment redirect response / branded solution
        //$response_type = 1 //sale Web hook
        //$response_type = 2 //2d payment response

        $merchant_commission_percentage = 0.00;
        $merchant_commission_fixed = 0.00;
        $installment = 1;
        $amount = 0.00;

        $transaction_type = 'Auth';
        if ($sale_type == 2){
            $transaction_type = config('constants.PRE_AUTHORIZATION');
        }


        if($status){
            $error_code = 100;
        }else{
            if(trim($status_message) == 'Success'){
                $status_message = 'Failed';
            }
        }

        if(is_array($status_message)){
           $status_message = GlobalFunction::safe_json_encode($status_message);
        }

        $masked_credit_card_no = SaleTransaction::CardNumberMaskingForResponse($credit_card_no);

        $params = [
            'sipay_status' => $status,
            'order_no' => trim($order_id),
            'order_id' => trim($order_id),
            'invoice_id' => trim($invoice_id),
            'status_code' => $error_code,
            'status_description' => $status_message,
            'sipay_payment_method' => $payment_type,
            'credit_card_no' => $masked_credit_card_no,
            'transaction_type' => $transaction_type,
            'payment_status' => $status,
            'payment_method' => $payment_type,
//            'merchant_commission' => isset($this->ccpayment->temporaryPaymentRecordObj->commission)?GlobalFunction::getDbFormattedAmount( (double)json_decode($this->ccpayment->temporaryPaymentRecordObj->commission)->merchant_fee,2)??null:null,
//            'user_commission' =>  isset($this->ccpayment->temporaryPaymentRecordObj->commission)?GlobalFunction::getDbFormattedAmount((double)json_decode($this->ccpayment->temporaryPaymentRecordObj->commission)->user_fee,2)??null:null,
            'error_code' => $error_code,
            'error' => $status_message,
            'auth_code' => $auth_code
        ];


        $params = PaymentResponse::params($params, $this->ccpayment, $is_pay_by_market_place, $response_type);


        if (!empty($card_token)){
            $params['card_token'] = $card_token;
        }

        $hash_key_status = $status;
        if ($response_type == 1){//
            if ($status == 1){
                $params['status'] = TransactionState::status(1);
            }else{
                $params['status'] = TransactionState::status(14);
            }

            $hash_key_status = $params['status'];
        }

        if (config('brand.name_code') != (config('constants.BRAND_NAME_CODE_LIST.SP') || config('constants.BRAND_NAME_CODE_LIST.PIN'))){
            unset($params['sipay_status']);
            unset($params['sipay_payment_method']);

        }

        if ($response_type == 2){// 2d payment
            unset($params['status_code']);
            unset($params['status_description']);
        }



        if (!empty($currency_code) && !empty($merchant_id) && !empty($product_price)){
            $merchantSetting = new MerchantSettings();
            if (empty($this->ccpayment->merchantSettingsObj)){
                $merchantSettingObj = $merchantSetting->getMerchantSettingByMerchantId($merchant_id);

            }else{
                $merchantSettingObj = $this->ccpayment->merchantSettingsObj;
            }


            if (empty($this->ccpayment->saleCurrencyConversionObj)){
                $sale_currency_conversion = new SaleCurrencyConversion();
                $saleCurrencyConversionObj = $sale_currency_conversion->findByOrderId($order_id);

            }else{
                $saleCurrencyConversionObj = $this->ccpayment->saleCurrencyConversionObj;
            }

            if (!empty($saleCurrencyConversionObj)){
                $params['hash_key'] = $this->generateResponseHashKey($merchantSettingObj->app_secret, $hash_key_status,
                    $saleCurrencyConversionObj->original_amount, $invoice_id, $order_id, $saleCurrencyConversionObj->from_currency,$masked_credit_card_no );
            }else{
                $params['hash_key'] = $this->generateResponseHashKey($merchantSettingObj->app_secret, $hash_key_status,
                    $product_price, $invoice_id, $order_id, $currency_code,  $masked_credit_card_no);
            }

        }


        //send payment_id only for manual pos
        if(isset($this->ccpayment->saleObj->payment_source) &&
            in_array($this->ccpayment->saleObj->payment_source,
                [
                  CCPayment::PAYMENT_SOURCE_MP_3D,
                  CCPayment::PAYMENT_SOURCE_MP_2D,
                  CCPayment::PAYMENT_SOURCE_BILL_PAYMENT_PAY_3D,
                  CCPayment::PAYMENT_SOURCE_BILL_PAYMENT_PAY_2D
                ])){
           $params['payment_id'] = $this->ccpayment->saleObj->payment_id;
        }

        if (isset($this->ccpayment->extras['is_new_api']) && $this->ccpayment->extras['is_new_api']) {
            if (isset($this->ccpayment->saleObj->payment_source) &&
                Arr::isAMemberOf($this->ccpayment->saleObj->payment_source,
                    [
                        CCPayment::PAYMENT_SOURCE_MP_3D,
                        CCPayment::PAYMENT_SOURCE_MP_2D
                    ])) {
                $params['is_manual_pos'] = true;
            } else {
                $params['is_manual_pos'] = false;
            }
        }
        if(in_array($response_type, [0,1]) && $md_status > MdStatus::$undefined_md_status){
            $params['md_status'] = $md_status;
        }
/*
        if(isset($this->ccpayment->saleObj->settlement_date_merchant)){
            $params['settlement_date'] = @Carbon::parse($this->ccpayment->saleObj->settlement_date_merchant)->toDateString();
        }
*/
        $params = $this->addOriginalBankErrorEntitiesToPaymentResponse($this->ccpayment ?? null, $params);

        return $params;


    }

    public function addOriginalBankErrorEntitiesToPaymentResponse($ccpayment, $params)
    {
        if($ccpayment instanceof CCPayment && !empty($ccpayment->saleObj) && !empty($ccpayment->saleObj->result)) {
            list($original_bank_error_code, $original_bank_error_description) = (new ProviderErrorCodeHandler())->formatResultForOriginalBankResponseEntities($ccpayment->saleObj->result);
            $params["original_bank_error_code"] = $original_bank_error_code;
            $params["original_bank_error_description"] = $original_bank_error_description;
        }

        return $params;
    }

    public function prepareRedirectUrl($url, $order_id, $invoice_id, $payment_type, $status,
                                       $status_message, $error_code = '00', $sale_type = '', $md_status = -1,
                                       $merchant_id = '', $currency_code = '', $product_price = 0,
    )
    {
/*        $merchant_id = '';
        $currency_code = '';
        $product_price = 0;*/
        $auth_code = '';


       $sessionData = GlobalFunction::getBrandSession('3d_data', $order_id);

       if (empty($sale_type)){
           $sale_type = 1;
           if($this->isPreAuthTransaction($sessionData)){
               $sale_type = 2;
           }
       }

       if(isset($this->ccpayment) && isset($this->ccpayment->extras)) {
           $auth_code = $this->ccpayment->extras['auth_code'] ?? '';
       }

       if($md_status == MdStatus::$undefined_md_status &&
           isset($this->ccpayment) &&
           !empty($this->ccpayment->saleObj) &&
           isset($this->ccpayment->saleObj->md_status)
       ){
           $md_status = $this->ccpayment->saleObj->md_status;

       }


        $credit_card_no = $sessionData['credit_card_no'] ?? '';
        if (!empty($credit_card_no)){
            $credit_card_no = $this->customEncryptionDecryption($credit_card_no, \config('app.brand_secret_key'), 'decrypt');

        }


        if (!empty($sessionData)){
            $merchant_id = $sessionData['merchant_id'] ?? '';
            $currency_code = $sessionData['currency_code'] ?? '';
            $product_price = $sessionData['total'] ?? 0;
        }
        $purchaseRequestObj = GlobalFunction::getBrandSession('PurchaseRequest', $order_id);

        if (!empty($purchaseRequestObj) && (empty($currency_code) || empty($product_price))){
            $currency_code = $purchaseRequestObj->currency_code;
            $product_price = $purchaseRequestObj->data->total;

        }


        if (empty($purchaseRequestObj) && (empty($currency_code) || empty($product_price))){
            $purchaseRequest = new PurchaseRequest();
            $purchaseRequestObj = $purchaseRequest->getPurchaseRequest($order_id);
            if (!empty($purchaseRequestObj)){
                $currency_code = $purchaseRequestObj->currency_code;
                $product_price = $purchaseRequestObj->data->total;
            }
        }

        if (empty($merchant_id)){
            $sale = new Sale();
            $saleObj = $sale->getSaleByOrderId($order_id);
            if (!empty($saleObj)){
                $merchant_id = $saleObj->merchant_id;
            }
        }


       if (isset($purchaseRequestObj->lang) && !empty($purchaseRequestObj->lang) && in_array($purchaseRequestObj->lang, config('constants.SYSTEM_SUPPORTED_LANGUAGE'))) {
           //as the brand session is set before choosing the language by user, so we should use current lang
           $brandSessionLang = $purchaseRequestObj->lang;
           $onePagePayment =  !empty($purchaseRequestObj?->data?->is_one_page_payment_dpl);
           $currentLang = app()->getLocale();
           if($brandSessionLang != $currentLang && !$onePagePayment){
               $brandSessionLang = $currentLang;
           }
           app()->setLocale($brandSessionLang);
       } else {
          app()->setLocale(config('app.locale'));
       }


        $params = $this->preparePaymentResponseData(0, $order_id, $invoice_id, $payment_type, $status,
            $status_message, $error_code, $sale_type, $merchant_id,
            $product_price, $credit_card_no, $currency_code, '' ,$md_status, $auth_code,$sessionData['is_pay_by_marketplace']??0);


        if(isset($sessionData['is_wix']) && $sessionData['is_wix'] == 1) {
            $wix = new Wix(false, true);
            $wix_prepared_data = $wix->preparationForWixPaymentRedirect($status, $error_code, $status_message ?? $params['status_description'], $sessionData, $purchaseRequestObj, $params,$this->is_transaction_cancelled_by_end_user);
            $wix_url = $wix_prepared_data['wix_url']??null;

            if(!empty($wix_url)){
                $url = $wix_url;
            }
        }

        if(isset($sessionData['is_fastpay_wallet_payment']) && $sessionData['is_fastpay_wallet_payment'] == 1) {
            list($url, $params) = (new FastpayPayment())->prepare3DSecureRedirectResponseData($url, $params,$purchaseRequestObj,$this->ccpayment->fp_wallet_approve_status);

        }

        if ($this->ccpayment instanceof CCPayment){
            $this->ccpayment->redirect_url = $url;
            $this->ccpayment->redirect_response_params = $params;
        }
        $prepared_url = $this->parseAndPrepareUrl($url, $params);
        self::setProcessPaymentCacheData($order_id, $status, $prepared_url);

        $this->createLog([
            "action" => "REDIRECTED_TO_MERCHANT",
            "url" => $prepared_url
        ]);

        return $prepared_url;
    }

    public function setProcessPaymentCacheData($order_id, $payment_status, $prepared_url=null)
    {
        if($payment_status == 1){
            $pp_cache_status = CCPayment::PP_CACHE_STATE_COMPLETED;
        }else if($payment_status == 0){
            $pp_cache_status = CCPayment::PP_CACHE_STATE_FAILED;
        }else{
            $pp_cache_status = CCPayment::PP_CACHE_STATE_PROCESSING;
        }
        $cache_data =
            [
                'pp_cache_status' => $pp_cache_status,
                'order_id' => $order_id,
                'url' => $prepared_url
            ];
        GlobalFunction::setBrandCache('pp_'.$order_id, $cache_data, CCPayment::PP_CACHE_EXPIRY_TIME_IN_SECONDS);
    }

    private function redirectFromProcessPaymentCache($order_id, $request)
    {
        //if cache data with allowed cache states is found this method will not return to calling method and redirect
        $action = "PROCESS_PAYMENT_CACHE_REDIRECT";
        $cache_data = GlobalFunction::getBrandCache('pp_'.$order_id);


        if(!empty($cache_data)) {
            $this->createLog($this->_getCommonLogData(['action' => $action, 'cache_data' => $cache_data]));

            if (isset($cache_data['pp_cache_status']) && $cache_data['pp_cache_status']==CCPayment::PP_CACHE_STATE_COMPLETED){
                //$this->createLog($this->_getCommonLogData(['action' => $action, 'cache_data' => $cache_data]));

                if (filter_var($cache_data['url'], FILTER_VALIDATE_URL)) {
                    (new ManageLogging())->createBankResponseLog($action.'_BANK_LANDING_RESPONSE',$request);
                    header('Location: '.$cache_data['url']);
                    exit;
                }else{
                    (new ManageLogging())->createBankResponseLog($action.'_BANK_LANDING_RESPONSE',$request);
                    abort(404, 'Request is already succeeded.');
                }

            }else if((isset($cache_data['pp_cache_status']) && $cache_data['pp_cache_status'] == CCPayment::PP_CACHE_STATE_PROCESSING)){
                (new ManageLogging())->createBankResponseLog($action.'_BANK_LANDING_RESPONSE',$request);
                abort(404, 'Request is being processed');
            }
        }else{
            self::setProcessPaymentCacheData($order_id, 3);//for processing status cache
        }

    }

    public function parseAndPrepareUrl($url, $params){
        $res = http_build_query($params);
        $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . $res;
        return $url;
    }
    public function GetTurkposOrderId($order_id){
        return 'isem'.$order_id;
    }

    public function RemoveTurkposOrderIdString($order_id){
        return str_replace('isem', '', $order_id);
    }

    public function sessionExpiredLog($requestData){

        $logData["action"] = "3D Payment Failed due to Session Expired";

        if (isset($requestData['Ecom_Payment_Card_ExpDate_Year'])){
            unset($requestData['Ecom_Payment_Card_ExpDate_Year']);
        }
        if (isset($requestData['Ecom_Payment_Card_ExpDate_Month'])){
            unset($requestData['Ecom_Payment_Card_ExpDate_Month']);
        }


        $logData["bank_response"] = $requestData;

        $this->createLog($this->_getCommonLogData($logData));
    }

    public function getPayallToken($tokenURL, $client_id, $client_secret){

        $tokenBodyData = [
            'grant_type' => "client_credentials",
            'client_id' => $client_id,
            'client_secret' => $client_secret,
        ];
        $_SESSION['token_request'] = $tokenBodyData;

        $tokenBodyData = http_build_query($tokenBodyData);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $tokenURL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POSTFIELDS => $tokenBodyData,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded",
                "cache-control: no-cache"
            ),
        ));
        $response = curl_exec($curl);
//    $err = curl_error($curl);
        curl_close($curl);

        $response = json_decode($response, true);

        $token = $response['access_token'] ?? '';

        return $token;
    }


    public function capitalizeTextInput ($str) {

        $str = str_replace('i', 'İ', $str);
        $str = str_replace('ı', 'I', $str);

        return mb_strtoupper($str, 'UTF-8');
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


    private function creditCardEncrypt($data, $password)
    {
        if (empty($data)) {
            return $data;
        }
        $iv = 'cJ>_uWL@_fv/2ad9'; //16 digits
        $password = sha1($password);

        $salt = 'tE@_'; //4 digits
        $saltWithPassword = hash('sha256', $password . $salt);

        $encrypted = openssl_encrypt(
            "$data", 'aes-256-cbc', "$saltWithPassword", 0, $iv
        );
        $msg_encrypted_bundle = "$iv:$salt:$encrypted";
        return $msg_encrypted_bundle;
    }

    private function creditCardDecrypt($msg_encrypted_bundle, $password)
    {
        if (empty($msg_encrypted_bundle)) {
            return $msg_encrypted_bundle;
        }
        $password = sha1($password);

        $components = explode(':', $msg_encrypted_bundle);
        if (count($components) < 3) {
            return $msg_encrypted_bundle;
        }


        $iv = $components[0] ?? '';
        $salt = $components[1] ?? '';
        $salt = hash('sha256', $password . $salt);
        $encrypted_msg = $components[2] ?? '';

        $decrypted_msg = openssl_decrypt(
            $encrypted_msg, 'aes-256-cbc', $salt, null, $iv
        );

        if ($decrypted_msg === false) {
            return $msg_encrypted_bundle;
        }

//        $msg = substr( $decrypted_msg, 41 );
        return $decrypted_msg;
    }

    public function deadLockMonitoringLog($area, $order_id = null, $payment_id = null){
        $logData = [
            'action' => 'DEADLOCK_STILL_EXIST',
            'area' => $area,
            'order_id' => $order_id
        ];
        if (!empty($order_id)){
            $logData['order_id'] = $order_id;
        }

        if (!empty($payment_id)){
            $logData['payment_id'] = $payment_id;
        }

        $this->createLog($this->_getCommonLogData($logData));
    }

    public function unsetKeysVakifBank($requestData)
    {
       try{
            if(is_array($requestData)) {
                $requestData = $this->unsetKeys($requestData);
                return $requestData;
            }else {
                $xmlString = substr($requestData, ($pos = strpos($requestData, '<')) !== false ? $pos : 0);
                $xml = simplexml_load_string($xmlString, "SimpleXMLElement", LIBXML_NOCDATA);
                $json = json_encode($xml);
                $array = json_decode($json, TRUE);
                $array = $this->unsetKeys($array);
                $xml = $this->arrayToXML($array, '<VposRequest/>', false);
                //array_walk_recursive($array, array ($xml, 'addChild'));
                $string = 'prmstr=' . $xml;
                return $string;
            }
        }catch (\Throwable $e){
            return $requestData;
        }

    }

    public function hideKeysYapiKredi($data,$hide_type = null){
        try {
            if ($hide_type == 'log_hide_tid_oosRequestData') {
                if (isset($data['oosRequestData']) && !empty($data['oosRequestData'])) {
                    if (isset($data['tid'])) {
                        $data['tid'] = '***';
                    }
                    $keys = ['ccno', 'expDate', 'cvc'];
                    foreach ($keys as $key) {
                        if (isset($data['oosRequestData'][$key])) {
                            $data['oosRequestData'][$key] = '***';
                        }
                    }
                    return $data;
                }
            }
        }catch (\Throwable $e){
            return $data;
        }
    }

    public function hideKeyValues($data, $values){
        return InformationMasking::hideValues($data, $values);
    }




    public function uniqueStringGeneration ($data, $type = Cashout::UNIQUE_STR_TYPE_SEND)
    {
        $array = $result = [];
        if ($type == Cashout::UNIQUE_STR_TYPE_DEPOSIT) {
            $array = ['gsm_number', 'deposit_source', 'name', 'gross', 'currency_id', 'deposit_method_id',  'created_at'];
        } elseif ($type == Cashout::UNIQUE_STR_TYPE_CASHOUT) {
            $array = ['merchant_id', 'iban', 'name', 'user_name', 'gsm_number', 'amount', 'currency', 'cashout_type', 'source', 'created_at'];
        } elseif ($type == Cashout::UNIQUE_STR_TYPE_SEND) {
            $array = ['user_id', 'user_name', 'user_gsm_number', 'to_id', 'to_name', 'to_gsm_number', 'gross', 'currency', 'send_type', 'created_at'];
        } elseif ($type == Cashout::UNIQUE_STR_TYPE_WITHDRAW) {
            $array = ['user_id', 'name', 'iban', 'bank_static_id', 'account_holder_name', 'gsm_number', 'gross', 'currency_id', 'user_type', 'destination_type', 'created_at'];
        }

        if (!empty($array)) {
            foreach ($array as $arr) {
                if (array_key_exists($arr, $data)) {
                    $result[] = $data[$arr] ?? '-';
                } else {
                    $result = [];
                    break;
                }
            }
        }

        return !empty($result) ? implode('|', $result) : '';
    }

    public function uniqueIdGeneration ($merchant_id, $user_id, $type, $unique_id = '')
    {
        if (BrandConfiguration::isUpcomingWithdrawalUniqueId() && $type == Cashout::UNIQUE_ID_TYPE[4]) {
            $result = (new ImportedTransaction())->generateOrderId(ImportedTransaction::TYPE_WD);
            $result = strlen($result) > 21 ? substr($result, 0, 21) : $result;
        } else {
            $unique_id = $unique_id ? $unique_id : time() . rand(0, 9999);
            $result = in_array($type, Cashout::UNIQUE_ID_TYPE)
                ? $merchant_id . '-' . $user_id . '-' . $type . '-' . $unique_id
                : '';
        }
        return $result;
    }
}