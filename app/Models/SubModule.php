<?php

namespace App\Models;

use common\integration\Override\Model\CustomBaseModel as Model;

class SubModule extends Model
{

    protected $fillable = [
        'id', 'module_id','name', 'icon', 'sequence','controller_name','favicon_path','default_method'
    ];

    public function pages()
    {
        return $this->hasMany(\App\Models\Page::class);
    }

    public function modules() {
        return $this->hasOne(\App\Models\Module::class,'id', 'module_id');
    }
    public function getSubModules(){
        $submodules = $this->all();

        return $submodules;
    }

    public function getPages(){
        return $this->belongsTo('\App\Models\Page', 'sub_module_id');
    }

    public function usertype_submodules(){
        return $this->hasMany(UsertypeSubmodule::class);
    }

    public function insert_entry($input){
        $this->id = $input['submodule_id'];
        $this->module_id = $input['module_id'];
        $this->name = $input['submodule_name'];
        $this->icon = $input['submodule_icon'];
        $this->sequence = $input['sequence'];
        $this->controller_name = $input['controller_name'];
        $this->default_method = $input['default_method'];
        $this->save();
        return $this;
    }

}
