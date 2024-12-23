<?php

namespace common\integration\Utility;

use common\integration\GlobalFunction;
use common\integration\ManageLogging;

class Xml
{

    public static function fromArr(array $data, $root_tag = '', $header_tag = true, $encoding = 'UTF-8')
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="'. $encoding .'"?>'.(!empty($root_tag) ? $root_tag : '<rootTag/>'));
        self::recurse($xml, $data);

        $result = $xml->asXML();
        if (!$header_tag){
            $result = explode("\n", $result, 2)[1];
            $result = str_replace("\n", "", $result);
        }
        return $result;
    }

    public static function recurse(\SimpleXMLElement $object, array $data)
    {
        foreach ($data as $key => $value) {
            if (!empty($key)) {
                if (is_array($value)) {
                    $new_object = $object->addChild($key);
                    self::recurse($new_object, $value);
                } else {
                    if ($key == (int)$key) {
                        $key = "$key";
                    }
                    $object->addChild($key, Encode::htmlSpecialChars($value));
                }
            }
        }
    }

    public static function toArr($value, $needles = [], $position = 0)
    {
        if(!empty($needles)){
            $preg_replaced = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $value);
            $simpleXmlElement = new \SimpleXMLElement($preg_replaced);

            $obj = collect($needles)->map(function($needle) use ($simpleXmlElement, $position){
                $array = $simpleXmlElement->xpath("//{$needle}");

                if(!empty($array)){
                    return $array[$position];
                }

            })->filter()->first();
        }else {
            $value = self::stripInvalidChars($value);
            if(!self::isValid($value)){
                (new ManageLogging())->createLog(["action" => "INVALID_XML", $value => $value]);
                return [];
            }
            libxml_use_internal_errors(TRUE);
            $obj = new \SimpleXMLElement($value);
        }

        $arr = Json::decode(Json::encode($obj), true);
        $callback = function ($k, &$v) {empty($v) ? $v = "":"do nothing";};
        Arr::walkRecursive($arr, $callback);
        return $arr;

    }

    public static function stripInvalidChars($value)
    {
        if(is_string($value)) {
            $value = str_replace(['&'], "?", $value);
        }

        return $value;
    }

    public static function isValid($content, $version = '1.0', $encoding = 'utf-8')
    {
        if (trim($content) == '') {
            return false;
        }

        libxml_use_internal_errors(true);

        $doc = new \DOMDocument($version, $encoding);
        $doc->loadXML($content);

        $errors = libxml_get_errors();
        libxml_clear_errors();


        return empty($errors);
    }


}