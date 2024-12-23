<?php

namespace common\integration\AccessControl;

use App\Http\Controllers\Traits\PermissionUpdateTreait;
use App\Models\Module;
use App\Models\Profile;
use App\Models\Role;
use App\Models\RolePage;
use App\Models\RoleSetting;
use App\Models\Usergroup;
use App\Models\UsergroupRole;
use App\Models\UserUsergroup;
use App\User;
use common\integration\ApiService;
use common\integration\AppRequestValidation;
use common\integration\Brand\Configuration\Backend\BackendAdmin;
use common\integration\BrandConfiguration;
use common\integration\ManageLogging;
use common\integration\Utility\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserRolePermissionService
{
    use PermissionUpdateTreait;

    const ADD_ROLE = 'add_role';
    const EDIT_ROLE = 'edit_role';
    const ADD_USER_GROUP = 'add_usergroup';
    const EDIT_USER_GROUP = 'edit_usergroup';
    const GET_USER_GROUP = 'get_usergroup';

    public function getAccessControlData($id)
    {

        $user = (new Profile())->getUserById($id);
        $data = [];

        if (empty($user)) {
            $status_code = ApiService::API_SERVICE_USER_NOT_FOUND;
            $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_USER_NOT_FOUND];
        } else {
            $data['saler_id'] = $id;
            $company_id = $user->company_id;
            $role = new Role();
            $data['roles'] = $role->getRolesByCompanyId($company_id, '', false);

            $rolesArray = [];

            $column = 'title';
            if (BrandConfiguration::call([BackendAdmin::class, 'allowAccessRoleTurkishVersion'])) {
                $column = app()->getLocale() == config()->get('constants.SYSTEM_SUPPORTED_LANGUAGE.0') ? 'title' : 'title_tr';
            }
            foreach ($data['roles'] as $role) {
                $column_value = $role->{$column};
                if (empty($column_value)) {
                    $column_value = $role->title;
                }
                $rolesArray [$role->id] = $column_value;
            }
            $data['roleList'] = $rolesArray;

            $usergroupobj = new Usergroup();
            $data['user_groups'] = $usergroupobj->getUsergroupsByCompanyId($user->company_id, $user->id);
            $data['user'] = $user;
            // $data['merchant_users'] = $data['merchant']->getUsersofMerchant($id);
            $data['users'] = User::with('userGroup', 'userGroup.role')->where('merchant_parent_user_id', $id)->get();


            $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
            $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_SUCCESS_CODE];

        }

        return [$status_code, $status_message, $data];
    }

    public function processRoleManagementData($input, $company_id, $role_id)
    {
        try {
            $exception_message = '';
            [$status_code, $status_message] = $this->validateRoleManagementData($input, $company_id);

            if (empty($status_code)) {
                DB::beginTransaction();

                $data = $this->prepareRoleManagementData($input, $company_id);

                if ($input->action == UserRolePermissionService::ADD_ROLE) {
                    [$status_code, $status_message] = $this->addRole($data);
                } elseif ($input->action == UserRolePermissionService::EDIT_ROLE) {
                    [$status_code, $status_message] = $this->editRole($data, $role_id);
                }

                DB::commit();
            }

        } catch (\Throwable $exception) {
            DB::rollBack();
            $status_code = ApiService::API_SERVICE_UNKNOWN_ERROR;
            $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_UNKNOWN_ERROR];
            $exception_message = $exception->getMessage() . ' ' . $exception->getFile() . ' ' . $exception->getLine();

        }

        $log_data['action'] = 'STORE_ROLE_MANAGEMENT';
        $log_data['request_data'] = $input;
        $log_data['status_code'] = $status_code;
        $log_data['status_message'] = $status_message;
        $log_data['exception_message'] = $exception_message;
        (new ManageLogging())->createLog($log_data);

        return [$status_code, $status_message];
    }

    public function validateRoleManagementData($input, $company_id)
    {
        $status_code = $status_message = '';
        [$rules, $messages] = AppRequestValidation::roleManagementValidation($input, $company_id);
        $validator = Validator::make($input->all(), $rules, $messages);

        if ($validator->fails()) {
            $status_code = ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR;
            $status_message = $validator->errors()->first();
        }

        return [$status_code, $status_message];
    }

    public function prepareRoleManagementData($input, $company_id)
    {
        $data = [
            "title" => $input->title,
            'company_id' => $company_id
        ];

        if ($input->action == UserRolePermissionService::ADD_ROLE) {
            $data = Arr::merge($data, [
                'is_allow_merchant_auth_email' => isset($input->is_allow_merchant_auth_email) ? $input->is_allow_merchant_auth_email : 0
            ]);
        }

        if (BrandConfiguration::call([BackendAdmin::class, 'allowAccessRoleTurkishVersion'])) {
            $data = Arr::merge($data, [
                'title_tr' => $input->title_tr
            ]);
        }
        return $data;
    }

    public function addRole($data)
    {
        $roleAdd = Role::insert_entry($data);

        if ($roleAdd) {
            (new RoleSetting())->addOrDeleteSetting($roleAdd->id, $data['is_allow_merchant_auth_email']);
        }
        $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
        $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_SUCCESS_CODE];

        return [$status_code, $status_message];
    }

    public function editRole($data, $role_id)
    {
        $role = new Role();
        $roleObj = $role->findById($role_id);
        if (!empty($roleObj)) {
            if ($roleObj->company_id != $data['company_id']) {
                $status_code = ApiService::API_SERVICE_UNKNOWN_ERROR;
                $status_message = __('Invalid role!');
            } else {
                $roleObj->update($data);
                $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
                $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_SUCCESS_CODE];
            }
        } else {
            $status_code = ApiService::API_SERVICE_UNKNOWN_ERROR;
            $status_message = __('Role not found');
        }

        return [$status_code, $status_message];
    }

    public function processUserGroupManagementData($input, $id, $usergroup_id)
    {
        $user = (new Profile())->getUserById($id);
        if (!empty($user)) {
            try {
                $exception_message = '';
                [$status_code, $status_message] = $this->validateUserGroupManagementData($input);

                if (empty($status_code)) {
                    DB::beginTransaction();

                    if ($input->action == UserRolePermissionService::ADD_USER_GROUP) {
                        [$status_code, $status_message] = $this->addUserGroup($input, $user->company_id);
                    } elseif ($input->action == UserRolePermissionService::EDIT_USER_GROUP) {
                        [$status_code, $status_message] = $this->editUserGroup($input, $user->company_id, $usergroup_id);
                    }

                    DB::commit();
                }

            } catch (\Throwable $exception) {
                DB::rollBack();
                $status_code = ApiService::API_SERVICE_UNKNOWN_ERROR;
                $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_UNKNOWN_ERROR];
                $exception_message = $exception->getMessage() . ' ' . $exception->getFile() . ' ' . $exception->getLine();

            }

            $log_data['action'] = 'STORE_USER_GROUP_MANAGEMENT';
            $log_data['request_data'] = $input;
            $log_data['status_code'] = $status_code;
            $log_data['status_message'] = $status_message;
            $log_data['exception_message'] = $exception_message;
            (new ManageLogging())->createLog($log_data);
        } else {
            $status_code = ApiService::API_SERVICE_USER_NOT_FOUND;
            $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_USER_NOT_FOUND];
        }

        return [$status_code, $status_message];
    }

    public function validateUserGroupManagementData($input)
    {
        $status_code = $status_message = '';
        [$rules, $messages] = AppRequestValidation::userGroupManagementValidation();
        $validator = Validator::make($input->all(), $rules, $messages);

        if ($validator->fails()) {
            $status_code = ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR;
            $status_message = $validator->errors()->first();
        }

        return [$status_code, $status_message];
    }

    public function addUserGroup($data, $company_id)
    {
        $user_group_data = [
            'group_name' => $data->group_name,
            'dashboard_url' => $data->dashboard_url,
            'status' => 1,
            'company_id' => $company_id
        ];

        $user_group_add = (new Usergroup())->createUserGroup($user_group_data);

        if ($user_group_add) {
            if ($data->selected_users) {
                $user_user_group_data = [];
                for ($i = 0; $i < sizeof($data->selected_users); $i++) {
                    $user_user_group_data[] = [
                        'usergroup_id' => $user_group_add->id,
                        'user_id' => $data->selected_users[$i],
                        'company_id' => $company_id
                    ];
                }
                if (Arr::count($user_user_group_data) > 0) {
                    (new UserUsergroup())->insertUserGroup($user_user_group_data);
                }
            }
            $status_code = ApiService::API_SERVICE_SUCCESS_CODE;;
            $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_SUCCESS_CODE];
        } else {
            $status_code = ApiService::API_SERVICE_FAILED_CODE;;
            $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_FAILED_CODE];
        }
        return [$status_code, $status_message];
    }

    public function editUserGroup($data, $company_id, $usergroup_id)
    {
        $user_group = new Usergroup();
        $usergroup = $user_group->getUsergroupId($usergroup_id);
        $edited_data = [
            'group_name' => $data->group_name,
            'dashboard_url' => $data->dashboard_url
        ];

        $usergroupEdit = $user_group->updateUserGroup($usergroup, $edited_data);

        $userusergroup = new UserUsergroup();
        $userusergroup->deleteByUserGroupId($usergroup_id);

        if ($usergroupEdit) {

            if ($data->selected_users) {
                $user_user_group_data = [];
                for ($i = 0; $i < sizeof($data->selected_users); $i++) {
                    $user_user_group_data[] = [
                        'usergroup_id' => $usergroup_id,
                        'user_id' => $data->selected_users[$i],
                        'company_id' => $company_id
                    ];
                }
                if (Arr::count($user_user_group_data) > 0) {
                    (new UserUsergroup())->insertUserGroup($user_user_group_data);
                }
            }

            if (BrandConfiguration::isAllowDataModificationNotification()) {
                $archiveData = ['Action' => 'Access Control Update Archive', ['old_data' => $usergroup, 'new_data' => $data->all()]];
                (new ManageLogging())->createLog($archiveData);
            }
        }
        $status_code = ApiService::API_SERVICE_SUCCESS_CODE;;
        $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_SUCCESS_CODE];
        return [$status_code, $status_message];
    }

    public function getUserGroupManagementData($usergroup_id, $id)
    {
        $user = (new Profile())->getUserById($id);
        $data = [];
        if (!empty($user)) {
            $data['usergroup'] = (new Usergroup())->getUsergroupId($usergroup_id);
            $data['users'] = (new User())->getUserByCompanyId($user->company_id);
            $data['userusergroups'] = (new UserUsergroup())->getByUserGroupAndCompanyId($usergroup_id, $user->company_id);
            $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
            $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_SUCCESS_CODE];
        } else {
            $status_code = ApiService::API_SERVICE_USER_NOT_FOUND;
            $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_USER_NOT_FOUND];
        }

        return [$status_code, $status_message, $data];
    }

    public function processUserGroupRoleAssociationData($input, $id)
    {
        $user = (new Profile())->getUserById($id);
        if ($user) {
            try {
                $exception_message = '';
                DB::beginTransaction();

                $userGroupRole = new UsergroupRole();
                $usergroup_id = $input->usergroup_id;
                $user_group_details = $userGroupRole->getDetailsByUserGroupIds($usergroup_id);

                if (!empty($user_group_details)) {
                    $userGroupRole->deleteByIds($user_group_details->pluck('id')->toArray());
                }

                $insert_user_role_data = [];
                if ($input->selected_roles) {
                    for ($i = 0; $i < sizeof($input->selected_roles); $i++) {
                        $insert_user_role_data[] = [
                            'usergroup_id' => $usergroup_id,
                            'role_id' => $input->selected_roles[$i],
                        ];
                    }

                    if (!empty($insert_user_role_data)) {
                        $userGroupRole->insertData($insert_user_role_data);
                    }
                }

                $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
                $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_SUCCESS_CODE];

                DB::commit();

            } catch (\Throwable $exception) {
                DB::rollBack();
                $status_code = ApiService::API_SERVICE_UNKNOWN_ERROR;
                $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_UNKNOWN_ERROR];
                $exception_message = $exception->getMessage() . ' ' . $exception->getFile() . ' ' . $exception->getLine();

            }

            $log_data['action'] = 'USERGROUP_ROLE_ASSOCIATION_CHANGED';
            $log_data['request_data'] = $input;
            $log_data['status_code'] = $status_code;
            $log_data['status_message'] = $status_message;
            $log_data['exception_message'] = $exception_message;
            (new ManageLogging())->createLog($log_data);
        } else {
            $status_code = ApiService::API_SERVICE_USER_NOT_FOUND;
            $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_USER_NOT_FOUND];
        }


        return [$status_code, $status_message];
    }

    public function getUserGroupRoleData($usergroup_id, $id)
    {
        $data = [];
        if (!empty($usergroup_id)) {
            $user = (new Profile())->getUserById($id);

            if ($user) {
                $role = new Role();
                $data['roles'] = $role->getRoles($user->company_id);
                $data['usergrouproles'] = UsergroupRole::where('usergroup_id', '=', $usergroup_id)->get();
                $status_code = ApiService::API_SERVICE_SUCCESS_CODE;
                $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_SUCCESS_CODE];
            } else {
                $status_code = ApiService::API_SERVICE_USER_NOT_FOUND;
                $status_message = ApiService::API_SERVICE_STATUS_MESSAGE[ApiService::API_SERVICE_USER_NOT_FOUND];
            }
        } else {
            $status_code = ApiService::API_SERVICE_FAILED_CODE;
            $status_message = __('Please select a User Group');
        }

        return [$status_code, $status_message, $data];
    }

    public function getSelectedpages($id, $user, $hide_page_permission_from_merchant_area = false)
    {
        $SUPER_SUPER_ADMIN_ID = 1;
        $roles = array();
        $selectedpages = array();
        $submoduleSelectedList = array();

        list($hide_module_ids, $hide_sub_module_ids, $hide_pages_ids) = $this->hideModulesSubModules(true, $hide_page_permission_from_merchant_area);
        $usertype_submodule_list = Module::join('sub_modules', 'sub_modules.module_id', 'modules.id')
            ->join('usertype_submodules', 'usertype_submodules.sub_module_id', 'sub_modules.id')
            ->where('usertype_submodules.user_type_id', $user->user_type)
            ->whereNotIn('modules.id', $hide_module_ids)
            ->whereNotIn('sub_modules.id', $hide_sub_module_ids)
            ->select('modules.id as module_id',
                'sub_modules.id as sub_module_id'
            )->get();

        $module_ids_array = $usertype_submodule_list->unique('module_id')->pluck('module_id')->toArray();
        $usertype_submodule_ids_array = $usertype_submodule_list->unique('sub_module_id')->pluck('sub_module_id')->toArray();

        $modulesInfo = Module::with(['submodules.pages' => function ($query) use ($hide_pages_ids) {
            $query->whereNotIn('id', $hide_pages_ids);
        }])->with(['submodules' => function ($query) use ($usertype_submodule_ids_array) {
            $query->whereIn('id', $usertype_submodule_ids_array)->orderBy('sequence', 'ASC');
        }])->whereIn('id', $module_ids_array);

        if ($user->id == $SUPER_SUPER_ADMIN_ID) {
            $modulesInfo = $modulesInfo->whereNotIn('id', [1001, 1002]);
        } else {
            $modulesInfo = $modulesInfo->whereNotIn('id', [1001, 1002, 1007]);
        }

        $modulesInfo = $modulesInfo->get();

        if ($id > 0) {
            $roles = RolePage::where('role_id', $id)->get();
        }

        if (!empty($roles)) {
            foreach ($roles as $role) {
                $selectedpages[] = $role->page_id;
            }
        }

        $modulesArray = [];
        $moduleAssocSubmodule = [];
        if (!empty($modulesInfo)) {
            foreach ($modulesInfo as $moduleInfo) {
                if (in_array($moduleInfo->id, $module_ids_array)) {
                    $modulesArray [$moduleInfo->id] = $moduleInfo->name;
                }
                if (!empty($moduleInfo->submodules)) {
                    foreach ($moduleInfo->submodules as $submodules) {
                        $moduleAssocSubmodule[$moduleInfo->id][] = $submodules->id;
                        if (!empty($submodules)) {
                            $submoduleselected = 0;
                            foreach ($submodules->pages as $page) {
                                if (in_array($page->id, $selectedpages)) {
                                    $submoduleselected++;
                                }
                            }
                            $submoduleSelectedList[$submodules->id] = 0;
                            if (count($submodules->pages) == $submoduleselected) {
                                $submoduleSelectedList[$submodules->id] = 1;
                            }
                        }
                    }

                }
            }
        }
        $selectedModuleList = [];
        if (!empty($moduleAssocSubmodule)) {
            foreach ($moduleAssocSubmodule as $module_id => $submoduleList) {
                $selectedModuleList[$module_id] = 0;
                if (!empty($submoduleList)) {
                    $selectedmodule = 0;
                    foreach ($submoduleList as $submodule_id) {
                        if ($submoduleSelectedList[$submodule_id] == 1) {
                            $selectedmodule++;
                        }
                    }

                    if (count($submoduleList) == $selectedmodule) {
                        $selectedModuleList[$module_id] = 1;
                    }
                }
            }
        }


        $response['modulesArray'] = $modulesArray;
        $response['modulesInfo'] = $modulesInfo;
        $response['module_ids_array'] = $module_ids_array;
        $response['usertype_submodule_ids_array'] = $usertype_submodule_ids_array;
        $response['selectedpages'] = $selectedpages;
        $response['submoduleSelectedList'] = $submoduleSelectedList;
        $response['selectedModuleList'] = $selectedModuleList;

        return $response;
    }

}