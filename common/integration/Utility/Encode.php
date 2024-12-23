<?php

namespace common\integration\Utility;

class Encode
{
    const ICONV_TRANSLIT = "TRANSLIT";
    const ICONV_IGNORE = "IGNORE";
    const WITHOUT_ICONV = "";

    protected static $win1252ToUtf8 = array(
        128 => "\xe2\x82\xac",

        130 => "\xe2\x80\x9a",
        131 => "\xc6\x92",
        132 => "\xe2\x80\x9e",
        133 => "\xe2\x80\xa6",
        134 => "\xe2\x80\xa0",
        135 => "\xe2\x80\xa1",
        136 => "\xcb\x86",
        137 => "\xe2\x80\xb0",
        138 => "\xc5\xa0",
        139 => "\xe2\x80\xb9",
        140 => "\xc5\x92",

        142 => "\xc5\xbd",


        145 => "\xe2\x80\x98",
        146 => "\xe2\x80\x99",
        147 => "\xe2\x80\x9c",
        148 => "\xe2\x80\x9d",
        149 => "\xe2\x80\xa2",
        150 => "\xe2\x80\x93",
        151 => "\xe2\x80\x94",
        152 => "\xcb\x9c",
        153 => "\xe2\x84\xa2",
        154 => "\xc5\xa1",
        155 => "\xe2\x80\xba",
        156 => "\xc5\x93",

        158 => "\xc5\xbe",
        159 => "\xc5\xb8"
    );

    protected static $brokenUtf8ToUtf8 = array(
        "\xc2\x80" => "\xe2\x82\xac",

        "\xc2\x82" => "\xe2\x80\x9a",
        "\xc2\x83" => "\xc6\x92",
        "\xc2\x84" => "\xe2\x80\x9e",
        "\xc2\x85" => "\xe2\x80\xa6",
        "\xc2\x86" => "\xe2\x80\xa0",
        "\xc2\x87" => "\xe2\x80\xa1",
        "\xc2\x88" => "\xcb\x86",
        "\xc2\x89" => "\xe2\x80\xb0",
        "\xc2\x8a" => "\xc5\xa0",
        "\xc2\x8b" => "\xe2\x80\xb9",
        "\xc2\x8c" => "\xc5\x92",

        "\xc2\x8e" => "\xc5\xbd",


        "\xc2\x91" => "\xe2\x80\x98",
        "\xc2\x92" => "\xe2\x80\x99",
        "\xc2\x93" => "\xe2\x80\x9c",
        "\xc2\x94" => "\xe2\x80\x9d",
        "\xc2\x95" => "\xe2\x80\xa2",
        "\xc2\x96" => "\xe2\x80\x93",
        "\xc2\x97" => "\xe2\x80\x94",
        "\xc2\x98" => "\xcb\x9c",
        "\xc2\x99" => "\xe2\x84\xa2",
        "\xc2\x9a" => "\xc5\xa1",
        "\xc2\x9b" => "\xe2\x80\xba",
        "\xc2\x9c" => "\xc5\x93",

        "\xc2\x9e" => "\xc5\xbe",
        "\xc2\x9f" => "\xc5\xb8"
    );

    protected static $utf8ToWin1252 = array(
        "\xe2\x82\xac" => "\x80",

        "\xe2\x80\x9a" => "\x82",
        "\xc6\x92"     => "\x83",
        "\xe2\x80\x9e" => "\x84",
        "\xe2\x80\xa6" => "\x85",
        "\xe2\x80\xa0" => "\x86",
        "\xe2\x80\xa1" => "\x87",
        "\xcb\x86"     => "\x88",
        "\xe2\x80\xb0" => "\x89",
        "\xc5\xa0"     => "\x8a",
        "\xe2\x80\xb9" => "\x8b",
        "\xc5\x92"     => "\x8c",

        "\xc5\xbd"     => "\x8e",


        "\xe2\x80\x98" => "\x91",
        "\xe2\x80\x99" => "\x92",
        "\xe2\x80\x9c" => "\x93",
        "\xe2\x80\x9d" => "\x94",
        "\xe2\x80\xa2" => "\x95",
        "\xe2\x80\x93" => "\x96",
        "\xe2\x80\x94" => "\x97",
        "\xcb\x9c"     => "\x98",
        "\xe2\x84\xa2" => "\x99",
        "\xc5\xa1"     => "\x9a",
        "\xe2\x80\xba" => "\x9b",
        "\xc5\x93"     => "\x9c",

        "\xc5\xbe"     => "\x9e",
        "\xc5\xb8"     => "\x9f"
    );

