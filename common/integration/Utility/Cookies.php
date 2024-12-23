<?php

namespace common\integration\Utility;

use Illuminate\Support\Facades\Cookie;

class Cookies
{

    public static function set($name, $value, $time){
        return Cookie::queue($name, $value, $time);
    }

    public static function get($key = null, $default = null){
        return Cookie::get($key, $default);
    }

    public static function delete($name){
        return Cookie::queue(Cookie::forget($name));
    }

    public static function setRaw ($name, $value, $time, $path)
    {
        return setcookie($name, $value, $time, $path);
    }

    public static function getRaw ($name)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

}