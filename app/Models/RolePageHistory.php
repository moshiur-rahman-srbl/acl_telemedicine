<?php

namespace App\Models;

use App\Utils\CommonFunction;
use common\integration\ManageAccessControl;
use common\integration\Override\Model\CustomBaseModel as Model;
use Illuminate\Support\Carbon;

class RolePageHistory extends Model
{
    protected $table = 'role_page_histories';
    protected $guarded = [];
    const ACTIVE = 1;
    const INACTIVE = 2;

    public function getAllData($role_id = null)
    {
        $query = $this->query();
        if (!empty($role_id)) {
            if (is_array($role_id)) {
                $query->whereIn('role_id', $role_id);
            } else {
                $query->where('role_id', $role_id);
            }
        }
        return $query->get();
    }

    public function insertCheckAndUncheckData($admin_pages, $selectedPageIdList, $authObj, $role_id){
        $check_uncheck_page = ManageAccessControl::getAllCheckUncheckPage($admin_pages, $selectedPageIdList, $authObj, $role_id);
        if(!empty($check_uncheck_page)){
            $this->saveOrUpdateData($check_uncheck_page, $role_id);
        }
    }

    public function saveOrUpdateData($input_data, $role_id)
    {
        $rolePageExportObj = $this->getAllData($role_id);
        if (count($rolePageExportObj) > 0) {
            foreach ($input_data as $data) {
                $exists_data = $rolePageExportObj
                    ->where('role_id', $data['role_id'])
                    ->where('page_id', $data['page_id'])
                    ->first();

                if (!empty($exists_data)) {
                    if ($exists_data->status != $data['status']) {
                        $this->updateData($data, $exists_data->id);

                    }
                } else {
                    $insert_data = $this->prepareSingleData($data);
                    $this->saveData($insert_data);

                }
            }
        } else {
            $prepare_data = $this->prepareMultiInsertData($input_data);
            $this->saveData($prepare_data);
        }
    }

    public function saveData($input_data)
    {
        return self::insert($input_data);
    }

    public function updateData($update_data, $id)
    {
        $column_name = $this->getAllowDisAllowColumn($update_data);
        return $this->query()
            ->where('id', $id)
            ->update([
                'status' => $update_data['status'],
                $column_name => $update_data['created_by_id'],
                'updated_at' => CommonFunction::dateFormat(Carbon::now())
            ]);
    }

    private function prepareMultiInsertData($inputData)
    {
        $data = [];
        if (!empty($inputData)) {
            foreach ($inputData as $key => $rolePage) {
                $data[$key]['role_id'] = $rolePage['role_id'];
                $data[$key]['page_id'] = $rolePage['page_id'];
                $data[$key]['status'] = $rolePage['status'];
                $data[$key]['allowed_by_id'] = $rolePage['status'] == self::ACTIVE ? $rolePage['created_by_id']: null;
                $data[$key]['disallowed_by_id'] = $rolePage['status'] == self::INACTIVE ? $rolePage['created_by_id']: null;
                $data[$key]['created_at'] = CommonFunction::dateFormat(Carbon::now());
            }
        }
        return $data;
    }

    private function prepareSingleData($inputData)
    {
        $data = [];
        if (!empty($inputData)) {
            $column_name = $this->getAllowDisAllowColumn($inputData);
            $data['role_id'] = $inputData['role_id'];
            $data['page_id'] = $inputData['page_id'];
            $data[$column_name] = $inputData['created_by_id'];
            $data['status'] = $inputData['status'];
            $data['created_at'] = CommonFunction::dateFormat(Carbon::now());
        }
        return $data;
    }

    public function getAllowDisAllowColumn($data){
        $column_name = 'allowed_by_id';
        if($data['status'] == self::INACTIVE){
              $column_name = 'disallowed_by_id';
        }
        return $column_name;
    }
}
