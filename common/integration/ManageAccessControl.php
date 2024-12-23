<?php

namespace common\integration;

use App\Models\Role;
use App\Models\RolePage;
use App\Models\RolePageHistory;
use App\Utils\CommonFunction;
use common\integration\Brand\Configuration\Backend\BackendAdmin;

class ManageAccessControl
{

    public static function prepareDataRolePage($userObj)
    {
        $header = ManageAccessControl::rolePageHeader();
        $DEFAULT_COMPANY_ID = 1;
        $roleList = (new Role())->getRolesByCompanyId($userObj->company_id, $DEFAULT_COMPANY_ID, false, true);
        $roleIds = ManageAccessControl::getFormatedRoleIds($roleList);
        $rolePageExportData = (new RolePageHistory())->getAllData($roleIds);
        $data = [];
        $title = [];
        $previous_role_id = 0;
	    
	    $column = 'title';
	    if(BrandConfiguration::call([BackendAdmin::class, 'allowAccessRoleTurkishVersion'])){
		    $column = app()->getLocale() == config()->get('constants.SYSTEM_SUPPORTED_LANGUAGE.0') ? 'title': 'title_tr';
	    }
		
        foreach ($roleList as $role) {
			
	        $column_value = $role->{$column};
	        if(empty($column_value)){
		        $column_value = $role->title;
	        }

            $title[$role->id] = $column_value;
            $temp_data = [];
            $response = (new RolePage())->getSelectedpages($role->id, $userObj);

            $modulesObj = $response['modulesInfo'];
            $selectedpages = $response['selectedpages'];
            if ($role->id != $previous_role_id) {
                $previous_role_id = $role->id;
            } else {
                $previous_role_id = 0;
            }

            $temp_data[] = [
                'Role Name' => isset($role) ? $role->{$cloumn} : '-',
                'ID' => '',
                'Module' => '',
                'Sub Module' => '',
                'Pages' => '',
                'Action' => "",
                'Created At' => "",
                'Updated At' => '',
                'Creator Name' => isset($role->createdByUser) ? $role->createdByUser->name : '-',
                'Approver Name' => isset($role->updatedByUser) ? $role->updatedByUser->name : '-'
            ];

            $previous_module_id = 0;
            foreach ($modulesObj as $module) {

                $temp_data[] = [
                    'Role' => "",
                    'ID' => ($module->id != $previous_module_id) ? $module->id : '',
                    'Module' => ($module->id != $previous_module_id) ?
	                    \common\integration\GlobalFunction::nameCaseConversion(__($module->name)) : '',
                    'Sub Module' => '',
                    'Pages' => '',
                ];

                if ($module->id != $previous_module_id) {
                    $previous_module_id = $module->id;
                } else {
                    $previous_module_id = 0;
                }
                $previous_sub_module_id = 0;
                foreach ($module->submodules as $submodule) {

                    $temp_data[] = [
                        'Role' => "",
                        'ID' => ($module->id != $previous_module_id) ? $module->id : '',
                        'Module' => ($module->id != $previous_module_id) ?
	                        \common\integration\GlobalFunction::nameCaseConversion(__($module->name)) : '',
                        'Sub Module' => ($submodule->id != $previous_sub_module_id) ?
	                        \common\integration\GlobalFunction::nameCaseConversion(__($submodule->name)) : '',
                        'Pages' => '',
                        'Action' => '',
                    ];

                    if ($submodule->id != $previous_sub_module_id) {
                        $previous_sub_module_id = $submodule->id;
                    } else {
                        $previous_sub_module_id = 0;
                    }

                    foreach ($submodule->pages as $page) {
                        $checked = __('Not Allow');
                        if (in_array($page->id, $selectedpages)) {
                            $checked = __("Allow");
                        }
                        $rolePageHistoryObj = $rolePageExportData->where('role_id', $role->id)->where('page_id', $page->id)->first();

                        $temp_data[] = [
                            'Role' => "",
                            'ID' => ($module->id != $previous_module_id) ? $module->id : '',
                            'Module' => ($module->id != $previous_module_id) ?
	                            \common\integration\GlobalFunction::nameCaseConversion(__($module->name)) : '',
                            'Sub Module' => ($submodule->id != $previous_sub_module_id) ?
	                            \common\integration\GlobalFunction::nameCaseConversion(__($submodule->name)) : '',
                            'Pages' => \common\integration\GlobalFunction::nameCaseConversion(__($page->name)),
                            'Action' => $checked,
                            'Created At' => !empty($rolePageHistoryObj) ? CommonFunction::dateFormat($rolePageHistoryObj['created_at']) : '',
                            'Updated At' => !empty($rolePageHistoryObj) ? CommonFunction::dateFormat($rolePageHistoryObj['updated_at']) : '',
                        ];

                    }
                }
            }
            $data[$role->id] = $temp_data;
        }

        return [$data, $title, $header];
    }

    public static function rolePageHeader()
    {
        $header = [
            __('Role Name'),
            __('ID'),
            __('Module'),
            __('Sub Module'),
            __('Pages'),
            __('Permission'),
            __('Created At'),
            __('Updated At'),
            __('Creator Name'),
            __('Approver Name'),
        ];
        return $header;
    }


    public static function getFormatedRoleIds($rolePageHistoryObj){
        $role_ids = [];
        if(!empty($rolePageHistoryObj)){
             foreach ($rolePageHistoryObj as $role ) {
                $role_ids[] = $role->id;
            }
        }
        return $role_ids;
    }

    public static function getAllCheckUncheckPage($admin_pages, $selectedPageIdList, $authObj, $role_id)
    {
        $export_insert_data = [];
        $check_datas = [];
        $uncheck_datas = [];

        foreach ($admin_pages as $page_id) {
            if (in_array($page_id, $selectedPageIdList)) {
                $check_datas[] = $page_id;
            } else {
                $uncheck_datas[] = $page_id;
            }
        }

        if (!empty($check_datas)) {
            $check_datas_unique = array_unique($check_datas);
            foreach ($check_datas_unique as $page_id) {
                $export_insert_data[] = [
                    'role_id' => $role_id,
                    'page_id' => $page_id,
                    'status' => RolePageHistory::ACTIVE,
                    'created_by_id' => $authObj->id,
                ];
            }
        }

        if (!empty($uncheck_datas)) {
            $uncheck_datas_unique = array_unique($uncheck_datas);
            foreach ($uncheck_datas_unique as $page_id) {
                $export_insert_data[] = [
                    'role_id' => $role_id,
                    'page_id' => $page_id,
                    'status' => RolePageHistory::INACTIVE,
                    'created_by_id' => $authObj->id,
                ];
            }
        }

        return $export_insert_data;
    }

}