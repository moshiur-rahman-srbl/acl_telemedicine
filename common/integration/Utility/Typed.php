<?php

namespace common\integration\Utility;

class Typed
{
    const Unspecified = 1;
    const Boolean = 2;
    const Byte = 3;
    const SignedByte = 4;
    const UnsignedByte = 5;
    const Short = 6;
    const UnsignedShort = 7;
    const Int16 = 8;
    const UnsignedInt16 = 9;
    const Int32 = 10;
    const Int64 = 11;
    const Int96 = 12;
    const ByteArray = 13;
    const String = 14;
    const Float = 15;
    const Double = 16;
    const Decimal = 17;
    const DateTimeOffset = 18;
    const Interval = 19;
    const UnsignedInt32 = 20;
    const UnsignedInt64 = 21;


    public static function type($val)
    {
        return gettype($val);
    }


    public static function isInt($type)
    {
        return Arr::isAMemberOf($type,array( self::Int16, self::UnsignedInt16, self::Int32, self::Int64, self::Int96));
    }


    public static function intval($val)
    {
        $ret = Number::format($val,0,'','.','',Typed::Int16);

        return $ret;
    }

    public static function isDouble($type)
    {
        return Arr::isAMemberOf($type, [self::Double]);
    }

    public static function dooubleval($val, $length = 4)
    {
        $ret = Number::format($val, $length,'','.','',Typed::Double);

        return $ret;
    }


}