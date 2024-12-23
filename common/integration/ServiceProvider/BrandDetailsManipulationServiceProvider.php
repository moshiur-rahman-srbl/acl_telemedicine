<?php

namespace common\integration\ServiceProvider;

use common\integration\Utility\Ip;
use common\integration\Utility\LocalEnvironmentManipulation;
use Illuminate\Support\ServiceProvider;

class BrandDetailsManipulationServiceProvider extends ServiceProvider
{
    public function register()
    {
		/**
		 * THERE HAS SOME ISSUE FOR LANGUAGE ISSUE SO IT IS REGISTERED
		 * ON HERE BECAUSE LARAVEL CALL REGISTER METHOD AT FIRST
		 */
	    if(Ip::isLocal()){
		    list($brand_name, $brand_code) = LocalEnvironmentManipulation::setProjectBrandDetailsForLocalHost();
		    if(!empty($brand_name)){
			    config()->set('brand.name', $brand_name);
		    }
		    if(!empty($brand_code)){
			    config()->set('brand.name_code', $brand_code);
			    config()->set('brand.logo', "assets/brand/{$brand_code}/logo.png");
			    config()->set('brand.logo_2', "assets/brand/{$brand_code}/logo2.png");
			    config()->set('brand.logo_white', "assets/brand/{$brand_code}/logo_white.png");
			    config()->set('brand.favicon', "assets/brand/{$brand_code}/favicon.png");
		    }
	    }
    }

    public function boot(){
		
    }
}