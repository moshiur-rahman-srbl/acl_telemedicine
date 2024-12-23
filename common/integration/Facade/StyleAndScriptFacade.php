<?php
	
	namespace common\integration\Facade;
	
	use Illuminate\Support\Facades\Facade;
	
	class StyleAndScriptFacade extends Facade
	{
		protected static function getFacadeAccessor()
		{
			return 'StyleScript';
		}
	}