<?php

namespace common\integration\Utility;

use App\Models\Country;
use App\Models\Profile;
use common\integration\BrandConfiguration;
use common\integration\ManageLogging;
use libphonenumber\PhoneNumberUtil;
use Propaganistas\LaravelPhone\Exceptions\NumberParseException;
use Propaganistas\LaravelPhone\PhoneNumber;

class Phone
{
    const COUNTRY_CODE_TURKEY = "+90";
    const FORMATION_TYPE_GENERAL = 1;
    const FORMATION_TYPE_FOR_HYPHEN = 2;
    const MINIMUM_PHONE_LENGTH = 7;
    const MINIMUM_PHONE_AREA_CODE_LENGTH = 3;

    /** @var PhoneNumber $phoneNumber */
    private $phoneNumber;

    /** @var \libphonenumber\PhoneNumber $libPhoneNumber */
    private  $libPhoneNumber;

    private bool $isParsed = false;

    public static function removeCountryCode($phone, $countryCode = self::COUNTRY_CODE_TURKEY)
    {
        return preg_replace('/^\+?' . $countryCode . '|\|1|\D/', '', ($phone));
    }



    public static function viaLib($number, $country) : ?self
    {
        $instance = new self();
        $instance->phoneNumber = new PhoneNumber($number, $country);
        try {
            $instance->libPhoneNumber = $instance->phoneNumber->toLibPhoneObject();
            $instance->isParsed = $instance->phoneNumber->isValid();
        }
        catch (\Throwable $throwable){
            (new  ManageLogging())->createLog([
                "action" => "VIA_LIB_PHONE_EXCEPTION",
                "message" => $throwable->getMessage()
            ]);
        }
        return $instance;
    }

    public function isParsed()
    {
        return $this->isParsed;
    }

    public function getCountryCode()
    {
        if($this->isParsed){
            return $this->libPhoneNumber->getCountryCode();
        }

        return "";
    }

    public function getNationalNumber()
    {
        if($this->isParsed){
            return $this->libPhoneNumber->getNationalNumber();
        }

        return "";

    }



    public static function format($number, $type = self::FORMATION_TYPE_GENERAL, $withPhoneLib = false)
    {
       if ($type == self::FORMATION_TYPE_GENERAL){
           $length = Str::len($number) - 9;
           $number = Str::chunkSplit(Str::removeFromLast($number, $length), 3, ' '). Str::chunkSplit(Str::removeFromFirst($number, 9), 2, ' ');
       }

        if ($type == self::FORMATION_TYPE_FOR_HYPHEN) {
            $number = Str::getFirstString($number, 0, 2) . '-' . Str::removeFromFirst($number, 2);
        }

       return $number;
    }

    public static function getPhoneNumberLength(bool $except_country_code = false):int
    {
        $phone_number_length = Profile::PHONE_LENGTH;
        if ($except_country_code || BrandConfiguration::allowTenDigitPhoneNumber()){
            $phone_number_length = 10;
        }
        return $phone_number_length;
    }

    public static function getCountryPhoneCode ($phone_no): array
    {
        $full_phone_number = Str::trim(Str::replace(['+', ' '], '', $phone_no), 'l', '0');
        $country_phone_code = '';
        $only_phone_number = '';
        $number = '';
        $area_code = '';

        if (!empty($full_phone_number)) {
            $countries = (new Country())->getAll();

            if (count($countries) > 0) {
                foreach ($countries as $key => $value) {
                    if (!empty($value->country_phone_code) && Str::position($full_phone_number, $value->country_phone_code) === 0) {
                        $tmp_only_phone = Str::removeFromFirst($full_phone_number, Str::len($value->country_phone_code));

                        if ($tmp_only_phone >= self::MINIMUM_PHONE_LENGTH) {
                            $country_phone_code = $value->country_phone_code;
                            $only_phone_number = $tmp_only_phone;
                            break;
                        }
                    }
                }
                if(!empty($only_phone_number)){
                    $number = Str::removeFromFirst($only_phone_number, self::MINIMUM_PHONE_AREA_CODE_LENGTH);
                    $area_code = Str::sartEndSubStr($only_phone_number, 0,  self::MINIMUM_PHONE_AREA_CODE_LENGTH);
                }
            }
        }

        return [$country_phone_code, $only_phone_number, $full_phone_number, $number, $area_code];
    }

}