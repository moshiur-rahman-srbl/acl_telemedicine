<?php


namespace common\integration;


use App\Http\Controllers\Traits\OTPTrait;
use App\Utils\CommonFunction;
use common\integration\Payment\Card;
use common\integration\Utility\Arr;
use common\integration\Utility\Exception;
use common\integration\Utility\Phone;
use common\integration\Utility\Str;

class InformationMasking
{
    public static function unsetKeys($data, $masking_card = false, $remove_custom_keys = [])
    {
        if (isset($data['action'])) {
            $newArr['action'] = $data['action'];
            $data = array_merge($newArr, $data);
        }

        if(!empty($remove_custom_keys)){
            $removeKeys = $remove_custom_keys;
        }else{
            $removeKeys = array('store_key', 'year', 'cvv', 'month', 'expiry_year', 'expiry_month', 'cv2', 'pan',
                'app_secret', 'bankObj','merchantObj', 'credit_card_no','cc_no', 'credit_card','Pan', 'Expiry',
                'card_no', 'Password','card','media_identifier','media_details','Ecom_Payment_Card_ExpDate_Month','Ecom_Payment_Card_ExpDate_Year', 'card_number');
        }

        foreach ($removeKeys as $item){
            self::removeKey($data, $item, $masking_card);
        }
        return $data;
    }

