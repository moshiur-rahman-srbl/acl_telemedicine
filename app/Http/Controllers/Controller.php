<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CommonLogTrait;
use App\Http\Controllers\Traits\ExportExcelTrait;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Controllers\Traits\OTPTrait;
use App\Http\Controllers\Traits\SendEmailTrait;
use App\Http\Controllers\Traits\NotificationTrait;
use App\Models\Country;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Facades\Validator;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, SendEmailTrait, OTPTrait, FileUploadTrait, CommonLogTrait, ExportExcelTrait, NotificationTrait;

    public function countries()
    {
        $country_array = Country::pluck('country_name', 'id')->toArray();
//        dd($country_array);
//        $country_array = array( "1"=>"Afghanistan","2"=>"Aland Islands","3"=>"Albania","4"=>"Algeria","5"=>"American Samoa","6"=>"Andorra","7"=>"Angola","8"=>"Anguilla","9"=>"Antarctica","10"=>"Antigua","11"=>"Argentina","12"=>"Armenia","13"=>"Aruba","14"=>"Australia","15"=>"Austria","16"=>"Azerbaijan","17"=>"Bahamas","18"=>"Bahrain","19"=>"Bangladesh","20"=>"Barbados","21"=>"Barbuda","22"=>"Belarus","23"=>"Belgium","24"=>"Belize","25"=>"Benin","26"=>"Bermuda","27"=>"Bhutan","28"=>"Bolivia","29"=>"Bosnia","30"=>"Botswana","31"=>"Bouvet Island","32"=>"Brazil","33"=>"British Indian Ocean Trty.","34"=>"Brunei Darussalam","35"=>"Bulgaria","36"=>"Burkina Faso","37"=>"Burundi","38"=>"Caicos Islands","39"=>"Cambodia","40"=>"Cameroon","41"=>"Canada","42"=>"Cape Verde","43"=>"Cayman Islands","44"=>"Central African Republic","45"=>"Chad","46"=>"Chile","47"=>"China","48"=>"Christmas Island","49"=>"Cocos (Keeling) Islands","50"=>"Colombia","51"=>"Comoros","52"=>"Congo","53"=>"Congo, Democratic Republic of the","54"=>"Cook Islands","55"=>"Costa Rica","56"=>"Cote d'Ivoire","57"=>"Croatia","58"=>"Cuba","59"=>"Cyprus","60"=>"Czech Republic","61"=>"Denmark","62"=>"Djibouti","63"=>"Dominica","64"=>"Dominican Republic","65"=>"Ecuador","66"=>"Egypt","67"=>"El Salvador","68"=>"Equatorial Guinea","69"=>"Eritrea","70"=>"Estonia","71"=>"Ethiopia","72"=>"Falkland Islands (Malvinas)","73"=>"Faroe Islands","74"=>"Fiji","75"=>"Finland","76"=>"France","77"=>"French Guiana","78"=>"French Polynesia","79"=>"French Southern Territories","80"=>"Futuna Islands","81"=>"Gabon","82"=>"Gambia","83"=>"Georgia","84"=>"Germany","85"=>"Ghana","86"=>"Gibraltar","87"=>"Greece","88"=>"Greenland","89"=>"Grenada","90"=>"Guadeloupe","91"=>"Guam","92"=>"Guatemala","93"=>"Guernsey","94"=>"Guinea","95"=>"Guinea-Bissau","96"=>"Guyana","97"=>"Haiti","98"=>"Heard","99"=>"Herzegovina","100"=>"Holy See","101"=>"Honduras","102"=>"Hong Kong","103"=>"Hungary","104"=>"Iceland","105"=>"India","106"=>"Indonesia","107"=>"Iran (Islamic Republic of)","108"=>"Iraq","109"=>"Ireland","110"=>"Isle of Man","111"=>"Israel","112"=>"Italy","113"=>"Jamaica","114"=>"Jan Mayen Islands","115"=>"Japan","116"=>"Jersey","117"=>"Jordan","118"=>"Kazakhstan","119"=>"Kenya","120"=>"Kiribati","121"=>"Korea","122"=>"Korea (Democratic)","123"=>"Kuwait","124"=>"Kyrgyzstan","125"=>"Lao","126"=>"Latvia","127"=>"Lebanon","128"=>"Lesotho","129"=>"Liberia","130"=>"Libyan Arab Jamahiriya","131"=>"Liechtenstein","132"=>"Lithuania","133"=>"Luxembourg","134"=>"Macao","135"=>"Macedonia","136"=>"Madagascar","137"=>"Malawi","138"=>"Malaysia","139"=>"Maldives","140"=>"Mali","141"=>"Malta","142"=>"Marshall Islands","143"=>"Martinique","144"=>"Mauritania","145"=>"Mauritius","146"=>"Mayotte","147"=>"McDonald Islands","148"=>"Mexico","149"=>"Micronesia","150"=>"Miquelon","151"=>"Moldova","152"=>"Monaco","153"=>"Mongolia","154"=>"Montenegro","155"=>"Montserrat","156"=>"Morocco","157"=>"Mozambique","158"=>"Myanmar","159"=>"Namibia","160"=>"Nauru","161"=>"Nepal","162"=>"Netherlands","163"=>"Netherlands Antilles","164"=>"Nevis","165"=>"New Caledonia","166"=>"New Zealand","167"=>"Nicaragua","168"=>"Niger","169"=>"Nigeria","170"=>"Niue","171"=>"Norfolk Island","172"=>"Northern Mariana Islands","173"=>"Norway","174"=>"Oman","175"=>"Pakistan","176"=>"Palau","177"=>"Palestinian Territory, Occupied","178"=>"Panama","179"=>"Papua New Guinea","180"=>"Paraguay","181"=>"Peru","182"=>"Philippines","183"=>"Pitcairn","184"=>"Poland","185"=>"Portugal","186"=>"Principe","187"=>"Puerto Rico","188"=>"Qatar","189"=>"Reunion","190"=>"Romania","191"=>"Russian Federation","192"=>"Rwanda","193"=>"Saint Barthelemy","194"=>"Saint Helena","195"=>"Saint Kitts","196"=>"Saint Lucia","197"=>"Saint Martin (French part)","198"=>"Saint Pierre","199"=>"Saint Vincent","200"=>"Samoa","201"=>"San Marino","202"=>"Sao Tome","203"=>"Saudi Arabia","204"=>"Senegal","205"=>"Serbia","206"=>"Seychelles","207"=>"Sierra Leone","208"=>"Singapore","209"=>"Slovakia","210"=>"Slovenia","211"=>"Solomon Islands","212"=>"Somalia","213"=>"South Africa","214"=>"South Georgia","215"=>"South Sandwich Islands","216"=>"Spain","217"=>"Sri Lanka","218"=>"Sudan","219"=>"Suriname","220"=>"Svalbard","221"=>"Swaziland","222"=>"Sweden","223"=>"Switzerland","224"=>"Syrian Arab Republic","225"=>"Taiwan","226"=>"Tajikistan","227"=>"Tanzania","228"=>"Thailand","229"=>"The Grenadines","230"=>"Timor-Leste","231"=>"Tobago","232"=>"Togo","233"=>"Tokelau","234"=>"Tonga","235"=>"Trinidad","236"=>"Tunisia","237"=>"Turkey","238"=>"Turkmenistan","239"=>"Turks Islands","240"=>"Tuvalu","241"=>"Uganda","242"=>"Ukraine","243"=>"United Arab Emirates","244"=>"United Kingdom","245"=>"United States","246"=>"Uruguay","247"=>"US Minor Outlying Islands","248"=>"Uzbekistan","249"=>"Vanuatu","250"=>"Vatican City State","251"=>"Venezuela","252"=>"Vietnam","253"=>"Virgin Islands (British)","254"=>"Virgin Islands (US)","255"=>"Wallis","256"=>"Western Sahara","257"=>"Yemen","258"=>"Zambia","259"=>"Zimbabwe");

        return $country_array;
    }

    public function getLang($user)
    {
        $default = 'tr';

        if (!empty($user) && !empty($user->language)) {
            $default = $user->language;
        }

        return $default;
    }

    public function sendResponse($result, $message = '', $statusCode = null)
    {
        $response = [
            'stausCode' => $statusCode,
            'status' => 'success',
            'success' => true,
            'data' => $result,
            'message' => $message,
        ];
        return response()->json($response, 200);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'stausCode' => $code,
            'status' => 'error',
            'success' => false,
            'message' => $error,
        ];
        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }
        return response()->json($response, 200);
    }

    public function ajax_validation_process($inputs, $rules)
    {
        $validator = Validator::make($inputs, $rules);
        if ($validator->fails()) {
            return $this->sendError(__('Validation error'), $validator->errors(), 400);
        }
        return true;
    }

