<?php

namespace App\Models;

use common\integration\Override\Model\CustomBaseModel as Model;

class Page extends Model
{
    protected $fillable = [
        'id', 'module_id', 'sub_module_id', 'name', 'method_name', 'method_type', 'available_to_company'
    ];

    public function getPages(){
        $pages = $this->all();

        return $pages;
    }

    public function submodules(){
        return $this->hasOne(\App\Models\SubModule::class,'id', 'sub_module_id');
    }

    public function modules() {
        return $this->hasOne(\App\Models\Module::class,'id', 'module_id');
    }

    public function getModuleSubmodulePagesByPageIds($db_page_ids,$seleted_page_ids) {

        $processing_page_ids = $this->getAddRemovePageIds($db_page_ids,$seleted_page_ids);
        $merged_page_ids = array_merge($processing_page_ids['add_page_ids'],$processing_page_ids['remove_page_ids']);

        if(empty($merged_page_ids)){
            return [];
        }

        // get role page,module, submodule associations from DB
        $relations = [
            'modules' => fn($query) => $query->select('id','name'),
            'submodules' => fn($query) => $query->select('id','name')
        ];

        $pageAssocObj = Page::with($relations)
                            ->select('id','name','module_id','sub_module_id')
                            ->whereIn('id',$merged_page_ids)
                            ->orderBy('created_at', 'ASC')
                            ->get();

        return [
            'added' => $pageAssocObj->whereIn('id',$processing_page_ids['add_page_ids'])->toArray(),
            'removed' => $pageAssocObj->whereIn('id',$processing_page_ids['remove_page_ids'])->toArray()
        ];

    }

    public function getAddRemovePageIds($db_page_ids,$seleted_page_ids) {
        // new added page ids
        $add_page_ids = [];
        if(!empty($seleted_page_ids)){
            foreach ($seleted_page_ids as $seleted_page_id) {
                if(!in_array($seleted_page_id, $db_page_ids)){
                    $add_page_ids[] = $seleted_page_id;
                }
            }
        }

        // remove page ids
        $remove_page_ids = [];
        if(!empty($db_page_ids)){
            foreach ($db_page_ids as $db_page_id) {
                if(!in_array($db_page_id, $seleted_page_ids)){
                    $remove_page_ids[] = $db_page_id;
                }
            }
        }

        return array('add_page_ids'=>$add_page_ids,'remove_page_ids'=>$remove_page_ids);
    }



}