    public static function removeKey(&$array, $key, $masking_card)
    {
        $creditCardArray = ['cc_no', 'credit_card', 'credit_card_no', 'expiry_year','expiry_month'];

        if (is_array($array))
        {
            if (isset($array[$key]))
            {
                if (!is_array($array[$key]) && in_array($key, $creditCardArray) ){
                    if ($key == 'expiry_month'){
                        if (strlen($array[$key]) < 5){
                            $array['part_1'] = DataCipher::customEncryptionDecryption($array[$key], config('app.brand_secret_key'),'encrypt', 0, \config('constants.ENCRYPTION_FIXED_IV'), \config('constants.ENCRYPTION_FIXED_SALT'));
                            unset($array[$key]);
                        }

                    }elseif ($key == 'expiry_year'){
                        if (strlen($array[$key]) < 5){
                            $array['part_2'] = DataCipher::customEncryptionDecryption($array[$key], config('app.brand_secret_key'),'encrypt', 0, \config('constants.ENCRYPTION_FIXED_IV'), \config('constants.ENCRYPTION_FIXED_SALT'));
                            unset($array[$key]);
                        }

                    }else{
                        if ($masking_card){
                            $array[$key] = CommonFunction::creditCardNoMasking($array[$key]);
                        }else{
                            $array[$key] = DataCipher::customEncryptionDecryption($array[$key], config('app.brand_secret_key'),'encrypt', 0, \config('constants.ENCRYPTION_FIXED_IV'), \config('constants.ENCRYPTION_FIXED_SALT'));

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
                    self::removeKey($array[$k], $key, $masking_card);
                }
            }
        }
    }

    public static function hideValues($data, $hides, $masks = [], $replace_with = "***", $should_mask = true ){
       //to statically log all request
        if ($should_mask){
            foreach ($hides as $hide) {
                if (strlen($hide) >= (Card::BIN_DIGITS_LEN+Card::LAST_DIGITS_LEN)){
                    $masks["masked_".base64_encode($hide)] = $hide;
                }
            }
        }
        if(!empty($masks)){
            return self::maskAndHide($data, $hides, $masks);
        }
        try {
            if (is_array($data)) {
                array_walk_recursive($data, function (&$v) use ($hides){$v = self::hideValues($v, $hides);});
            } else if (is_string($data)) {
                $data = str_replace($hides, $replace_with, $data);
            }
            return $data;
        }catch (\Throwable $e){
            return $data;
        }
    }

    public static function cardPartToMask($source, $card_no)
    {
        switch ($source){
            case "log":
            case "db":
                return Str::midVal($card_no, Card::BIN_DIGITS_LEN, Card::LAST_DIGITS_LEN);
        }

    }

    public static function maskAndHide($data, $hides, $masks)
    {

        try {
            uasort($masks, function($a, $b){
                return strlen($b) - strlen($a);
            });
            $masked_arr = array();
            foreach ($masks as $k => $v) {
                if(($len = strlen($v)) < 15){
                    if ($len<Card::BIN_DIGITS_LEN){
                        $first_length = $len;
                        $last_length = 0;
                    }else{
                        $first_length = round((Card::BIN_DIGITS_LEN/15)*$len);
                        $last_length = round((Card::LAST_DIGITS_LEN/15)*$len);
                    }
                    $masked = self::sheild($v,$first_length,$last_length);
                }else{
                    $masked = self::sheild($v);
                }
                $masked_arr[$k] = $masked;
                $data = self::hideValues($data, [$v], [], $k, false);
            }
            $data = self::hideValues($data, $hides,[],'***',false);

            foreach ($masked_arr as $k => $v) {
                $data = self::hideValues($data, [$k], [], $v,false);
            }
            
            return $data;
        }catch (\Throwable $t){
            return  $data;
        }
    }
    
    

    public static function sheild($string, $first_length = Card::BIN_DIGITS_LEN, $last_length = Card::LAST_DIGITS_LEN, $replaceWith = "***")
    {
        $first = substr($string, 0, $first_length);
        $last = substr($string, -$last_length);
        return $first.$replaceWith.$last;
    }

    /**
     * Masks a portion of a string with a repeated character.
     *
     * @param  string  $string
     * @param  string  $character
     * @param  int  $index
     * @param  int|null  $length
     * @param  string  $encoding
     */
    public static function mask($string, $character, $index = 0, $length = null, $encoding = 'UTF-8')
    {
        if ($character === '') {
            return $string;
        }

        $segment = mb_substr($string, $index, $length, $encoding);

        if ($segment === '') {
            return $string;
        }

        $strlen = mb_strlen($string, $encoding);
        $startIndex = $index;

        if ($index < 0) {
            $startIndex = $index < -$strlen ? 0 : $strlen + $index;
        }

        $start = mb_substr($string, 0, $startIndex, $encoding);
        $segmentLen = mb_strlen($segment, $encoding);
        $end = mb_substr($string, $startIndex + $segmentLen);

        return $start.str_repeat(mb_substr($character, 0, 1, $encoding), $segmentLen).$end;
    }


    /**
     * Phone Number Masking.
     *
     * @param  string  $string
     */
    
     public static function phoneNumberMasking($phone_number)
     {
        $phone_number = Str::replace(' ', '', $phone_number);

        $masked_phone_number = static::mask($phone_number, '*', 3, 6);
        if(BrandConfiguration::isShowFormattedPhoneNumber()){
            $masked_phone_number = Phone::format($masked_phone_number);
        }
        return $masked_phone_number;
     }
	 
	 /**
	  * OTP MASKING FROM STRING
	  * @param string $string
	  */
	 
	 public static function getOtpToMask(string $string): string
	 {
		 $otp_len = (new class { use OTPTrait; })->otp_length;
		 $content = Str::replace(["\n","\r","\t"], "", $string);
		 $matches = collect(Str::pregMatchAll('!\d+(?:\.\d+)?!', $content))->first();
		 
		 $raw_key = '';
		 foreach ($matches as $match) {
			 
			 if(Str::len($match) == $otp_len && Str::preg_match('/^\d+$/', $match)){
				 $raw_key = $match;
				 break;
			 }
			 
		 }
		 
		 return $raw_key;
		 
	 }

     public function getConcealableInformation()
     {
         $databaseInformation =config('database');

         $concealableKeys = ["username", "password", "host"];

         $concealableInformation = [];

         if(Arr::isOfType($databaseInformation)){
             Arr::walkRecursive($databaseInformation, function ($k, &$v) use ($concealableKeys, &$concealableInformation) {
                 if(Arr::isAMemberOf($k, $concealableKeys) && !Arr::isOfType($v) && !empty($v)){
                     $concealableInformation[] = $v;
                 }
             });
         }

         if(!empty($concealableInformation)){
             $concealableInformation = Arr::unique($concealableInformation);
         }

         return $concealableInformation;
     }


}