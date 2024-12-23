<?php
	
	namespace common\integration\Traits;
	
	use common\integration\Utility\SqlBuilder;
	
	trait QueryMethodOverride
	{
		protected static function boot()
		{
			parent::boot();
			static::addGlobalScope('customFirst', function ($builder) {
				if (SqlBuilder::isPgsql() && empty($builder->getQuery()->orders)) {
					$builder->orderBy('id', 'asc');
				}
			});
		}
	}