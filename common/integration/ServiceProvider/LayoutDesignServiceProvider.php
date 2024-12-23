<?php
	
	namespace common\integration\ServiceProvider;
	
	use common\integration\Design\StyleAndScript;
	use Illuminate\Support\ServiceProvider;
	
	class LayoutDesignServiceProvider extends ServiceProvider
	{
		public function register()
		{
			$this->app->singleton('StyleScript',function(){
				return new StyleAndScript();
			});
			
		}
	}