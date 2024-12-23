<?php

namespace App\Models;

use common\integration\Override\Model\CustomBaseModel as Model;

class UserUserGroupSetting extends Model
{
    protected $table = "user_usergroup_settings";
    protected $guarded = [];


    public function insertOrUpdate($input_data)
    {
        $id = $input_data['user_id'];
        $user_group_setting = self::query()->where('user_id',$id)->first();
        if(!empty($user_group_setting)){
//            if(empty($input_data["is_allow_direct_export"]) || $input_data["is_allow_direct_export"] == 0){
//                $user_group_setting->delete();
//            }else{
                $user_group_setting->update($input_data);
//            }
        }else{
//            if(empty($input_data["is_allow_direct_export"]) || $input_data["is_allow_direct_export"] == 0) {
//
//            }else{
                self::query()->create($input_data);
//            }
        }
    }

    public function findByUserId($id) {
        return $this->where('user_id', $id)->first();
    }


}
