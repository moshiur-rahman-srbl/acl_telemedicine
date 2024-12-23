<?php

namespace App\Models;

use common\integration\Override\Model\CustomBaseModel as Model;

class ChangePasswordHistory extends Model
{
    protected $fillable = ['user_id', 'passwords'];

    public function getPasswordHistoryByUserId($userid){
        $limit = config('constants.PASSWORD_DENY_LAST_USED');
        return ChangePasswordHistory::where('user_id', $userid)->orderBy('created_at', 'DESC')->limit($limit)->get();
    }

    public function createHistory($user_id,$passwords){
        $changePassword = new ChangePasswordHistory();
        $changePassword->user_id = $user_id;
        $changePassword->passwords = $passwords;

        $changePassword->save();
    }

    public function deleteHistory(array $user_ids)
    {
        return $this->whereNotIn('id', $user_ids)->delete();
    }
}
