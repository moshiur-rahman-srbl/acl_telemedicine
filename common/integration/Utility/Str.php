<?php

namespace common\integration\Utility;


use common\integration\ManageLogging;
class Str
{
    const STR_MB_TEXT_TO_UPPER = "mb_upper";

    const RIGHT_SM_FIRST = 'r';
    const LEFT_SM_FIRST = 'l';

    public static function isHyphenExist($string){
        $status = false;
        if (strpos($string, '-') !== false) {
            $status = true;
        }
        return $status;
    }

    public static function removeHyphen($string)
    {
        $last_part = substr($string, strrpos($string, "-") + 1);
        $with_hyphen = '-' . $last_part;
        $string = rtrim($string, $with_hyphen);
        return $string;
    }

    public static function isString($string){
        $status = false;
        if (is_string($string)) {
            $status = true;
        }
        return $status;
    }

    public static function fill($string, $length, $fill_by, $should_fill = true, $pad_type = STR_PAD_RIGHT){

       // re generate a string with (space, -, digit etc), example 'Test-------'
      if ($should_fill) {
         $string = str_pad($string, $length, $fill_by, $pad_type);
      }

      return $string;
   }


   public static function toArr($string, $delimiter):array
   {
      if($string === false || empty($string)) {
          return [];
      } else{
          return explode($delimiter, $string);
      }

   }

   public static function toLower($string):string
   {
       return strtolower($string);
   }  
   
   public static function toUpper($string):string
   {
       return strtoupper($string);
   }

   public static function ucFirst($string):string
   {
       return ucfirst($string);
   }

    public static function truncate($string, $limit, $is_multi_bytes = true, $start = 0, $end = 0)
    {
        if ($is_multi_bytes) {
            $string_length = \mb_strlen($string);
            return $string_length > $limit ? mb_substr($string, $start, $limit) : $string;
        } else {
            $string_length = strlen($string);
            return $string_length > $limit ? substr($string, $start, $limit) : $string;
        }

    }

    public static function replace($find, $replace, $string, $count=null)
    {
        return str_replace($find, $replace, $string, $count);

    }

    public static function removeFromFirst($string,$length)
    {

        return substr($string, $length);

    }


    public static function removeFromLast($string,$length)
    {

        return substr($string,0, -$length);

    }

    public static function midVal($string, $first_len, $last_len)
    {
        $len = strlen($string);
        $combined_len = $first_len+$last_len;
        if ($len >= $combined_len){
         return   self::removeFromFirst(self::removeFromLast($string, $last_len), $first_len);
        }
        return $string;
    }

    public static function upperCase($name){
        return (string) \Illuminate\Support\Str::of($name)->upper();
    }

    public static function contains($haystack, $needle)
    {
		return str()->contains($haystack, $needle);
    }

    public static function position($haystack, $needle, $offset=0)
    {
        return strpos($haystack, $needle, $offset);
    }

    public static function len($string)
    {
        return strlen($string);
    }

    public static function sartEndSubStr($string, $start, $end)
    {
        return substr($string, $start, $end - $start);
    }

    public static function whiteTrim($value, $enable_encoding = false)
    {
        $value = ltrim($value);
        $value = rtrim($value);
        $encoded_value = trim(iconv("UTF-8","ISO-8859-1//IGNORE",$value)," \t\n\r\0\x0B\xA0");

        if ($enable_encoding) {
            $value = $encoded_value;
        }

        return $value;

    }

    public static function lowerCase($value){
        return str()->lower($value);
    }

    public static function titleCase ($value)
    {
        return str()->title($value);
    }

    public static function getFirstString($string, $start, $length): string
    {
        return substr($string, $start, $length);
    }

    public static function stripTags ($value)
    {
        return strip_tags($value);
    }

    public static function customCaseConversion ($name, $case = 'title')
    {
        if (!empty($name)) {
            //$name = self::encodeToUtf8($name);
            mb_internal_encoding("UTF-8");
            $upper = ['Ç', 'Ğ', 'I', 'İ', 'Ö', 'Ş', 'Ü'];
            $lower = ['ç', 'ğ', 'ı', 'i', 'ö', 'ş', 'ü'];

            if ($case == 'title') {
                $word_list = mb_split("\s", $name);
                foreach ($word_list as $key => $word) {
                    if(empty($word)){
                        continue;
                    }
                    $word_array = preg_split("//u", $word, 2, PREG_SPLIT_NO_EMPTY);
                    if(isset($word_array[0])){
                        $word = mb_convert_case(str_replace($lower, $upper, $word_array[0]), MB_CASE_TITLE, "UTF-8");
                    }
                    if (isset($word_array[1])) {
                        $word .= mb_convert_case(str_replace($upper, $lower, $word_array[1]), MB_CASE_LOWER, "UTF-8");
                    }
                    $word_list[$key] = $word;
                }
                $name = implode(" ", $word_list);
            } elseif ($case == 'upper') {
                $name = mb_convert_case(str_replace($lower, $upper, $name), MB_CASE_UPPER, "UTF-8");
            } elseif ($case == 'lower') {
                $name = mb_convert_case(str_replace($upper, $lower, $name), MB_CASE_LOWER, "UTF-8");
            } elseif ($case == 'convertTurkishCharactersToEnglish') {
                $search = ['Ğ', 'ğ', 'Ü', 'ü', 'Ö', 'ö', 'Ş', 'ş', 'İ', 'ı', 'Ç', 'ç'];
                $replace = ['G', 'g', 'U', 'u', 'O', 'o', 'S', 's', 'I', 'i', 'C', 'c'];
                $name = mb_convert_case(str_replace($search, $replace, $name), MB_CASE_LOWER, "UTF-8");
            } elseif($case == 'convertTurkishCharactersToEnglishAsItIs'){
	            $search = ['Ğ', 'ğ', 'Ü', 'ü', 'Ö', 'ö', 'Ş', 'ş', 'İ', 'ı', 'Ç', 'ç'];
	            $replace = ['G', 'g', 'U', 'u', 'O', 'o', 'S', 's', 'I', 'i', 'C', 'c'];
	            $name = Self::replace($search,$replace,$name);
            }
            elseif($case == self::STR_MB_TEXT_TO_UPPER){
                $name = mb_strtoupper($name);
            }
        }
        return $name;
    }

