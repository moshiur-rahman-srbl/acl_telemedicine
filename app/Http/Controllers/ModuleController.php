<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Module;
use App\Models\SubModule;
use Session;
use Config;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cmsInfo = [
            'moduleTitle' => __("Master Data"),
            'subModuleTitle' => __("Module Management"),
            'subTitle' => __("Module List")
        ];

        $SUPER_SUPER_ADMIN_ID = 1;
        $user = Auth::user();
        $company_id = $user->company_id;
        $module = new Module;
        $modules = $module->getModules();
        //dd($modules);exit;

        return view('modules.index', compact('cmsInfo', 'modules'));
    }


    private function validation_rule($id = null)
    {
        return $rules = [
            'module_name' => 'required|unique:modules,name,' . $id,
            'module_id' => 'required|integer|unique:modules,id,' . $id,
            'module_icon' => 'required',
            'sequence' => 'required|integer'
        ];
    }

    private function input_array($input)
    {
        return [
            'id' => $input['module_id'],
            'name' => $input['module_name'],
            'icon' => $input['module_icon'],
            'sequence' => $input['sequence']
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            $rules = $this->validation_rule('');
            $this->validate($request, $rules);
            $input = $request->all();
            $input_array = $this->input_array($input);
            $module = Module::create($input_array);
            flash(__('The record has been saved successfully!'), 'success');
            return redirect(route(Config::get('constants.defines.APP_MODULES_INDEX')));
        }

        return $this->showAddForm();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    public function showeditform($id)
    {
        $cmsInfo = [
            'moduleTitle' => __("Master Data"),
            'subModuleTitle' => __("Module Management"),
            'subTitle' => __("Edit Module")
        ];
        $dynamic_route = Config::get('constants.defines.APP_MODULES_EDIT');
        $isEdit = true;
        $module = Module::find($id);
        return view('modules.edit_view', compact('cmsInfo', 'dynamic_route', 'isEdit', 'module'));
    }

    public function view($id)
    {
        $cmsInfo = [
            'moduleTitle' => __("Master Data"),
            'subModuleTitle' => __("Module Management"),
            'subTitle' => __("View Module")
        ];
        $dynamic_route = Config::get('constants.defines.APP_MODULES_EDIT');
        $isEdit = false;
        $module = Module::find($id);
        return view('modules.edit_view', compact('cmsInfo', 'dynamic_route', 'isEdit', 'module'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showAddForm()
    {
        $cmsInfo = [
            'moduleTitle' => __("Master Data"),
            'subModuleTitle' => __("Module Management"),
            'subTitle' => __("Add Module")
        ];

        $dynamic_route = Config::get('constants.defines.APP_MODULES_CREATE');
        return view('modules.edit_add', compact('cmsInfo', 'dynamic_route'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id = null)
    {
        if ($request->isMethod('post')) {
            $rules = $this->validation_rule($request->id);
            $this->validate($request, $rules);
            $input = $request->all();
            $input_array = $this->input_array($input);
            $module = Module::find($request->old_id);
            $moduleSave = $module->update($input_array);
            if ($moduleSave) {
                flash(__('The record has been updated successfully!'), 'success');
            }

            return redirect(route(Config::get('constants.defines.APP_MODULES_INDEX')));

        } else {

            if ($id > 0) {
                return $this->showeditform($id);
            }

        }
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
        if ($id == Auth::user()->id) {
            flash(__("You can't Delete Yourself!"), 'danger');
            return back();
        }
        $submodule = SubModule::where('module_id', $id)->first();
        if (empty($submodule)) {
            $module = Module::find($id);
            $module->delete();
            flash(__('The record has been deleted successfully!'), 'success');
        } else {
            flash(__('This Module Is Being Used'), 'danger');
        }
        return back();
    }
}
