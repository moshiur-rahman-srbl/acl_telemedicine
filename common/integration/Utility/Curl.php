<?php

namespace common\integration\Utility;

use common\integration\BrandConfiguration;
use common\integration\ManageLogging;
use common\integration\Utility\Sftp\HttpCode;

class Curl
{

    const CONNECT_TIMEOUT = 90;
    const READ_TIMEOUT = 150;
    public static $http_code;
    public static $options = [];
    public static $action = "";
    public static $error_no;
    public static $error;
    private static $attempt;



    public static function get($url, array $headers, $content = null)
    {
        return self::request($url, array(
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => self::READ_TIMEOUT,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $content ? $content : '',
        ));
    }

    public static function post($url, array $headers, $content)
    {
        return self::request($url, array(
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => self::READ_TIMEOUT,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $content ? $content : '',
        ));
    }

    public static function put($url, array $headers, $content)
    {
        return self::request($url, array(
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => self::READ_TIMEOUT,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $content ? $content : '',
        ));
    }

    public static function delete($url, array $headers)
    {
        return self::request($url, array(
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => self::READ_TIMEOUT,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
        ));
    }

    private static function request($url, $options)
    {
        $options =  self::withOptions() + $options;
        
        $request = curl_init($url);

        curl_setopt_array($request, $options);

        $response = self::exec($request);

        (new ManageLogging())->createCurlErrorLog(!empty(self::$action) ? self::$action :"GENERAL", $url,$request,$options[CURLOPT_POSTFIELDS]?? [],$response, $options[CURLOPT_HTTPHEADER] ?? []);

        self::destruct(true);

        curl_close($request);

        unset($request);

        return $response;
    }

    private static function exec($request)
    {
        self::$attempt = self::$attempt - 1;

        $response = curl_exec($request);

        self::errors($request);


        if(self::$attempt > 0 && self::isTransportError(true)){
            return self::exec($request);
        }


        self::$attempt = 0;


        return $response;

    }

    private static function errors($request)
    {
        self::$http_code = curl_getinfo($request, CURLINFO_HTTP_CODE);
        self::$error_no = curl_errno($request);
        self::$error = curl_error($request);
    }

    public static function httpCode()
    {
        return self::$http_code;
    }


    public static function error()
    {
        return self::$error;
    }


    public static function errorNo()
    {
        return self::$error_no;
    }

    public static function isTransportError($log = false)
    {
        $is_transport_error = false;

        if(!empty(self::errorNo()) || HttpCode::isError(self::httpCode())){
            $is_transport_error = true;
        }
        if($is_transport_error && $log){
            (new ManageLogging())->createLog([
               "action" => "CURL_TRANSPORT_ERROR",
               "error_no" => self::errorNo(),
               "error" => self::error(),
               "http_code" => self::httpCode()
            ]);
        }

        return $is_transport_error;
    }



    public static function withOptions($options = [])
    {
        if(!empty($options)){
            self::$options = self::$options + $options;
        }

        if(Ip::isLocal() || BrandConfiguration::isDisableSSLverify()) {

            (new ManageLogging())->createLog(["action"=> "SSL_VERIFICATION", "is_verification_off" => true]);

            self::$options = self::$options + array(
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_SSL_VERIFYPEER => 0
                );
        }

        if(!empty($options)){
            return new self();
        }else {
            return self::$options;
        }

    }

    public static function tries($attempt)
    {
        self::$attempt = $attempt;

        return new static();
    }


    public static function withAction($action, $log_request = false)
    {
        self::$action = $action;
        if($log_request){
            ManageLogging::shouldLog(true);
        }else{
            ManageLogging::shouldLog(false);
        }
        return new self();
    }

    public static function withTimeout($timeout)
    {
        self::$options[CURLOPT_CONNECTTIMEOUT] = $timeout;

        return new static();

    }

    public static function asJson()
    {
        $headers =  Arr::merge([
            'content-type: application/json'
        ], (self::$options[CURLOPT_HTTPHEADER ] ?? []));
        self::$options[CURLOPT_HTTPHEADER ] = $headers;

        return new static();

    }

    public static function acceptJson()
    {
        $headers = self::$options[CURLOPT_HTTPHEADER ] ?? [];
        $headers =  Arr::merge([
                'accept: application/json'
            ],$headers);
        self::$options[CURLOPT_HTTPHEADER] = $headers;

        return new static();
    }


    public static function withHeaders($headers)
    {
        $headers =  Arr::merge((self::$options[CURLOPT_HTTPHEADER ] ?? []), $headers);
        self::$options[CURLOPT_HTTPHEADER] = $headers;
        return new static();
    }


    public static function destruct($clear_options = false)
    {
        Curl::withAction("");
        ManageLogging::shouldLog(false);
        if($clear_options){
            Curl::$options = [];
        }
    }


    public static function patch($url, array $headers, $content)
    {
        return self::request($url, array(
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => self::READ_TIMEOUT,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $content ? $content : '',
        ));
    }





}
