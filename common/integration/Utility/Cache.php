<?php

namespace common\integration\Utility;

use Illuminate\Redis\RedisManager;

class Cache
{
    const FP_WALLET_APPROVE =
        ["key" =>"wallet_approve",
         "lifetime" => 86400
        ];

    const KEY_REFUND_REQUEST_ALREADY_IN_PROGRESS = "REFUND_REQUEST_IN_PROGRESS";
    const KEY_REFUND_APPROVE_ALREADY_IN_PROGRESS = "REFUND_APPROVE_IN_PROGRESS";
    const KEY_REFUND_REJECT_ALREADY_IN_PROGRESS = "REFUND_REJECT_IN_PROGRESS";
    const KEY_REFUND_APPROVE_REFUND_TO_BANK_SQL_EXCEPTION_CONTROL = "REFUND_APPROVE_REFUND_TO_BANK_SQL_EXCEPTION_CONTROL";

    const TIME_REFUND_IN_PROGRESS = 60;

    const ORDER_STATUS_REFERENCE = 'ORDER_STATUS';

    const CACHE_KEY_REFUND_IN_PROGRESS = [
        self::KEY_REFUND_REQUEST_ALREADY_IN_PROGRESS,
        self::KEY_REFUND_APPROVE_ALREADY_IN_PROGRESS,
        self::KEY_REFUND_REJECT_ALREADY_IN_PROGRESS,
    ];

    public static function add($key, $data, $time_in_seconds, $reference=null)
    {
        $original_key = $key;
        if (!empty($reference)){
            $key = $key.'_'.$reference;
        }
        $prefix = self::getCachePrefix($reference, $original_key);
        self::setCachePrefix($reference, $original_key);
        self::forget($original_key, $reference);
        if (empty($time_in_seconds)){
            $cache = \Illuminate\Support\Facades\Cache::forever($key, $data);
        }else{
            $cache = \Illuminate\Support\Facades\Cache::add($key, $data, $time_in_seconds);
        }
        self::setCachePrefix($reference, $original_key, $prefix['default_cache_prefix'] ?? '', $prefix['redis_option_cache_prefix'] ?? '', $prefix['redis_default_cache_prefix'] ?? '');
        return $cache;
    }

    public static function get($key, $reference=null, $default_data = null)
    {
        $original_key = $key;
        if (!empty($reference)){
            $key = $key.'_'.$reference;
        }
        $prefix = self::getCachePrefix($reference, $original_key);
        self::setCachePrefix($reference, $original_key);
        $cache = \Illuminate\Support\Facades\Cache::get($key, $default_data);
        self::setCachePrefix($reference, $original_key, $prefix['default_cache_prefix'] ?? '', $prefix['redis_option_cache_prefix'] ?? '', $prefix['redis_default_cache_prefix'] ?? '');
        return $cache;
    }

    public static function has($key, $reference = null){
        $original_key = $key;
        if (!empty($reference)){
            $key = $key.'_'.$reference;
        }
        $prefix = self::getCachePrefix($reference, $original_key);
        self::setCachePrefix($reference, $original_key);
        $cache = \Illuminate\Support\Facades\Cache::has($key);
        self::setCachePrefix($reference, $original_key, $prefix['default_cache_prefix'] ?? '', $prefix['redis_option_cache_prefix'] ?? '', $prefix['redis_default_cache_prefix'] ?? '');
        return $cache;
    }

    public static function forget($key, $reference = null){
        $original_key = $key;
        if (!empty($reference)){
            $key = $key.'_'.$reference;
        }
        $prefix = self::getCachePrefix($reference, $original_key);
        self::setCachePrefix($reference, $original_key);
        \Illuminate\Support\Facades\Cache::forget($key);
        self::setCachePrefix($reference, $original_key, $prefix['default_cache_prefix'] ?? '', $prefix['redis_option_cache_prefix'] ?? '', $prefix['redis_default_cache_prefix'] ?? '');
    }

    public static function increment($key, $increment_by = null, $reference = null){
        if (!empty($reference)){
            $key = $key.'_'.$reference;
        }
        if (!empty($increment_by)){
            \Illuminate\Support\Facades\Cache::increment($key, $increment_by);
        }else{
            \Illuminate\Support\Facades\Cache::increment($key);
        }
    }

    public static function decrement($key, $decrement_by = null, $reference = null){
        if (!empty($reference)){
            $key = $key.'_'.$reference;
        }
        if (!empty($decrement_by)){
            \Illuminate\Support\Facades\Cache::decrement($key, $decrement_by);
        }else{
            \Illuminate\Support\Facades\Cache::decrement($key);
        }
    }
    
    public static function store($channel){
        return \Illuminate\Support\Facades\Cache::store($channel);
    }

    public static function setCachePrefix($reference = '', $key='', $cache_prefix = '', $option_prefix = '', $default_prefix = ''): void
    {
        if(!self::isGlobalCache()){
            return;
        }

        $set_global_cache = false;
        if(!empty($reference) && $reference == self::ORDER_STATUS_REFERENCE){
            $set_global_cache = true;
        }
        if(!empty($key) && Arr::isAMemberOf($key, self::CACHE_KEY_REFUND_IN_PROGRESS)){
            $set_global_cache = true;
        }

        if($set_global_cache){
            self::setCachePrefixForRedis($cache_prefix, $option_prefix, $default_prefix);
        }
    }

    public static function getCachePrefix($reference = '', $key = ''): array
    {
        $prefix = [];
        if(!self::isGlobalCache()){
            return $prefix;
        }

        $set_global_cache = false;
        if(!empty($reference) && $reference == self::ORDER_STATUS_REFERENCE){
            $set_global_cache = true;
        }
        if(!empty($key) && Arr::isAMemberOf($key, self::CACHE_KEY_REFUND_IN_PROGRESS)){
            $set_global_cache = true;
        }

        if($set_global_cache){
            $prefix = self::getCachePrefixForRedis();
        }

        return $prefix;
    }

    public static function isGlobalCache(): bool
    {
        $is_global = false;
        if(self::isRedisCache()){
            $is_global = true;
        }
        return $is_global;
    }

    public static function isRedisCache(): bool
    {
        $cache_driver = config()->get('cache.default');
        if($cache_driver == config()->get('cache.stores.redis.driver')){
            return true;
        }
        return false;
    }

    public static function setCachePrefixForRedis($cache_prefix = '', $option_prefix = '', $default_prefix = ''): void
    {
        config()->set('cache.prefix', $cache_prefix);
        config()->set('database.redis.options.prefix', $option_prefix);
        config()->set('database.redis.default.prefix', $default_prefix);

        app()->bind('redis', function ($app) {
            return new RedisManager($app, $app['config']['database.redis.client'], $app['config']['database.redis']);
        });
        app('cache')->forgetDriver(config('cache.default'));
    }

    public static function getCachePrefixForRedis(): array
    {
        return [
            'default_cache_prefix' => config()->get('cache.prefix'),
            'redis_option_cache_prefix' => config()->get('database.redis.options.prefix'),
            'redis_default_cache_prefix' => config()->get('database.redis.default.prefix')
        ];
    }

}