    protected static function strlen($text){
        return (function_exists('mb_strlen') && ((int) ini_get('mbstring.func_overload')) & 2) ?
            mb_strlen($text,'8bit') : strlen($text);
    }

    public static function toUtf8($text){

        if (is_string($text)){
            $text = html_entity_decode($text);
        }
        if(is_array($text)){
            foreach($text as $k => $v)
            {
                $text[$k] = self::toUTF8($v);
            }
            return $text;
        }

        if(is_object($text)){
            foreach($text as $p => $v)
            {
                $text->$p = self::toUTF8($v);
            }
            return $text;
        }


        if(!is_string($text)) {
            return $text;
        }

        $max = self::strlen($text);

        $buf = "";
        for($i = 0; $i < $max; $i++){
            $c1 = $text[$i];
            if($c1>="\xc0"){ //Should be converted to UTF8, if it's not UTF8 already
                $c2 = $i+1 >= $max? "\x00" : $text[$i+1];
                $c3 = $i+2 >= $max? "\x00" : $text[$i+2];
                $c4 = $i+3 >= $max? "\x00" : $text[$i+3];
                if($c1 >= "\xc0" & $c1 <= "\xdf"){ //looks like 2 bytes UTF8
                    if($c2 >= "\x80" && $c2 <= "\xbf"){ //yeah, almost sure it's UTF8 already
                        $buf .= $c1 . $c2;
                        $i++;
                    } else { //not valid UTF8.  Convert it.
                        $cc1 = (chr(ord($c1) / 64) | "\xc0");
                        $cc2 = ($c1 & "\x3f") | "\x80";
                        $buf .= $cc1 . $cc2;
                    }
                } elseif($c1 >= "\xe0" & $c1 <= "\xef"){ //looks like 3 bytes UTF8
                    if($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf"){ //yeah, almost sure it's UTF8 already
                        $buf .= $c1 . $c2 . $c3;
                        $i = $i + 2;
                    } else { //not valid UTF8.  Convert it.
                        $cc1 = (chr(ord($c1) / 64) | "\xc0");
                        $cc2 = ($c1 & "\x3f") | "\x80";
                        $buf .= $cc1 . $cc2;
                    }
                } elseif($c1 >= "\xf0" & $c1 <= "\xf7"){ //looks like 4 bytes UTF8
                    if($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf" && $c4 >= "\x80" && $c4 <= "\xbf"){ //yeah, almost sure it's UTF8 already
                        $buf .= $c1 . $c2 . $c3 . $c4;
                        $i = $i + 3;
                    } else { //not valid UTF8.  Convert it.
                        $cc1 = (chr(ord($c1) / 64) | "\xc0");
                        $cc2 = ($c1 & "\x3f") | "\x80";
                        $buf .= $cc1 . $cc2;
                    }
                } else { //doesn't look like UTF8, but should be converted
                    $cc1 = (chr(ord($c1) / 64) | "\xc0");
                    $cc2 = (($c1 & "\x3f") | "\x80");
                    $buf .= $cc1 . $cc2;
                }
            } elseif(($c1 & "\xc0") === "\x80"){ // needs conversion
                if(isset(self::$win1252ToUtf8[ord($c1)])) { //found in Windows-1252 special cases
                    $buf .= self::$win1252ToUtf8[ord($c1)];
                } else {
                    $cc1 = (chr(ord($c1) / 64) | "\xc0");
                    $cc2 = (($c1 & "\x3f") | "\x80");
                    $buf .= $cc1 . $cc2;
                }
            } else { // it doesn't need conversion
                $buf .= $c1;
            }
        }
        return self::safeUtf8Encode($buf);
    }


//    public static function toUtf8($value)
//    {
//        if (is_string($value)) {
//            return self::safeUtf8Encode($value);
//        } elseif (is_array($value)) {
//            $ret = [];
//            foreach ($value as $i => $d) $ret[ $i ] = self::toUtf8($d);
//
//            return $ret;
//        } elseif (is_object($value)) {
//            foreach ($value as $i => $d) $value->$i = self::toUtf8($d);
//
//            return $value;
//        } else {
//            return $value;
//        }
//    }