//    public function _getCommonLogData($logData = array())
//    {
//        $logCommonData = [
//            'date_time' => Carbon::now(),
//            'auth_user_ip'=> $this->getClientIp(),
//            'auth_user_agent'=> $this->getUserAgent()
//        ];
//        if(Auth::user()){
//            $logCommonData['auth_default_currency'] = Auth::user()->currentCurrency()->name;
//            $logCommonData['auth_email'] = Auth::user()->email;
//            $logCommonData['auth_id'] = Auth::user()->id;
//        }
//        return array_merge($logData, $logCommonData);
//    }
//
//    private function getClientIp()
//    {
//        $ipaddress = '';
//        if (isset($_SERVER['HTTP_CLIENT_IP']))
//            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
//        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
//            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
//        else if(isset($_SERVER['HTTP_X_FORWARDED']))
//            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
//        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
//            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
//        else if(isset($_SERVER['HTTP_FORWARDED']))
//            $ipaddress = $_SERVER['HTTP_FORWARDED'];
//        else if(isset($_SERVER['REMOTE_ADDR']))
//            $ipaddress = $_SERVER['REMOTE_ADDR'];
//        else
//            $ipaddress = 'UNKNOWN';
//        return $ipaddress;
//    }
//
//    private function getUserAgent()
//    {
//        $maxlength = 80;
//        $userAgent = $_SERVER['HTTP_USER_AGENT'];
//        if(strlen($userAgent) < 80){
//            $maxlength = strlen($userAgent);
//        }
//
//        return substr($userAgent, 0, $maxlength);;
//    }

}
