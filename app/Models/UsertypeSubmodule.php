<?php

namespace App\Models;

use common\integration\Override\Model\CustomBaseModel as Model;

class UsertypeSubmodule extends Model
{
    protected $fillable = ['user_type_id', 'sub_module_id'];
    public $timestamps = false;

    public function sub_module(){
        return $this->belongsTo('App\Models\SubModule','sub_module_id');
    }

    public function pages(){
        return $this->hasMany(\App\Models\Page::class);
    }
}
