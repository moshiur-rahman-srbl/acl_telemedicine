<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class MedicalRecordsController extends Controller
{
    private MedicalRecords $medicalRecord;

    public function __construct()
    {
        $this->medicalRecord = new MedicalRecords();
    }

    public function index(Request $request)
    {
        $input = $request->all();
        $view_data = $this->getIndexViewData($input);
        return view("medical_records.index", $view_data);
    }

    public function getIndexViewData($input): array
    {
        return [
            "cmsInfo" => [
                'moduleTitle' => __("Operations"),
                'subModuleTitle' => __("Medical Records"),
                'subTitle' => __("Medical Records List"),
            ],
            "filters" => $this->medicalRecord->handleSearch($input),
            "medicalRecords" => $this->medicalRecord->getAll($this->medicalRecord->handleSearch($input), true, $input['page_limit'] ?? 10),
        ];
    }

    public function create(Request $request)
    {
        if ($request->isMethod("POST")) {
            $input = $request->all();
            [$rule, $message] = $this->medicalRecord->validateData();
            $validate = Validator::make($input, $rule, $message);

            if ($validate->fails()) {
                flash($validate->errors()->first(), 'danger');
                return redirect()->back();
            }

            $record = $this->medicalRecord->saveData($this->medicalRecord->prepareInsertData($input));

            flash($record ? __('Saved successfully!') : __('Some Errors!'), $record ? 'success' : 'danger');
            return redirect(route(Config::get('constants.defines.APP_MEDICAL_RECORDS_INDEX')));
        }

        return view("medical_records.create", ["cmsInfo" => [
            'moduleTitle' => __("Operations"),
            'subModuleTitle' => __("Medical Records"),
            'subTitle' => __("Create Medical Record"),
        ],
            "doctors" => $this->medicalRecord->getDoctors(),
    ]);
    }

    public function edit(Request $request, $id)
    {
        if ($request->isMethod("POST")) {
            $input = $request->all();
            [$rule, $message] = $this->medicalRecord->validateData();
            $validate = Validator::make($input, $rule, $message);

            if ($validate->fails()) {
                flash($validate->errors()->first(), 'danger');
                return redirect()->back();
            }

            $record = $this->medicalRecord->updateData($id, $this->medicalRecord->prepareInsertData($input));

            flash($record ? __('Updated successfully!') : __('Some Errors!'), $record ? 'success' : 'danger');
            return redirect(route(Config::get('constants.defines.APP_MEDICAL_RECORDS_INDEX')));
        }

        return view("medical_records.edit", [
            "cmsInfo" => [
            'moduleTitle' => __("Operations"),
            'subModuleTitle' => __("Medical Records"),
            'subTitle' => __("Edit Medical Record"),
        ], 
        "record" => $this->medicalRecord->findById($id), // Updated key to "record"
        "doctors" => $this->medicalRecord->getDoctors(),
    
    
    ]);
    }

    public function getEditViewData($id): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("Operations"),
            'subModuleTitle' => __("Medical Records"),
            'subTitle' => __("View Medical Record"),
        ];
        // $view_data["statuses"] = $this->medicalRecord->getStatuses();
        $view_data["model"] = $this->medicalRecord->findById($id);
        // $view_data["doctors"] = $this->appointment->getDoctors(); // Example: fetching doctors
        // $view_data["patients"] = $this->appointment->getPatients(); // Example: fetching patients
        return $view_data;
    }
    
    public function view($id)
    {
        $record = $this->medicalRecord->findOrFail($id);
        $view_data = $this->getEditViewData($id);

        // Pass data to the view
        return view('medical_records.view', $view_data);
    }

    public function destroy(Request $request, $id)
    {
        $ids = $request->action == MedicalRecords::MOVE_TO_TRASH ? $request->input('ids', []) : $id;
        $record = $this->medicalRecord->deleteData($ids);

        flash($record ? __('Deleted successfully!') : __('Some Errors!'), $record ? 'success' : 'danger');
        return redirect()->back();
    }
}