    public static function safeUtf8Encode($string)
    {
        $string = self::replaceEncodings($string);

        $valid = mb_detect_encoding($string, "auto", true);

        if (!$valid) {
            return utf8_encode($string);
        }
        else {
            return $string;
        }

    }


    public static function htmlSpecialChars($string, int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, ?string $encoding = null, bool $double_encode = true)
    {
        return !empty($string) ? htmlspecialchars($string, $flags, $encoding, $double_encode) : $string;
    }


    public static function httpBuildQuery($data, string $numeric_prefix = "", ?string $arg_separator = "&", int $encoding_type = PHP_QUERY_RFC1738): string
    {
        return http_build_query($data, $numeric_prefix, $arg_separator, $encoding_type);

    }

    public static function urlEncode($string)
    {
        $string = urlencode($string);
        return $string;
    }

    public static function urlDecode($string)
    {

        if (Str::isString($string)) {
            $string = urldecode($string);
        }

        return $string;
    }


    public static function fixBrokenUtf8($string)
    {
        if (is_string($string) &&!is_object($string) && !is_array($string) && !Xml::isValid($string)) {
            $decoded = Json::decode("\"" . $string . "\"");
            if(empty($decoded)){
                if (str_starts_with($string, "'") || str_starts_with($string, '"')){
                    return json_decode($string);
                }else{
                    return json_decode("\"" . $string . "\"");
                }
            }else{
                return $string;
            }
        }
        return $string;
    }

    public static function toIsoTurkish (string $value): string
    {
        return mb_convert_encoding($value, "ISO-8859-9");
    }


    /*
     *  This Method will replace xml encoding ISO code TO UTF
     */
    public static function replaceEncodings ($value, $replace = 'UTF-8'): string
    {
        // As per Rifat Vai's instructions.

        return Str::replace(
            [
                'encoding="ISO-8859-1"',
                'encoding="ISO-8859-9"',
                'encoding="ISO-8859-15"',
                'encoding="ISO-8859-20"',
            ],
            'encoding="'.$replace.'"',
            $value
        );

    }


    public static function replaceTrChars($str){
        static $from = ["Ãœ", "Å", "Ä", "Ã‡", "Ä°", "Ã–", "Ã¼", "ÅŸ", "Ã§", "Ä±", "Ã¶", "ÄŸ",
            "Ü", "Ş", "Ğ", "Ç", "İ", "Ö", "ü", "ş", "ç", "ı", "ö", "ğ",
            "%u015F", "%E7", "%FC", "%u0131", "%F6", "%u015E", "%C7", "%DC", "%D6",
            "%u0130", "%u011F", "%u011E"];
        static $to = [
            'U', "S", "G", "C", "I", "O", "u", "s", "c", "i", "o", "g",
            "U", "S", "G", "C", "I", "O", "u", "s", "c", "i", "o", "g",
            "s", "c", "u", "i", "o", "S", "C", "U", "O", "I", "g", "G"
        ];

        return str_replace($from, $to, $str);
    }

    public static function base64Encode(string $string)
    {
       return  base64_encode($string);
    }

    public static function base64Decode(string $string, bool $strict = false)
    {

        return base64_decode($string, $strict);
    }

    public static function serialize($value)
    {
        return serialize($value);
    }

    public static function unserialize($data)
    {
        return unserialize($data);
    }
}