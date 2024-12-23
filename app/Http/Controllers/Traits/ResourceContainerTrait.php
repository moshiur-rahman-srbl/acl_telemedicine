<?php

namespace App\Http\Controllers\Traits;


trait ResourceContainerTrait{

    public function setResource($key,$resource){
        try {

            app()->instance($key,$resource);
            return $resource;
        }catch(\Throwable $ex){

        }
        return null;
    }

    public function getResource($key){
        try{
            if (app()->bound($key)){
                return app($key);
            }

        }catch(\Throwable $ex){
            //dd($ex->getMessage());
        }

        return null;
    }
}
