<?php

namespace App\Http\Controllers;

use App\Models\Appoinment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class AppoinmentController extends Controller
{
    private Appoinment $appointment;

    public function __construct()
    {
        $this->appointment = new Appoinment();
    }

    public function index(Request $request)
    {
        $input = $request->all();
        $view_data = $this->getIndexViewData($input);
        return view("appoinment.index", $view_data);
    }

    public function getIndexViewData($input): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("Operations"),
            'subModuleTitle' => __("Appoinment"),
            'subTitle' => __("Appoinment List")
        ];
        $view_data["filters"] = $this->appointment->handleSearch($input);
        $view_data["models"] = $this->appointment->getAll($view_data["filters"], true, $view_data["filters"]["page_limit"]);
        $view_data["page_limit"] = $view_data["filters"]["page_limit"];
        $view_data["statuses"] = $this->appointment->getStatuses();
        return $view_data;
    }

    public function create(Request $request)
    {
        if ($request->isMethod("POST")) {

            $input = $request->all();
            [$rule, $message] = $this->appointment->validateData();
            $validate = Validator::make($input, $rule, $message);
            if ($validate->fails()) {
                flash(($validate->errors()->first()), 'danger');
                return redirect()->back();
            }

            $store_data = $this->appointment->prepareInsertData($input);
            $appointment = $this->appointment->saveData($store_data);

            if (!empty($appointment)) {
                flash(('Saved successfully!'), 'success');
            } else {
                flash(('Some Errors!'), 'danger');
            }
            return redirect(route(Config::get('constants.defines.APP_APPOINMENT_INDEX')));
        }

        $view_data = $this->getCreateViewData();

        return view("appoinment.create", $view_data);
    }

    public function getCreateViewData(): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("Operations"),
            'subModuleTitle' => __("Appoinment"),
            'subTitle' => __("Appoinment Create")
        ];
        $view_data["statuses"] = $this->appointment->getStatuses();
        $view_data["patients"] = $this->appointment->getPatients();
        $view_data["doctors"] = $this->appointment->getDoctors();



        return $view_data;
    }

    public function edit(Request $request, $id)
    {
        if ($request->isMethod("POST")) {

            $input = $request->all();
            [$rule, $message] = $this->appointment->validateData();
            $validate = Validator::make($input, $rule, $message);
            if ($validate->fails()) {
                flash(($validate->errors()->first()), 'danger');
                return redirect()->back();
            }

            $update_data = $this->appointment->prepareInsertData($input);
            $appointment = $this->appointment->updateData($id, $update_data);

            if (!empty($appointment)) {
                flash(('Update successfully!'), 'success');
            } else {
                flash(('Some Errors!'), 'danger');
            }
            return redirect(route(Config::get('constants.defines.APP_APPOINMENT_INDEX')));
        }

        $view_data = $this->getEditViewData($id);
        return view("appoinment.edit", $view_data);
    }

    public function getEditViewData($id): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("Operations"),
            'subModuleTitle' => __("Appoinment"),
            'subTitle' => __("Appoinment View")
        ];
        $view_data["statuses"] = $this->appointment->getStatuses();
        $view_data["model"] = $this->appointment->findById($id);
        // $view_data["doctors"] = $this->appointment->getDoctors(); // Example: fetching doctors
        // $view_data["patients"] = $this->appointment->getPatients(); // Example: fetching patients
        return $view_data;
    }
    public function view($id)
    {
        $appointment = $this->appointment->findOrFail($id);
        $view_data = $this->getEditViewData($id);

        // Pass data to the view
        return view('appoinment.view', $view_data);
    }
    
    public function destroy(Request $request, $id)
    {
        if (!empty($request->action) && $request->action == Appoinment::MOVE_TO_TRASH) {
            $ids = $request->input('ids', []);
        } else {
            $ids = $id;
        }
        $appointment = $this->appointment->deleteData($ids);
        if ($appointment) {
            flash(('Delete successfully!'), 'success');
        } else {
            flash(('Some Errors!'), 'danger');
        }
        return redirect()->back();
    }
}