<?php

namespace common\integration\Utility;


class Process
{

    public static function phpUname(string $mode = 'a'): string
    {
        return php_uname($mode);
    }

    public static function getMyPid()
    {
        return getmypid();
    }

}