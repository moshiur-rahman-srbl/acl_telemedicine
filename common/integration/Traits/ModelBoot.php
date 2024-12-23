<?php


namespace common\integration\Traits;

use common\integration\BrandCacheConfiguration;
use common\integration\GlobalFunction;


trait ModelBoot
{
    public static function boot() {

        parent::boot();

        static::created(function(){   
            (new Self())->setModelCacheData(true);
        });

        static::updated(function(){  
            (new Self())->setModelCacheData(true);
        });
        
        static::deleted(function(){   
            (new Self())->setModelCacheData(true);         
        });

    }

    public function setModelCacheData($unset_key = false){
        if(BrandCacheConfiguration::$is_enable_brand_cache){

            if($unset_key){
                GlobalFunction::unsetBrandCache($this->cache_key);
            }
            return BrandCacheConfiguration::cacheForEver($this->cache_key, function(){
                return Self::get();
            });

        }
    } 
    
}