<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    private User $patient;

    public function __construct()
    {
        $this->patient = new User();
    }

    public function index(Request $request)
    {
        $input = $request->all();
        $view_data = $this->getIndexViewData($input);
        return view("patient.index", $view_data);
    }

    public function getIndexViewData($input): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("Operations"),
            'subModuleTitle' => __("Patient"),
            'subTitle' => __("Patient List")
        ];
        $view_data["filters"] = $this->patient->handleSearch($input);
        $view_data["models"] = $this->patient->getPatients($view_data["filters"], true, $view_data["filters"]["page_limit"]);
        $view_data["page_limit"] = $view_data["filters"]["page_limit"];
        //$view_data["statuses"] = $this->patient->getStatuses();
        return $view_data;
    }

    public function create(Request $request)
    {
        if ($request->isMethod("POST")) {

            $input = $request->all();
            [$rule, $message] = $this->patient->validateData();
            $validate = Validator::make($input, $rule, $message);
            if ($validate->fails()) {
                flash(($validate->errors()->first()), 'danger');
                return redirect()->back();
            }

            $store_data = $this->patient->preparePatientData($input);
            $patient = $this->patient->saveData($store_data);

            if (!empty($patient)) {
                flash(('Saved successfully!'), 'success');
            } else {
                flash(('Some Errors!'), 'danger');
            }
            return redirect(route(Config::get('constants.defines.APP_PATIENT_INDEX')));
        }

        $view_data = $this->getCreateViewData();

        return view("patient.create", $view_data);
    }

    public function getCreateViewData(): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("Operations"),
            'subModuleTitle' => __("Patient"),
            'subTitle' => __("Patient Create")
        ];
        $view_data["patients"] = $this->patient->getPatients();
        // Example: fetching additional patient-related data can be added here
        return $view_data;
    }

    public function edit(Request $request, $id)
    {
        if ($request->isMethod("POST")) {
            $patient = $this->patient->findOrFail($id);
            $input = $request->all();
            [$rule, $message] = $this->patient->validateData($id);
            $validate = Validator::make($input, $rule, $message);
            if ($validate->fails()) {
                flash(($validate->errors()->first()), 'danger');
                return redirect()->back();
            }

            $update_data = $this->patient->preparePatientData($input);
            $doctorUpdated = $patient->updateData($id, $update_data);

            if (!empty($doctorUpdated)) {
                flash(('Update successfully!'), 'success');
            } else {
                flash(('Some Errors!'), 'danger');
            }
            return redirect(route(Config::get('constants.defines.APP_PATIENT_INDEX')));
        }

        $view_data = $this->getEditViewData($id);
        return view("patient.edit", $view_data);
    }

    public function getEditViewData($id): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("Operations"),
            'subModuleTitle' => __("Patient"),
            'subTitle' => __("Patient Edit")
        ];
        //$view_data["statuses"] = $this->patient->getStatuses();
        $view_data["model"] = $this->patient->findById($id);
        // Additional patient-related data fetching can be added here
        return $view_data;
    }

    public function view($id)
    {
        $patient = $this->patient->findOrFail($id);
        $view_data = $this->getEditViewData($id);

        // Pass data to the view
        return view('patient.view', $view_data);
    }

    public function destroy(Request $request, $id)
    {
        if (!empty($request->action) && $request->action == Patient::MOVE_TO_TRASH) {
            $ids = $request->input('ids', []);
        } else {
            $ids = $id;
        }
        $patient = $this->patient->deleteData($ids);
        if ($patient) {
            flash(('Delete successfully!'), 'success');
        } else {
            flash(('Some Errors!'), 'danger');
        }
        return redirect()->back();
    }
}
