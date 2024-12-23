<?php

namespace common\integration\Utility;

class LocalEnvironmentManipulation
{

    // ON LOCAL YOU CAN SET BRAND NAME AND BRAND CODE IT WILL AUTOMATICALLY SET THE BRANDCODE AND BRAND NAME

    public static function setProjectBrandDetailsForLocalHost(){

        $brand_name = config('brand.name');
        $brand_code = config('brand.name_code');

        return [
            $brand_name,
            "SP"
        ];
    }
}