    public static function explode(string $separator, string $string, int $limit = PHP_INT_MAX): array
    {
        return explode($separator, $string, $limit);
    }

    public static function lastNChars($string, $n)
    {
        return substr($string, -$n);
    }

    public static function chunkSplit($string, $length, $separator)
    {
        return chunk_split($string, $length, $separator);
    }

    public static function replaceMiddleContractors($val, $contractor, $replaceWith)
    {
        $thisIsFirst = -1;
        $thisIsLast = -1;
        for ($i = 0; $i < self::len($val); $i++) {
            if ($val[$i] == $contractor) {
                if ($thisIsFirst == -1) {
                    $thisIsFirst = $i;
                } else {
                    $thisIsLast = $i;
                    $val[$i] = $replaceWith;
                }
            }

        }
        if ($thisIsLast > -1) {
            $val[$thisIsLast] = $contractor;
        }

        return $val;
    }

    public static function startsWith($haystack, $needle)
    {
        return str_starts_with($haystack, $needle);
    }

    public static function trim($value, $type = '', $needle = '')
    {
        if ($type == self::LEFT_SM_FIRST) {
            return ltrim($value, $needle);
        } elseif ($type == self::RIGHT_SM_FIRST) {
            return rtrim($value, $needle);
        } else {
            $iconved = $value;
            try {
                $iconved = iconv("UTF-8", "ISO-8859-1//IGNORE", $value);
            }catch (\Throwable $throwable){

                (new ManageLogging())->createLog([
                    "action" => "STR_TRIM_EXCEPTION",
                    "exceptions" => Exception::fullMessage($throwable),
                    "params" => [$value, $type, $needle , $iconved],
                    "from_encoding" => mb_detect_encoding($value),
                    "to_encoding" => mb_detect_encoding($iconved)
                ]);
            }
            return trim($iconved, " \t\n\r\0\x0B\xA0");
        }
    }
	
	public static function startWith($sentence, $niddle =[]){
		return \Illuminate\Support\Str::startsWith($sentence, $niddle);
	}

    public static function urlencode(?string $str): string
    {
        return rawurlencode($str);
    }

    public static function urldecode(?string $str): string
    {
        return urlencode($str);
    }

    public static function isUrlEncoded(?string $str): bool
    {
        return rawurldecode($str) !== $str;
    }

    public static function isNotUrlEncoded(?string $str): bool
    {
        return ! self::isUrlEncoded($str);
    }

    public static function titleToKey(string $title){
        if (empty($title)){
            return null;
        }
        return self::replace([' ','.','/','&'], ['_','','_'], self::toLower(Encode::safeUtf8Encode(self::trim($title))));
    }
    
    public static function preg_match($pattern, $string, $return_match = false)
    {
        if ($return_match) {
            preg_match($pattern, $string, $match);
            return $match;
        }
        return preg_match($pattern, $string);
    }
	
	public  static function pregMatchAll($niddle, $string){
		preg_match_all($niddle, $string, $matches);
		return $matches;
	}

    public static function isNullOrEmptyString($str){
        return ($str === null || trim($str) === '');
    }

    public static function char(int $code_point): string
    {
        return chr($code_point);
    }

    public static function preg_replace($string, $pattern = "/[^0-9]/", $replacement = ""){
        return preg_replace($pattern, $replacement, $string );
    }
    public static function removeMultipleSpacesWithinString($string, $replacement = ' '){
        return trim(preg_replace('/\s+/', $replacement, $string));
    }

    public static function singular($string): string
    {
        return str_singular($string);
    }
	
	public static function camelCase($name){
		return str()->camel($name);
	}

    public static function random($length){
        return (string) \Illuminate\Support\Str::random($length);
    }

	public static function toString ($value)
    {
		return strval($value);
	}

    public static  function nl2br($string){
        return nl2br($string);
    }

    public static function parseStr(string $query_string){
        parse_str($query_string,$arr);
        return $arr;
    }

    public static function filterVar($value, $filter = FILTER_DEFAULT, $options = 0)
    {
        return filter_var($value, $filter, $options);
    }

    public static  function strIntPos(string $haystack, string $needle, int $offset = 0): int|false
    {
        return  stripos($haystack, $needle,  $offset );
    }
	
	public static function boolenValue($input)
	{
		if (is_bool($input)) {
			return $input;
		}
		
		if ($input === 0) {
			return false;
		}
		
		if ($input === 1) {
			return true;
		}
		
		if (is_string($input)) {
			switch (strtolower($input)) {
				case "true":
				case "on":
				case "1":
					return true;
					break;
				
				case "false":
				case "off":
				case "0":
					return false;
					break;
			}
		}
		return null;
	}
	
	public static function mbConvertCase($string, $case, $charset)
	{
		return mb_convert_case($string, $case, $charset);
	}

    public static function compare(string $first_string, string $second_string)
    {
        return strcmp($first_string, $second_string);
    }
	
	public static function value($value, ...$args)
	{
		return $value instanceof \Closure ? $value(...$args) : $value;
	}

}