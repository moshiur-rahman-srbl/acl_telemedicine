<?php

namespace App\Http\Controllers;

use Auth;
use common\integration\Brand\Configuration\Backend\BackendAdmin;
use common\integration\BrandConfiguration;
use common\integration\Utility\Arr;
use Config;
use App\Models\Role;
use App\Models\RoleSetting;
use Illuminate\Http\Request;
use App\Models\UsergroupRole;
use common\integration\ManageLogging;
use DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cmsInfo = [
            'moduleTitle' => __("Access Control"),
            'subModuleTitle' => __("Role Management"),
            'subTitle' => __("Role List")
        ];
        $roles = (new Role())->getRoles(
            Auth::user()->company_id
        );
        return view('roles.index', compact('cmsInfo', 'roles'));
    }

    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            $log_data['action'] = 'ADMIN_ROLE_CREATE';
            $rules = [
                'title' => 'required|max:100|unique:roles,title'
            ];

            if (BrandConfiguration::call([BackendAdmin::class, 'allowAccessRoleTurkishVersion'])) {
                $rules = Arr::merge($rules, [
                    'title_tr' => 'required|max:100|unique:roles,title_tr'
                ]);
            }


            $this->validate($request, $rules);

            $auth = Auth::user();

            $data = [
                "title" => $request->title,
                'company_id' => $auth->company_id,
                'created_by_id' => $auth->id,
            ];

            if (BrandConfiguration::call([BackendAdmin::class, 'allowAccessRoleTurkishVersion'])) {
                $data = Arr::merge($data, [
                    'title_tr' => $request->title_tr
                ]);
            }

            $roleAdd = Role::insert_entry($data);

            if ($roleAdd) {
                (new RoleSetting())->addOrDeleteSetting($roleAdd->id, isset($request->is_allow_merchant_auth_email) ? $request->is_allow_merchant_auth_email : 0);
                flash(__('The record has been saved successfully!'), 'success');
                //log create
                $log_data['input_data'] = $data;
                $log_data['status'] = 'success';
                (new ManageLogging())->createLog($log_data);
            }
            return redirect(route(Config::get('constants.defines.APP_ROLES_INDEX')));


            return redirect()->back();

        } else {
            return $this->showAddForm();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
//    public function create()
//    {
//        //
//    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    public function showEditForm($id)
    {
        $cmsInfo = [
            'moduleTitle' => __("Access Control"),
            'subModuleTitle' => __("Role Management"),
            'subTitle' => __("Edit Role")
        ];
        $dynamic_route = Config::get('constants.defines.APP_ROLES_EDIT');
        $isEdit = true;
        $role = Role::find($id);

        if (!empty($role)) {
            $roleSet = (new RoleSetting())->findByRoleId($role->id);
            $isAllowed = !empty($roleSet) ? $roleSet->is_allow_merchant_auth_email : '';
        }
        return view('roles.edit_view', compact('cmsInfo', 'dynamic_route', 'isEdit', 'isAllowed', 'role'));
    }

    public function showViewForm($id)
    {
        $cmsInfo = [
            'moduleTitle' => __("Access Control"),
            'subModuleTitle' => __("Role Management"),
            'subTitle' => __("View Role")
        ];
        $dynamic_route = Config::get('constants.defines.APP_ROLES_CREATE');
        $isEdit = false;
        $role = Role::find($id);
        if (!empty($role)) {
            $roleSet = (new RoleSetting())->findByRoleId($role->id);
            $isAllowed = !empty($roleSet) ? $roleSet->is_allow_merchant_auth_email : '';
        }
        return view('roles.edit_view', compact('cmsInfo', 'dynamic_route', 'isEdit', 'isAllowed', 'role'));
    }


    public function showAddForm()
    {
        $cmsInfo = [
            'moduleTitle' => __("Access Control"),
            'subModuleTitle' => __("Role Management"),
            'subTitle' => __("Add Role")
        ];

        $dynamic_route = Config::get('constants.defines.APP_ROLES_CREATE');
        return view('roles.edit_add', compact('cmsInfo', 'dynamic_route'));
    }


    public function edit(Request $request, $id = null)
    {
        $flag = $this->validateCompany($id, 'roles');
        if ($flag) {
            return redirect()->route('login');
        }

        if ($request->isMethod('post')) {
            $log_data['action'] = 'ADMIN_ROLE_EDIT';

            $rules = [
                'title' => 'required|max:100|unique:roles,title,' . $request->id
            ];

            if (BrandConfiguration::call([BackendAdmin::class, 'allowAccessRoleTurkishVersion'])) {
                $rules = Arr::merge($rules, [
                    'title_tr' => 'required|max:100|unique:roles,title_tr,' . $request->id
                ]);
            }

            $this->validate($request, $rules);

            $auth = Auth::user();


            $insert_data = [
                'title' => $request->title,
                'updated_by_id' => $auth->id,
            ];

            if (BrandConfiguration::call([BackendAdmin::class, 'allowAccessRoleTurkishVersion'])) {
                $insert_data = Arr::merge($insert_data, [
                    'title_tr' => $request->title_tr
                ]);
            }

            $role = Role::find($request->id);
            $roleEdit = $role->update($insert_data);
            if ($roleEdit) {
                (new RoleSetting())->addOrDeleteSetting($request->id, isset($request->is_allow_merchant_auth_email) ? $request->is_allow_merchant_auth_email : 0);
                flash(__('The record has been updated successfully!'), 'success');
                //log edit
                $log_data['edit_data'] = $request->all();
                $log_data['status'] = 'success';
                (new ManageLogging())->createLog($log_data);
            }
            return redirect(route(Config::get('constants.defines.APP_ROLES_INDEX')));


            return redirect()->back();

        } else {
            return $this->showEditForm($id);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $auth = Auth::user();
        if ($id == 'bulk_delete' && is_array(request()->input('roles_id', ''))) {
            try {
                $rolesId = request()->input('roles_id');
                $validator = Validator::make([
                    'roles_id' => request()->input('roles_id')
                ], [
                    'roles_id' => ['required', 'array', Rule::exists('roles', 'id')]
                ]);

                if ($validator->fails()) {
                    flash(__('Failed to bulk delete. Please, try again.'), 'danger');
                    throw new \Exception(__('Failed to bulk delete. Please, try again.'));
                }


                DB::beginTransaction();
                (new Role)->deleteByRolesId($rolesId);
                (new RoleSetting())->deleteByRolesId($rolesId);
                DB::commit();
                flash(__('The roles have been deleted successfully!'), 'success');
                //log activity
                $logData['action'] = "ADMIN_BULK_ROLE_DELETE";
                $logData['roles_id'] = $rolesId;
                $logData['status'] = 'success';
                (new ManageLogging())->createLog($logData);


            } catch (\Throwable $th) {
                DB::rollBack();
            }

        } else {

            $flag = $this->validateCompany($id, 'roles');
            if ($flag) {
                return redirect()->route('login');
            }

            if ($id == Auth::user()->id) {
                flash(__("You Can't Delete Yourself!"), 'danger');
                return back();
            }


            $usergrouprole = UsergroupRole::where('role_id', $id)->first();
            if (empty($usergrouprole)) {
                $role = Role::find($id);
                $roleDelete = $role->delete();
                if ($roleDelete) {
                    (new RoleSetting())->deleteByRoleId($id);
                    flash(__('The record has been deleted successfully!'), 'success');
                    //log activity
                    $logData['action'] = "ADMIN_SINGLE_ROLE_DELETE";
                    $logData['role_info'] = $role;
                    $logData['status'] = 'success';
                    (new ManageLogging())->createLog($logData);
                }
            } else {
                flash(__('This Role Is Being Used'), 'danger');
            }


        }
        return redirect()->back();
    }
}
