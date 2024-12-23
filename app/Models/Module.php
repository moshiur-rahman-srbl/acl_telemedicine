<?php

namespace App\Models;

use common\integration\Override\Model\CustomBaseModel as Model;

class Module extends Model
{

    protected $fillable = [
        'id', 'name', 'icon', 'sequence'
    ];
    public function getModules(){
        $modules = $this->all();

        return $modules;
    }


    public function submodules(){
       return $this->hasMany(\App\Models\SubModule::class);
    }

    public function pages() {
        return $this->hasMany(\App\Models\Page::class);
    }
}
