<?php
	
	namespace common\integration\ServiceProvider;
	
	use Illuminate\Support\ServiceProvider;
	use common\integration\Override\Url\UrlGenerator;
	
	class UrlManipulationServiceProvider extends ServiceProvider
	{
		
		/*
		 *
		 *  Override the UrlGenerator Class
		 */
		public function register()
		{
			
			app()->extend('url', function () {
				
				return new UrlGenerator(
					app('router')->getRoutes(),
					request(),
					app('config')->get('app.asset_url')
				);
				
			});
			
		}
		
		public function boot()
		{

		}
		
	}