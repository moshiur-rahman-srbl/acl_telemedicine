<?php

namespace App\Models;

use App\User;
use common\integration\Brand\Configuration\Backend\BackendAdmin;
use common\integration\BrandConfiguration;
use common\integration\Override\Model\CustomBaseModel as Model;
use Auth;

class Role extends Model
{
    protected $guarded = [];

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public static function insert_entry($data){
        $role = new Role();
        $role->title = $data["title"];
        $role->title_tr = @$data["title_tr"];
        $role->status = 1;
        $role->company_id = $data["company_id"];
        $role->created_by_id = $data["created_by_id"] ?? null;
        $role->save();
        return $role;
    }

    public function getRolesByCompanyId($company_id, $DEFAULT_COMPANY_ID = '', $isArray = true , $is_relation = false){
//        if ($company_id == $DEFAULT_COMPANY_ID) {
//                $selected_pages = $this->all();
//        } else {
//                $selected_pages = $this->where([
//                        'company_id' => $company_id
//                ]);
//        }
        $query = $this->query();
        if ($is_relation) {
            $query->with('createdByUser', 'updatedByUser');
        }

        $selected_pages = $query->where([
            'company_id' => $company_id
        ])->get();

        $column = 'title';
        if(BrandConfiguration::call([BackendAdmin::class, 'allowAccessRoleTurkishVersion'])){
            $column = app()->getLocale() == config()->get('constants.SYSTEM_SUPPORTED_LANGUAGE.0') ? 'title': 'title_tr';
        }

        if ($isArray){
            $rolesArray = [ ];
            foreach ( $selected_pages as $role ) {
                $column_value = $role->{$column};
                if(empty($column_value)){
                    $column_value = $role->title;
                }
                $rolesArray [$role->id] = $column_value;
            }
            return $rolesArray;
        }
        return $selected_pages;

    }

    public function getRoles($company_id = ''){
        if (empty($company_id)){
            $company_id = Auth::user()->company_id;
        }
        $roles = $this->where('company_id',$company_id)->get();

        return $roles;
    }

    public function findByTitleAndCompanyId($title, $company_id){
        return $this->where('title', $title)->where('company_id', $company_id)->first();
    }

    public function findById($id){
        return $this->find($id);
    }

    public function deleteByRolesId($rolesId)
    {
        $this->whereIn('id', $rolesId)
            ->delete();
    }

    public function getAll($select_cols = [])
    {
        $roles = self::query();
        if (!empty($select_cols)) {
            $roles->select($select_cols);
        }
        return $roles->get();
    }
}
