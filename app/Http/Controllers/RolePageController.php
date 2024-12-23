<?php

namespace App\Http\Controllers;

use App\Models\AdminMakerChecker;
use App\Models\MerchantReportHistory;
use App\Models\RolePageExport;
use App\Models\RolePageHistory;
use App\Utils\CommonFunction;
use Carbon\Carbon;
use common\integration\ManageAccessControl;
use common\integration\ManageLogging;
use common\integration\Utility\File;
use Illuminate\Http\Request;
use Auth;
use App\Models\Role;
use App\Models\Module;
use App\Models\RolePage;
use App\Models\UsergroupRole;
use App\Models\Usergroup;
use App\Models\UserUsergroup;
use App\User;
use DB;
use App\Models\UsertypeSubmodule;
use common\integration\BrandConfiguration;
use Illuminate\Support\Facades\Config;

class RolePageController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        ManageLogging::queryLog("index1");
        if ($request->isMethod('post')) {
            return $this->store($request);
        } else {
            $cmsInfo = [
                'moduleTitle' => __("Access Control"),
                'subModuleTitle' => __("Role & Page Association")
            ];
            //echo Auth::user()->id;exit;


            $DEFAULT_COMPANY_ID = 1;
            $user = Auth::user();
            $company_id = $user->company_id;
            $role = new Role;
            $roleList = $role->getRolesByCompanyId($company_id, $DEFAULT_COMPANY_ID);

            $exportBtnRouteName = Config::get('constants.defines.APP_ROLE_PAGE_ASSOCIATION');
            $export_types = File::FORMAT_LIST;

            return view('RolePages.index', compact('cmsInfo', 'roleList', 'exportBtnRouteName', 'export_types'));
        }
    }


    public function ajaxGetRequest(Request $request, $id)
    {

        $rolepage = new RolePage();
        $user = Auth::user();

        $response = $rolepage->getSelectedpages($id, $user);
        echo json_encode($response);
        exit;
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        ManageLogging::queryLog("store");
        $auth = Auth::user();

        $rolepage = new RolePage();

        list($status_code, $status_message) = $rolepage->associateRolePage($request, User::ADMIN, $auth);
        if (!empty($rolepage->permission_modification_info)) {
            $logData['action'] = "ADMIN_PERMISSION_UPDATE";
            $logData['role'] = (new Role())->findById($request->role_id)->title;
            $logData['permission'] = $rolepage->permission_modification_info;
            (new ManageLogging())->createLog($logData);
        }
        if ($status_code == 100) {
            flash(__($status_message), 'success');
        } else {
            flash(__($status_message), 'danger');
        }
        return back()->withInput();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
//    public function store(Request $request) {
//        //
//    }

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
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
    public function destroy($id)
    {
        //
    }

    public function exportRolePage(Request $request)
    {
        $user = Auth::user();
        list($data, $title, $header) = ManageAccessControl::prepareDataRolePage($user);
        $file_name = __('Role & Page Association');
        $file_type = MerchantReportHistory::FORMAT_LIST[MerchantReportHistory::FORMAT_XLS];
        return $this->fileExport($file_type, collect($data), $file_name, $header, '', '', '', '', $title, 1);
    }

}
