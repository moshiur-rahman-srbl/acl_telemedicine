<?php

namespace common\integration\ServiceProvider;
use common\integration\Utility\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ServiceProvider;
// use Illuminate\Translation\Translator;
use common\integration\Translator;             
 //use Illuminate\Translation\FileLoader;
use common\integration\FileLoader;
use File;
use common\integration\BrandConfiguration;


class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */


    public function register()
    {
        $this->registerLoader();
        $this->app->singleton('translator', function ($app) {
            
            $loader = $app['translation.loader'];
            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale. So, we'll grab the application
            // configuration so we can easily get both of these values from there.
            $locale = $app['config']['app.locale'];
            
            $trans = new Translator($loader, $locale);
            
            $trans->setFallback($app['config']['app.fallback_locale']);
            return $trans;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }
    protected function registerLoader()
    {
        $this->app->singleton('translation.loader', function ($app) {
	        
	        $document_root = base_path("");
			if(config('constants.defines.PANEL') != BrandConfiguration::PANEL_USER){
				$document_root = base_path("..".DIRECTORY_SEPARATOR);
			}
			
			$common_path = $document_root.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.'views'
				.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR;
			
            $path = 'path.lang';
            $brand_name = $app['config']['brand.name_code'];

            $constant_brand_codes = $app['config']['constants']['BRAND_NAME_CODE_LIST'];
            if($brand_name == $constant_brand_codes['QP_TENANT']){
                $brand_name = $constant_brand_codes['QP'];
            }
            $main_path = $app[$path];
            $folder_path = DIRECTORY_SEPARATOR.$brand_name;

            if(File::isDirectory($app[$path].$folder_path)){
                $main_path = $app[$path].$folder_path;
            }
			
			$load_lang_files = [
				$app[$path],
				$common_path
			];
            
            if($app[$path] != $main_path){
	            
	            $load_lang_files = Arr::merge($load_lang_files, [
		            $main_path,
		            $common_path.$folder_path,
	            ]);
				
            }

            return new FileLoader($app['files'], $load_lang_files);
        
        });
    }
    public function provides()
    {
        return ['translator', 'translation.loader'];
    }
}
