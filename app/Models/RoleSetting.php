<?php

namespace App\Models;

use Carbon\Carbon;
use common\integration\Override\Model\CustomBaseModel as Model;

class RoleSetting extends Model
{
    protected $table="role_settings";
    protected $guarded=[];

    public function addOrDeleteSetting($role_id, $is_exist=0) {
        $check = $this->checkIfExistSetting($role_id);
        if($is_exist) {
            if(empty($check)) {
                $this->insert([
                    "role_id" => $role_id,
                    "is_allow_merchant_auth_email" => 1,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()
                ]);
            }
        } else {
            if(!empty($check)) {
                $this->where('role_id', $role_id)->delete();
            }
        }
    }

    private function checkIfExistSetting($role_id) {
        return $this->where('role_id', $role_id)->first();
    }

    public function findByRoleId($role_id) {
        return $this->where('role_id', $role_id)->first();
    }

    public function deleteByRoleId($role_id) {
        return $this->where('role_id', $role_id)->delete();
    }

    public function checkRolePermissionToShowData() {
        $status = false;
        $roles = (new Role())->getRoles();

        if(count($roles) > 0) {
            foreach ($roles as $role) {
                $roleSet = $this->findByRoleId($role->id);
                if(!empty($roleSet) && $roleSet->is_allow_merchant_auth_email) {
                    $status = true;
                    break;
                }
            }
        }

        return $status;
    }

    public function deleteByRolesId($rolesId)
    {
        return $this->whereIn('role_id', $rolesId)->delete();
    }
}
