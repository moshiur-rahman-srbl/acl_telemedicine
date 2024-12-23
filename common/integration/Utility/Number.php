<?php

namespace common\integration\Utility;

use common\integration\ManipulateDate;

class Number
{
    public static function format($number, $length = 4, $currency_symbol = '', $decimal_secprator = '.',$thousands_separator = '', $type = '')
    {
        $ret = number_format((float)$number, $length, $decimal_secprator, $thousands_separator). $currency_symbol;

        if(Typed::isInt($type)){
            $ret = (int)$ret;
        }
        if(Typed::isDouble($type)){
            $ret = (double)$ret;
        }

        return $ret;
    }

    public static function normalize($number, $precision = 2, $decimals = 2, $decimal_sepratior = ".", $thousands_separator = "")
    {
        return number_format(round(floatval($number), 2, PHP_ROUND_HALF_DOWN), $decimals, $decimal_sepratior, $thousands_separator);
    }

    public static function breakDown($number, $returnUnsigned = false)
    {
        $negative = 1;
        if ($number < 0)
        {
            $negative = -1;
            $number *= -1;
        }

        if ($returnUnsigned){
            return array(
                floor($number),
                ($number - floor($number))
            );
        }

        return array(
            floor($number) * $negative,
            ($number - floor($number)) * $negative
        );
    }

    public static function toFraction($number)
    {
        $len = strlen($number);
        return $number/e($len);
    }

    public static function toAbsolute($number) {
        return abs($number);
    }

    public static function isNum($string){

        return is_numeric($string);
    }
	
	public static function getOnlyIntegerNum($value)
	{
		return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
	}

    public static function round($num, $precision = 0, $mode = PHP_ROUND_HALF_UP): float
    {
        return round($num, $precision, $mode);
    }

    public static function fmod($dividend, $divisor): int
    {
        return fmod($dividend, $divisor);
    }
    public static function divideBy($digit, $divisor_digit = 100): float
    {
        return fdiv($digit,$divisor_digit);
    }

    public static function mtRand(int $min, int $max): int
    {
        return mt_rand($min, $max);
    }

    public static function decHex(int $num): string
    {
        return dechex($num);
    }
    public static function getRandomNumberBase16() {
        $sb = '';
        $processName = Process::phpUname('n') .Process::getMyPid();
        $secret = $processName . Process::getMyPid() . ManipulateDate::microTime(true);
        $randomBytes = openssl_random_pseudo_bytes(128);
        $secret .= $randomBytes;
        for ($i = 0; $i < 128; $i++) {
            $randomInt = self::mtRand(0, 15);
            $sb .= self::decHex($randomInt);
        }
        return Str::toUpper($sb);
    }

    public static function toInt (string $num): int
    {
        if (is_string($num)) {
            $num = (int)$num;
        } elseif(is_double($num) || is_float($num)) {
            $num = (int) $num;
        }
        return $num;
    }

    public static function isArithmeticSequence($number) {
        $number = strval($number);

        $delta = $number[1] - $number[0];

        for ($index = 0; $index < strlen($number) - 1; $index++)
        {
            if (($number[$index + 1] - $number[$index]) != $delta)
            {
                return false;
            }
        }

        return true;
    }

    public static function isNextDigitASubsequentDigit($number, $delta = 1)
    {
        $number = strval($number);

        for ($index = 0; $index < strlen($number) - 1; $index++)
        {
            if (abs(($number[$index + 1] - $number[$index])) == $delta)
            {
                return true;
            }
        }

        return false;

    }

    public static function isNextDigitSame($number)
    {
        return self::isNextDigitASubsequentDigit($number, 0);
    }

    public static function multiplyBy($digit, $multiply_digit = 100): float
    {
        return bcmul($digit,$multiply_digit);
    }
    public static function isNegative($number): bool
    {
        return $number < 0;
    }

    public static function toFloat($num)
    {
        if (is_string($num)) {
            $num = (float)$num;
        }
        return $num;
    }
    public static function numberFormatter($number = 0): bool|string
    {
        $numberFormatter = new \NumberFormatter('en_US', \NumberFormatter::ORDINAL);
        return $numberFormatter->format($number);
    }
    public static function decimalCommaToPointConversion($amount, $precision = 2) : float
    {
        $decimal_comma_position = $precision + 1;
        $is_valid = ( Str::len($amount) - Str::position($amount, ",") ) == $decimal_comma_position;

        if($is_valid){
            $amount = floatval(Str::replace( ',', '.',  Str::replace( '.','', $amount)));
        }else{
            $amount = Str::replace( ',','', $amount);
        }

        return self::format($amount, $precision);
    }

    public static function intVal($string)
    {
        return intval($string);
    }

}