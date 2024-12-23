<?php


namespace common\integration;



use Illuminate\Cache\FileStore;
use Illuminate\Support\Facades\Redis;

class ManipulateCache
{
    const IS_ACTIVE = true;

    public static function setCacheLock($key, $reference, $time)
    {
        if(self::IS_ACTIVE) {
            $start_time = microtime(true);

            while (GlobalFunction::hasBrandCache($key, $reference)) {

                $end_time = microtime(true);
                $execution_time = $end_time - $start_time;

                if ($execution_time >= $time) {
                    break;
                }
            }

            GlobalFunction::setBrandCache($key, [], $time, $reference);
        }

    }

    public static function unsetCacheLock($key, $reference)
    {

        GlobalFunction::unsetBrandCache($key,$reference);

    }
	
	/*
	 *  @params $cache_key
	 *  CACHE EXPIRED TIME RETURN IN A SEC
	 */
	
	/*
	 * FOR REDIS IF THE $expired_time returns -2 or -1 that means the key is already expired
	 *
	 */

	
	public static function getCacheExpireTime($key): int
	{
		
		$expired_time = 0;
		$cache_driver = config()->get('cache.default');
		
		if($cache_driver == config()->get('cache.stores.file.driver')){
			
			$fs = new class extends FileStore {
				public function __construct()
				{
					parent::__construct(app()->get('files'), config('cache.stores.file.path'));
				}
				
				public function getTTL(string $key): ?int
				{
					return $this->getPayload($key)['time'] ?? null;
				}
			};
			$expired_time = $fs->getTTL($key);
			
			
		}elseif($cache_driver == config()->get('cache.stores.redis.driver')){
			
			$expired_time = Redis::connection()
				->ttl(
				config('cache.prefix') . ':' . $key
			);
		}
		
		return $expired_time > 0 ? $expired_time : 0;
		
	}
	
	public static function isCacheKeyDestroyed ($key): bool
	{
		$status = false;
		if(self::getCacheExpireTime($key) === 0){
			$status = true;
		}
		return $status;
	}

}