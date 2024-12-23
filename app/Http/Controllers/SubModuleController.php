<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

//use Illuminate\Filesystem\Cache;
use Illuminate\Http\Request;
use Auth;
use App\Models\SubModule;
use App\Models\Module;
use App\Models\Page;
use DB;
use Config;
use App\Models\UsertypeSubmodule;

class SubModuleController extends Controller
{
    private function get_user_types()
    {
        return [
            config('constants.defines.CUSTOMER_USER_TYPE') => 'Customer',
            config('constants.defines.ADMIN_USER_TYPE') => 'Admin',
            config('constants.defines.MERCHANT_USER_TYPE') => 'Merchant',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        $cache = Cache::get('footer');
//        dd($cache);

        $cmsInfo = [
            'moduleTitle' => __("Master Data"),
            'subModuleTitle' => __("Submodules")
        ];

        $user = Auth::user();
        $submodules = SubModule::with('modules')->get();
        //dd($modules);exit;
        return view('submodules.index', compact('cmsInfo', 'submodules'));
    }


    public function showAddForm()
    {
        $cmsInfo = [
            'moduleTitle' => __("Master Data"),
            'subModuleTitle' => __("Submodules")

        ];
        $module = new Module();
        $modules = $module->getModules();
        $user_types = $this->get_user_types();
        $dynamic_route = Config::get('constants.defines.APP_SUBMODULES_CREATE');
        return view('submodules.edit_add', compact('cmsInfo', 'modules', 'dynamic_route', 'user_types'));
    }

    public function create(Request $request)
    {
        //dd($request);
        if ($request->isMethod('post')) {
            $rules = [
                'submodule_id' => 'required|integer',
                'submodule_name' => 'required',
                'submodule_icon' => 'required',
                'sequence' => 'required|integer',
                'controller_name' => 'required',
                'default_method' => 'required',
                'user_type_id' => 'required'

            ];
            $this->validate($request, $rules);
            $input = $request->all();
            //$modules = DB::table('modules')->first();
//            $submodule  = SubModule::create([
//                'id'=>$input['submodule_id'],
//                'module_id'=>$input['module_id'],
//                'name'=>$input['submodule_name'],
//                'icon' => $input['submodule_icon'],
//                'sequence'=>$input['sequence'],
//                'controller_name'=>$input['controller_name'],
//                'default_method'=>$input['default_method'],
//
//            ]);
            $submodule = new SubModule();
            $submodule->insert_entry($input);
            if (!empty($input['user_type_id'])) {
                UsertypeSubmodule::where('sub_module_id', $input['submodule_id'])->delete();
                foreach ($input['user_type_id'] as $value) {
                    UsertypeSubmodule::create([
                        'user_type_id' => $value,
                        'sub_module_id' => $input['submodule_id']
                    ]);
                }
            }
//            dd($submodule);
            flash(__('The record has been saved successfully!'), 'success');
            return redirect(route(Config::get('constants.defines.APP_SUBMODULES_INDEX')));
        }

        return $this->showAddForm();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showeditform($id)
    {
        $cmsInfo = [
            'moduleTitle' => __("Master Data"),
            'subModuleTitle' => __("Sub Module Management"),
            'subTitle' => __("SubModule edit")
        ];
        $dynamic_route = Config::get('constants.defines.APP_SUBMODULES_EDIT');
        $isEdit = true;
        $submodules = SubModule::with('usertype_submodules')->find($id);
        $module = new Module();
        $modules = $module->getModules();
        $user_types = $this->get_user_types();
//        dd($submodules->usertype_submodules);
//        $usertype_submodules = UsertypeSubmodule::where('sub_module_id', $id)->get();
        return view('submodules.edit_view', compact('cmsInfo', 'dynamic_route', 'isEdit', 'submodules', 'modules', 'user_types'));
    }

    public function view($id)
    {
        $cmsInfo = [
            'moduleTitle' => __("Master Data"),
            'subModuleTitle' => __("Sub Module Management"),
            'subTitle' => __("SubModule edit")
        ];
        $dynamic_route = Config::get('constants.defines.APP_SUBMODULES_CREATE');
        $isEdit = false;
        $submodules = SubModule::find($id);
        $module = new Module();
        $modules = $module->getModules();
        return view('submodules.edit_view', compact('cmsInfo', 'dynamic_route', 'isEdit', 'submodules', 'modules'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */


    public function edit(Request $request, $id = null)
    {
        if ($request->isMethod('post')) {

            $rules = [
                'module_id' => 'required',
                'submodule_id' => 'required|integer|unique:sub_modules,id,' . $request->id . 'id',
                'submodule_name' => 'required',
                'submodule_icon' => 'required',
                'sequence' => 'required|integer',
                'controller_name' => 'required',
                'default_method' => 'required',
                'user_type_id' => 'required'
            ];

            $this->validate($request, $rules);
            $input = $request->all();
            $submodule = SubModule::find($request->subold_id);
            $submoduleSave = $submodule->insert_entry($input);
            if (!empty($input['user_type_id'])) {
                UsertypeSubmodule::where('sub_module_id', $input['submodule_id'])->delete();
                foreach ($input['user_type_id'] as $value) {
                    UsertypeSubmodule::create([
                        'user_type_id' => $value,
                        'sub_module_id' => $input['submodule_id']
                    ]);
                }
            }
            if ($submoduleSave) {
                flash(__('The record has been updated successfully!'), 'success');
            }

            return redirect(route(Config::get('constants.defines.APP_SUBMODULES_INDEX')));

        } else {

            if ($id > 0) {
                return $this->showeditform($id);
            }
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */


    public function destroy($id)
    {
        if ($id == Auth::user()->id) {
            flash(__("You Can't Delete Yourself!"), 'danger');
            return back();
        }
        $pages = Page::where('sub_module_id', $id)->first();
        if (empty($pages)) {
            $submodule = SubModule::find($id);
            $subDelete = $submodule->delete();
            if ($subDelete) {
                flash(__('The record has been deleted successfully!'), 'success');
            }
        } else {
            flash(__('This Submodule Is Being Used'), 'danger');
        }
        return back();
    }
}
