<?php


namespace common\integration\Traits;


use common\integration\ManageLogging;
use common\integration\Utility\Helper;
use common\integration\Utility\Ip;

trait HttpServiceInfoTrait
{
    public function getClientIpAddress(){
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';

        return $this->extractIpAddress($ipaddress);
    }

    public function getServerIpAddress()
    {
        $ip = '';
        $addr = '';
        $log["action"] = "GET_SERVER_IP_ADDRESS";
        $log["global_server_array"] = $_SERVER ?? [];

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && Helper::isNginxServerEnvironment()) {
            $addr = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_REFERER'])) {
            $addr = $_SERVER['HTTP_REFERER'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])){
            $addr = $_SERVER['REMOTE_ADDR'];
        } else {
            $addr = url()->previous();

            $log["is_from_previous_url"] = true;
        }

        if (strlen($addr) > 2) {
            $subAddr = substr($addr, 0, 3);
            if ($subAddr == 'htt' || $subAddr == 'www') {
                $host = parse_url($addr, PHP_URL_HOST);
                $log["host"] = $host;
                $currentServerHost = parse_url(config('app.url'), PHP_URL_HOST);
                $log["current_server_host"] = $currentServerHost;
                if ($host != $currentServerHost){
                    $ip = gethostbyname($host);
                    $log["is_ip_from_host_by_name"] = true;
                }

            } else {
                $ip = $addr;
                $log["is_ip_from_direct_addr"] = true;
            }
        }
        $log["addr"] = $addr;
        $log["server_ip"] = $ip;
        (new ManageLogging())->createTestLog($log,['sp_nginx', 'sp_prov', 'sp_dev']);
//        $this->fileWrite("otp.txt", $ip.$addr, config('app.IS_OTP_FILE_WRITE_ENABLE'));
        return $this->extractIpAddress($ip);
    }


    public function getUserAgentInfo($is_custom_user_agent = false){
        $maxlength = 80;
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';

        if(empty($userAgent) && $is_custom_user_agent){
            $userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.77 Safari/537.36";
        }

        if (strlen($userAgent) < 80) {
            $maxlength = strlen($userAgent);
        }

        return substr($userAgent, 0, $maxlength);
    }

    public function extractIpAddress($inputString) {


        // Check if a comma exists in the string
        if (strpos($inputString, ',') !== false) {
            // Split the string by commas
            $ipAddresses = explode(',', $inputString);

            // Iterate through the extracted addresses
            foreach ($ipAddresses as $ipAddress) {
                // Trim any leading or trailing whitespace
                $ipAddress = trim($ipAddress);

                // Validate if the trimmed string is a valid IP address
                if (filter_var($ipAddress, FILTER_VALIDATE_IP)) {
                    // Return the first valid IP address found
                    $inputString =  $ipAddress;
                    if (Ip::isIPv4($inputString)){
                        break;
                    }

                }
            }
        }

        if (!empty($inputString) && strlen($inputString) > 45){
            $inputString = substr($inputString, 0,45);
        }

        // If no valid IP address is found, return null or any default value as needed
        return $inputString;
    }

    public function getServerPort(): string
    {
        return request()->getPort() ?? '';
    }

}