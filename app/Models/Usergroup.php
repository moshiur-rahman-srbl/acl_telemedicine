<?php

namespace App\Models;

use App\User;
use common\integration\Utility\SqlBuilder;
use common\integration\Override\Model\CustomBaseModel as Model;


class Usergroup extends Model
{
    protected $fillable = [
        'group_name', 'dashboard_url', 'status', 'company_id'
    ];

    public static function insert_entry($data){
        $usergroup = new Usergroup();
        $usergroup->group_name = $data["group_name"];
        $usergroup->status = 1;
        $usergroup->company_id = $data["company_id"];
        $usergroup->save();
        return $usergroup;
    }

    public function getUsergroupsByCompanyId($company_id, $SUPER_SUPER_ADMIN_ID){
        if ($company_id == $SUPER_SUPER_ADMIN_ID) {
            $usergroup = $this->all();
        } else {
            $usergroup = $this->where([
                'company_id' => $company_id
            ])->get();
        }
        return $usergroup;
    }

    public function getUsergroups(){
        $usergroups = $this->all();

        return $usergroups;
    }

    public function role()
    {
        return $this->belongsToMany('\App\Models\Role', 'usergroup_roles', 'usergroup_id','role_id');
    }

    public function getUsergroupId($id){
        return $this->find($id);
    }

    public function findByNameAndCompanyId($group_name, $company_id){
        return $this->where('group_name', $group_name)->where('company_id', $company_id)->first();
    }

    public function notificationSubcategories()
    {
        return $this->belongsToMany(NotificationSubCategory::class, 'usergroup_notification_subcategories', 'usergroup_id', 'notification_subcategory_id')->withPivot('created_at', 'updated_at');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_usergroups', 'usergroup_id', 'user_id');
    }

    public function getUserGroupData($search)
    {
        $query = self::query();

        if(!empty($search['company_id'])){
            $query->where('company_id', $search['company_id']);
        }

        if (!empty($search['search_key'])) {
            $likeKey = SqlBuilder::likeOperator();
            $query->where(function ($q) use ($search , $likeKey) {
                $q->where('group_name', $likeKey, '%' . $search['search_key'] . '%')
                    ->orWhere('dashboard_url', $likeKey, '%' . $search['search_key'] . '%')
                    ->orWhere('company_id', $likeKey, '%' . $search['search_key'] . '%');
            });
        }

        if (!empty($search['order_by'])) {
            $query->orderBy($search['order_by'], 'ASC');
        }

        if (!empty($search['page_limit'])) {
            return $query->paginate($search['page_limit']);
        } else {
            return $query->get();
        }

    }

    public function createUserGroup($data) {
       return $this->create($data);
    }

    public function updateUserGroup($usergroup, $data) {
        return $usergroup->update($data);
    }

    public function findByIds(array $ids) {
        return $this->whereIn('id', $ids)->orderBy('updated_at','DESC')->first();
    }
}
