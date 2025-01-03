<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Models\Usergroup;
use App\Models\UserUsergroup;
use App\Models\UserUserGroupSetting;


class DoctorController extends Controller
{
    private User $model;

    public function __construct()
    {
        $this->model = new User();
    }

    public function index(Request $request)
    {
        $input = $request->all();
        $view_data = $this->getIndexViewData($input);
        return view("doctor.index", $view_data);
    }

    public function getIndexViewData($input): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("Operations"),
            'subModuleTitle' => __("Doctor"),
            'subTitle' => __("Doctor List")
        ];
        $view_data["filters"] = $this->model->handleSearch($input);
        $view_data["models"] = $this->model->getDoctors($view_data["filters"], true, $view_data["filters"]["page_limit"]);
        $view_data["page_limit"] = $view_data["filters"]["page_limit"];

        return $view_data;
    }

    public function create(Request $request)
    {
        if ($request->isMethod("POST")) {

            $input = $request->all();
            [$rule, $message] = $this->model->validateDoctors();
            $validate = Validator::make($input, $rule, $message);
            if ($validate->fails()) {
                flash(($validate->errors()->first()), 'danger');
                return redirect()->back();
            }

            $store_data = $this->model->prepareInsertData($input);
            $doctor = $this->model->saveDoctor($store_data);

            if (!empty($doctor)) {
                flash(('Saved successfully!'), 'success');
            } else {
                flash(('Some Errors!'), 'danger');
            }
            return redirect(route(Config::get('constants.defines.APP_DOCTOR_INDEX')));
        }

        $view_data = $this->getCreateViewData();
        return view("doctor.create", $view_data);
    }

    public function getCreateViewData(): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("Operations"),
            'subModuleTitle' => __("Doctor"),
            'subTitle' => __("Doctor Create")
        ];
        // $view_data["statuses"] = $this->model->getStatuses();
        //$view_data["doctors"] = $this->model->getDoctors(); // Example: fetching doctors
        // $view_data["patients"] = $this->appointment->getPatients(); // Example: fetching patients
        return $view_data;
    }
    public function getEditViewData($doctor): array
{
    $view_data["cmsInfo"] = [
        'moduleTitle' => __("Operations"),
        'subModuleTitle' => __("Doctor"),
        'subTitle' => __("Doctor Edit")
    ];

    // Pass doctor data to the view
    $view_data['doctor'] = $doctor; 

    return $view_data;
}

    public function edit(Request $request, $id)
{
    // Fetch doctor data by ID
    $doctor = $this->model->findOrFail($id);

    if ($request->isMethod('POST')) {
        // If the form is submitted via POST, handle validation and update the doctor record
        $input = $request->all();

        // Validation rules for the doctor fields
        [$rule, $message] = $this->model->validateDoctors();
        $validate = Validator::make($input, $rule, $message);

        if ($validate->fails()) {
            flash($validate->errors()->first(), 'danger');
            return redirect()->back();
        }

        // Prepare the input data for saving
        $update_data = $this->model->prepareInsertData($input);

        // Update the doctor data
        $doctorUpdated = $this->model->updateDoctor($id, $update_data);

        // Check if the update was successful
        if ($doctorUpdated) {
            flash(__('Update successfully!'), 'success');
        } else {
            flash(__('Some Errors!'), 'danger');
        }

        // Redirect to the doctor index page
        return redirect(route(Config::get('constants.defines.APP_DOCTOR_INDEX')));
    }

    // Return the edit view with the doctor data
    $view_data = $this->getEditViewData($doctor);

    return view('doctor.edit', $view_data);
}


    // public function edit(Request $request, $id)
    // {
    //     if ($request->isMethod("POST")) {

    //         $input = $request->all();
    //         [$rule, $message] = $this->model->validateDoctors();
    //         $validate = Validator::make($input, $rule, $message);
    //         if ($validate->fails()) {
    //             flash(($validate->errors()->first()), 'danger');
    //             return redirect()->back();
    //         }

    //         $update_data = $this->model->prepareInsertData($input);
    //         $doctor = $this->model->updateDoctor($id, $update_data);

    //         if (!empty($doctor)) {
    //             flash(('Update successfully!'), 'success');
    //         } else {
    //             flash(('Some Errors!'), 'danger');
    //         }
    //         return redirect(route(Config::get('constants.defines.APP_DOCTOR_INDEX')));

    //         $view_data = $this->getEditViewData();
    //         return view("doctor.edit", $view_data);
    //     }
    // }

    // public function getEditViewData(): array
    // {
    //     $view_data["cmsInfo"] = [
    //         'moduleTitle' => __("Operations"),
    //         'subModuleTitle' => __("Doctor"),
    //         'subTitle' => __("Doctor Edit")
    //     ];
    //     //
    //     //$view_data["statuses"] = $this->model->getStatuses();
    //     //$view_data["model"] = $this->model->findById($id);
    //     //$view_data["doctors"] = $this->model->getDoctors(); // Example: fetching doctors
    //     // $view_data["patients"] = $this->appointment->getPatients(); // Example: fetching patients
    //     return $view_data;
    // }

    public function view($id)
    {
        $doctor = $this->model->findOrFail($id); // Fetch doctor by ID
        $view_data = [
            'cmsInfo' => [
                'moduleTitle' => __("Operations"),
                'subModuleTitle' => __("Doctor"),
                'subTitle' => __("Doctor View")
            ],
            'doctor' => $doctor, // Pass doctor data to the view
        ];

        return view('doctor.view', $view_data);
    }


    // 
    public function destroy(Request $request, $id)
    {
        // dd($id,$request->all());    // Ensure $id is an array for bulk deletion or a single ID for individual deletion
        $ids = is_array($id) ? $id : [$id];

        // Delete users where their role or type is 'doctor'
        $deleted = \App\User::whereIn('id', $ids)
            ->delete();

        if ($deleted) {
            flash(('Delete successfully!'), 'success');
        } else {
            flash(('Some Errors!'), 'danger');
        }

        return redirect()->back();
    }
}
