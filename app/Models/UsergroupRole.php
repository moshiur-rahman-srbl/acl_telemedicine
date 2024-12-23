<?php

namespace App\Models;

use common\integration\Override\Model\CustomBaseModel as Model;

class UsergroupRole extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'id', 'usergroup_id', 'role_id'
    ];

    public function role()
    {
        return $this->belongsToMany('\App\Models\Role', 'usergroup_roles', 'usergroup_id','role_id');
    }

    public function getUsergroupByRoleCompany($role_id, $company_id)
    {
        return Self::where('role_id', $role_id)->where('company_id', $company_id)->first();
    }

    public function insertUsergroupRole($role_id, $company_id, $usergroup_id)
    {
        $usergroup_role = new UsergroupRole();
        $usergroup_role->usergroup_id = $usergroup_id;
        $usergroup_role->role_id = $role_id;
        $usergroup_role->company_id = $company_id;
        $usergroup_role->save();

        return $usergroup_role;
    }

    public function insertData($data){
        return $this->insert($data);
    }

    public function getDetailsByUserGroupIds($usergroup_id){

        $query = $this->query();

        if(is_array($usergroup_id)){
            $query->whereIn('usergroup_id',$usergroup_id);
        }else{
            $query->where('usergroup_id',$usergroup_id);
        }

        return $query->get();
    }

    public function deleteByIds($ids){
        return $this->destroy($ids);
    }
}
