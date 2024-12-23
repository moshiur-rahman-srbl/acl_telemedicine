<?php

namespace App\Models;

use common\integration\Brand\Configuration\All\Mix;
use common\integration\BrandCacheConfiguration;
use common\integration\BrandConfiguration;
use common\integration\ManageAccessControl;
use common\integration\Utility\Cache;
use common\integration\Utility\SqlBuilder;
use common\integration\Utility\Str;
use common\integration\Override\Model\CustomBaseModel as Model;

use Auth;
use App\Models\Role;
use App\Models\Module;
use App\Models\UsergroupRole;
use App\Models\Usergroup;
use App\Models\UserUsergroup;
use App\User;
use DB;
use App\Models\UsertypeSubmodule;
use App\Http\Controllers\Traits\PermissionUpdateTreait;
use common\integration\ManageLogging;

class RolePage extends Model
{
    use PermissionUpdateTreait;
    public $timestamps = false;
    public $permission_modification_info = [];

    public const ADMIN_PERMISSION_CACHE_KEY = 'ADMIN_PERMISSION_CACHE_KEY';

    public function getSelectedpages($id, $user, $hide_page_permission_from_merchant_area = false){
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
            }])
            ->whereIn('id', $module_ids_array);
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
        if(!empty($modulesInfo)){
            foreach ($modulesInfo as $moduleInfo) {
                if (in_array($moduleInfo->id, $module_ids_array)){
                    $modulesArray [$moduleInfo->id] = $moduleInfo->name;
                }
                if(!empty($moduleInfo->submodules)){
                    foreach ($moduleInfo->submodules as $submodules){
                        $moduleAssocSubmodule[$moduleInfo->id][] = $submodules->id;
                        if(!empty($submodules)){
                            $submoduleselected = 0;
                            foreach ($submodules->pages as $page){
                                if(in_array($page->id, $selectedpages)){
                                    $submoduleselected++;
                                }
                            }
                            $submoduleSelectedList[$submodules->id] = 0;
                            if(count($submodules->pages) == $submoduleselected){
                                $submoduleSelectedList[$submodules->id] = 1;
                            }
                        }
                    }

                }
            }
        }
        $selectedModuleList = [];
        if(!empty($moduleAssocSubmodule)){
            foreach ($moduleAssocSubmodule as $module_id => $submoduleList){
                $selectedModuleList[$module_id] = 0;
                if(!empty($submoduleList)){
                    $selectedmodule = 0;
                    foreach($submoduleList as $submodule_id){
                        if($submoduleSelectedList[$submodule_id] == 1){
                            $selectedmodule++;
                        }
                    }

                    if(count($submoduleList) == $selectedmodule){
                        $selectedModuleList[$module_id] = 1;
                    }
                }
            }
        }


        $response['modulesInfo'] = $modulesInfo;
        $dropdown_contents = view('RolePages.moduleDropdownList', compact('modulesArray'))->render();
        $role_contents = view('RolePages.rolepagecontent', compact('modulesInfo', 'module_ids_array', 'usertype_submodule_ids_array', 'selectedpages','submoduleSelectedList','selectedModuleList'))->render();
        $response['moduledropdown'] = $dropdown_contents;
        $response['role_content'] = $role_contents;
        $response['selectedpages'] = $selectedpages;
        $response['selectedModuleList'] = $selectedModuleList;

        return $response;
    }

    public function associateRolePage($request, $user_type , $authObj){
        $status_code = '';
        $status_message = '';
        $selectedPageIdList = $request->selectedpageIds;
        $role_id = $request->role_id;
        $module_id = $request->module_id;

        if($role_id > 0 && !empty($selectedPageIdList)){
            $insertData = array();

            $admin_pages = [];
            $usertype_submodules = UsertypeSubmodule::with('sub_module')->where('user_type_id', $user_type)->get();
            foreach ($usertype_submodules as $sub_module) {
                if (!empty($sub_module->sub_module)) {
                    $all_pages = $sub_module->sub_module->pages;
                    if (!empty($all_pages)) {
                        foreach($all_pages as $page) {
                            $admin_pages[] = $page->id;
                        }
                    }
                }
            }


            foreach ($selectedPageIdList as $page_id){
                if (in_array($page_id, $admin_pages)) {
                    $insertData[] = ['role_id'=>$role_id,'page_id'=>$page_id];
                }
            }



            try{
                DB::beginTransaction();

                $roleObj = RolePage::where('role_id', $role_id);
                $db_role_pages = $roleObj->pluck('page_id')->toArray();
                $roleObj->delete();
                RolePage::insert($insertData);

                if (BrandConfiguration::allowRolePageAllRoleExport()) {
                    (new RolePageHistory())->insertCheckAndUncheckData($admin_pages, $selectedPageIdList, $authObj, $role_id);
                }

                $usergrouproles = UsergroupRole::where('role_id',$role_id)->get();
                $userGroupIds = [];

                if(!empty($usergrouproles)){
                    foreach ($usergrouproles as $usergrouprole){
                        $userGroupIds[] = $usergrouprole->usergroup_id;
                    }

                    $userGroupIds = array_unique($userGroupIds);
                    $usergroups = [];
                    if(!empty($userGroupIds)){
                        $usergroups = UserUsergroup::whereIn('usergroup_id',$userGroupIds)->get();
                    }
                    $userIds = [];
                    if(!empty($usergroups)){
                        foreach($usergroups as $usergroup){
                            $userIds[] = $usergroup->user_id;//['id'=>$usergroup->user_id];
                        }
                    }

                    $userIds = array_unique($userIds);
                    if(!empty($userIds)){
                        $user = User::whereIn('id',$userIds)->update(['permission_version'=>DB::raw('permission_version+1')]);

                    }

                    if (BrandConfiguration::call([Mix::class, 'isAllowAdminApi'])){
                        $this->resetPermissionInCache($user_type, $authObj);
                    }



                    DB::commit();

                    $status_code = 100;
                    $status_message = 'The record has been updated successfully!';

                    // set property permission version for user
                    $pageObj = new Page();
                    $this->permission_modification_info = $pageObj->getModuleSubmodulePagesByPageIds($db_role_pages,$selectedPageIdList);

                }
            }catch (\Throwable $e){
                DB::rollBack();
                $log_data['action'] = 'ROLE_PAGE_UPDATE_ROLLBACK';
                $log_data['rollback_status'] = true;
                $log_data['message'] = $e->getMessage() . ' in file ' . $e->getFile() . ' at line ' . $e->getLine();
                (new ManageLogging())->createLog($log_data);

                $status_code = 3;
                $status_message = 'Update failed';


            }

        }else{
            $status_code = 2;
            $status_message = 'No data available';
        }

        return [$status_code, $status_message];

    }


    public function resetPermissionInCache($user_type, $authObj){

        if ($user_type == User::ADMIN && $authObj->user_type == User::ADMIN){

            $pages = Page::query()
                ->join('sub_modules', 'sub_modules.id', 'pages.sub_module_id')
                ->join('role_pages', 'role_pages.page_id', 'pages.id')
                ->join('roles', 'role_pages.role_id', 'roles.id')
                ->join('usertype_submodules', 'sub_modules.id', 'usertype_submodules.sub_module_id')
                ->Join('usergroup_roles', 'usergroup_roles.role_id', 'role_pages.role_id')
                ->select(['sub_modules.controller_name', 'pages.method_name', 'usergroup_roles.usergroup_id'])
                ->where('usertype_submodules.user_type_id', $user_type)
                ->where('roles.company_id', $authObj->company_id)
                ->get()
                ->groupBy(function($item){
                    return Str::lowerCase($item["controller_name"]."@".$item["method_name"]);
                })->map(function ($group) {
                    return array_unique($group->pluck('usergroup_id')->toArray());
                })->toArray();

            if (count($pages) > 0){

                //update user_groups column
                if (SqlBuilder::isMysql()) {
                    $columns = DB::raw('GROUP_CONCAT(`usergroup_id`) AS `group_ids`');
                } else {
                    $columns = DB::raw("array_to_string(array_agg(usergroup_id), ',') as group_ids");
                }
                $userGroups = UserUsergroup::query()
                    ->select(['user_id', $columns])
                    ->where('company_id', $authObj->company_id)
                    ->groupBy('user_id')->get()

                    ->groupBy(['group_ids'])
                    ->map(function ($group) {
                        return array_unique($group->pluck('user_id')->toArray());
                    })
                    ->toArray();

                foreach ($userGroups as $key => $userIds){
                    User::query()->whereIn('id', $userIds)->where('user_type', $user_type)
                        ->update([
                            'user_group_ids' => $key
                        ]);
                }

                Cache::add(self::ADMIN_PERMISSION_CACHE_KEY, $pages, 0);// 0 means forever in the cache

                $logData = [
                    'action' => 'PERMISSION_IN_CACHE_UPDATE',
                    'data' => $userGroups
                ];
                (new ManageLogging())->createLog($logData);

            }



        }




    }

}
