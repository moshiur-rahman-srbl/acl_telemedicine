<?php

namespace common\integration\Utility;

class Json
{

    public static function encode($value, int $flags=0, int $depth=512)
    {
        return json_encode($value, $flags, $depth);
    }

    public static function jsonArrtoArr($json_string_array){
        $array_data = [];
        foreach($json_string_array as $json_string) {
            $array_data[] = json_decode($json_string, true);
        }
        return $array_data;
    }

    public static function safeEncode($value, int $flags=0, int $depth=512, bool $utf8_error_flag = false)
    {
        $encoded = self::encode($value, $flags, $depth);
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $encoded;
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            case JSON_ERROR_UTF8:
                $value = Encode::toUtf8($value);
                if ($utf8_error_flag) {
                    return 'UTF8 encoding error';
                }
                return self::safeEncode($value, $flags, $depth, true);
            default:
                return 'Unknown error';
        }
    }

    public static function decode(?string $json, ?bool $associative = null, int $depth = 512, int $flags = 0)
    {
        return json_decode($json,$associative,$depth,$flags);
    }

    public static function isValid($data){
        list($status) = self::validate($data);
        return $status;
    }

    public static function validate($string) : array {
        $status = false;
        $message = '';

        try{
            json_decode($string);
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    $status = true;
                    break;
                case JSON_ERROR_DEPTH:
                    $status = false;
                    $message = 'Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $status = false;
                    $message = 'Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $status = false;
                    $message = 'Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $status = false;
                    $message = 'Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $status = false;
                    $message = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $status = false;
                    $message = 'Unknown error';
                    break;
            }

        }catch ( \Throwable $exception){
            $status = false;
            $message = $exception->getMessage();
        }

        return [$status, $message];
    }

}