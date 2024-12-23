<?php

namespace common\integration\Utility;

use common\integration\Traits\HttpServiceInfoTrait;

class Ip
{
    use HttpServiceInfoTrait;

    public function clientAddr()
    {
        return $this->getClientIpAddress();
    }

    public static function isLocal()
    {
        $host = (new class { use HttpServiceInfoTrait; })->getClientIpAddress();
        if ($host == '::1' || $host == '127.0.0.1' || $host == 'localhost' || $host == 'UNKNOWN') {
            return true;
        }
        return false;
    }

    public static function isIPv4($ipAddress) {
        // Validate if the given string is a valid IPv4 address
        return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    public static function isIPv6($ipAddress) {
        // Validate if the given string is a valid IPv6 address
        return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

}