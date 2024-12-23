<?php

namespace common\integration\Utility;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class System
{
    const OS_UNKNOWN = 1;
    const OS_WIN = 2;
    const OS_LINUX = 3;
    const OS_OSX = 4;
    public const OS_IOS = 5;
    public const OS_ANDROID = 6;

    public const DEVICE_LIST = [
        self::OS_UNKNOWN => 'Unknown',
        self::OS_WIN     => 'Windows',
        self::OS_LINUX   => 'Linux',
        self::OS_OSX     => 'Mac',
        self::OS_IOS     => 'iOS',
        self::OS_ANDROID => 'Android',
    ];


    /**
     * @return int
     */
    static public function getOS() {
        switch (true) {
            case stristr(PHP_OS, 'DAR'): return self::OS_OSX;
            case stristr(PHP_OS, 'WIN'): return self::OS_WIN;
            case stristr(PHP_OS, 'LINUX'): return self::OS_LINUX;
            default : return self::OS_UNKNOWN;
        }
    }

    public static function getClientDeviceId() {
        $device_id = self::OS_UNKNOWN;
        $u_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        //Get the operating_system name
        if($u_agent) {
            if (preg_match('/linux/i', $u_agent)) {
                $device_id = self::OS_LINUX;
            } elseif (preg_match('/macintosh|mac os x|mac_powerpc/i', $u_agent)) {
                $device_id = self::OS_OSX;
            } elseif (preg_match('/windows|win32|win98|win95|win16/i', $u_agent)) {
                $device_id = self::OS_WIN;
            } elseif (preg_match('/ipod/i', $u_agent) || preg_match('/ipad/i', $u_agent) || preg_match('/iphone/i', $u_agent)) {
                $device_id = self::OS_IOS;
            } elseif (preg_match('/android/i', $u_agent)) {
                $device_id = self::OS_ANDROID;
            }
        }
        return $device_id;
    }

    /**
     * @param $query: db_connection, redis_connection
     * @return \Illuminate\Http\JsonResponse
     */
    public static function healthCheck($request_data = null)
    {

        $data['action'] = "Health_Check";
        $data['status'] = 200;
        $data['database_connection'] = true;
        $data['redis_connection']    = false;
        return response()->json(['message' => $data], 200);
        
        $cached_data = Cache::store('file')->get($data['action']);
        if (!empty($cached_data)){
            return response()->json(['message' => $cached_data], 200);
        }
        
        $db_connection    = $request_data['db_connection'] ?? null;
        $redis_connection = $request_data['redis_connection'] ?? null;


        try {
            $data['database_connection'] = System::isDatabaseReady($db_connection); //true
            $data['redis_connection']    = false;//System::isRedisReady($redis_connection);
        } catch (\Exception $e) {
            $data['error'] = "Error: " . $e->getMessage();
            $data['status'] = 500;
        }
        
        Cache::store('file')->add($data['action'],$data,60);
        return response()->json(['message' => $data], $data['status']);
    }
    public static function isDatabaseReady($connection = null)
    {
        $isReady = true;
        try {
            DB::connection($connection)->getPdo();
        } catch (\Exception $e) {
            $isReady = false;
        }
        return $isReady;
    }

    public static function isRedisReady($connection = null)
    {
        $isReady = true;
        try {
            $redis = Redis::connection($connection);
            $isReady = $redis->client()->isConnected();
            $redis->disconnect();
        } catch (\Exception $e) {
            $isReady = false;
        }

        return $isReady;
    }

}