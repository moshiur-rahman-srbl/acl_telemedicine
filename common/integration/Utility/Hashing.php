<?php

namespace common\integration\Utility;

class Hashing
{
    public static function bcrypt($value)
    {
        return bcrypt($value);
    }

}