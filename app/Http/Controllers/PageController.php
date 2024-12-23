<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\Page;
use App\Models\Module;
use App\Models\SubModule;
use Config;


class PageController extends Controller
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
            'subModuleTitle' => __("Page Management"),
            'subTitle' => __("Page List")
        ];

//        $pages = DB::table('pages')
//            ->join('modules', 'pages.module_id', '=', 'modules.id')
//            ->join('sub_modules', 'pages.sub_module_id', '=', 'sub_modules.id')
//            ->select('pages.*', 'modules.name as module_name', 'sub_modules.name as sub_module_name')
//            ->get();

        $pages = Page::with('submodules', 'modules')->get();
//dd($pages);
        return view('pages.index', compact('cmsInfo', 'pages'));
    }

    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            $this->validate($request, [
                'id' => 'required|integer|unique:pages,id'
            ]);
            //        $this->validate($request, $rules);
            //        dd($request);
            if (!isset($request->available_to_company)) {
                $request->available_to_company = 0;
            }
            //        $input = $request->all();

            $pageAdd = Page::create([
                'id' => $request->id,
                'module_id' => $request->module_id,
                'sub_module_id' => $request->sub_module_id,
                'name' => $request->name,
                'method_name' => $request->method_name,
                'method_type' => $request->method_type,
                'available_to_company' => $request->available_to_company
            ]);

            if ($pageAdd) {
                flash(__('The record has been saved successfully!'), 'success');
            }
            return redirect(route(Config::get('constants.defines.APP_PAGES_INDEX')));
        }

        return $this->showAddForm();
    }


    public function store(Request $request)
    {

    }

    public function showEditForm($id)
    {
        $cmsInfo = [
            'moduleTitle' => __("Master Data"),
            'subModuleTitle' => __("Page Management"),
            'subTitle' => __("Edit Page")
        ];
        $dynamic_route = Config::get('constants.defines.APP_PAGES_EDIT');
        $isEdit = true;
        $page = Page::find($id);
        $module = new Module();
        $modules = $module->getModules();
        $submodule = new SubModule();
        $submodules = $submodule->getSubModules();
        return view('pages.edit_view', compact('cmsInfo', 'dynamic_route', 'isEdit', 'page', 'modules', 'submodules'));
    }

    public function view($id)
    {
        $cmsInfo = [
            'moduleTitle' => __("Master Data"),
            'subModuleTitle' => __("Page Management"),
            'subTitle' => __("View Page")
        ];
        $dynamic_route = Config::get('constants.defines.APP_PAGES_CREATE');
        $isEdit = false;
        $page = Page::find($id);
        $module = new Module();
        $modules = $module->getModules();
        $submodule = new SubModule();
        $submodules = $submodule->getSubModules();
        return view('pages.edit_view', compact('cmsInfo', 'dynamic_route', 'isEdit', 'page', 'modules', 'submodules'));
    }


    public function showAddForm()
    {
        $cmsInfo = [
            'moduleTitle' => __("Master Data"),
            'subModuleTitle' => __("Page Management"),
            'subTitle' => __("Add Page")
        ];
        $module = new Module();
        $modules = $module->getModules();
        $submodule = new SubModule();
        $submodules = $submodule->getSubModules();

        $dynamic_route = Config::get('constants.defines.APP_PAGES_CREATE');
        return view('pages.edit_add', compact('cmsInfo', 'dynamic_route', 'modules', 'submodules'));
    }


    public function edit(Request $request, $id = null)
    {
        if ($request->isMethod('post')) {

            $this->validate($request, [
                'id' => 'required|integer|unique:pages,id,' . $request->old_id . 'id',
                'name' => 'required|unique:pages,name,' . $request->old_id . 'id',
            ]);
            if (!isset($request->available_to_company)) {
                $request->available_to_company = 0;
            }
            $page = Page::find($request->old_id);
            $pageEdit = $page->update([
                'id' => $request->id,
                'module_id' => $request->module_id,
                'sub_module_id' => $request->sub_module_id,
                'name' => $request->name,
                'method_name' => $request->method_name,
                'method_type' => $request->method_type,
                'available_to_company' => $request->available_to_company
            ]);
            if ($pageEdit) {
                flash(__('The record has been updated successfully!'), 'success');
            }
            return redirect(route(Config::get('constants.defines.APP_PAGES_INDEX')));
        } else {

            if ($id > 0) {
                return $this->showEditForm($id);
            }
        }
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        if ($id == Auth::user()->id) {
            flash(__("You Can't Delete Yourself!"), 'danger');
            return back();
        }
        $page = Page::find($id);
        $pageDelete = $page->delete();
//        flash(__('Module Deleted Successfully!'), 'info');
        if ($pageDelete) {
            flash(__('The record has been deleted successfully!'), 'success');
        }
        return back();
    }

    public function ajaxGetData($id)
    {
        $submodules = SubModule::where('module_id', '=', $id)->get();
        $submodule_contents = view('pages.submodulecontent', compact('submodules'))->render();
        $response['submodule_content'] = $submodule_contents;
        echo json_encode($response);
        exit;
    }
}
