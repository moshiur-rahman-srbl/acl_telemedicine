<?php
	
	namespace common\integration\Override\Model;
	
	use common\integration\Traits\ModelActivityTrait;
	use Illuminate\Database\Eloquent\Model;
	
	class CustomBaseModel extends Model
	{
		use ModelActivityTrait;
	}