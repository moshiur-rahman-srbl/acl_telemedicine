<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecords;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class MedicalHistoryController extends Controller
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
        return view("medical_history.index", $view_data);
    }

    public function getIndexViewData($input): array
    {
        return [
            "cmsInfo" => [
                'moduleTitle' => __("Doctors"),
                'subModuleTitle' => __("Medical Histories"),
                'subTitle' => __("Medical History List"),
            ],
            "filters" => $this->medicalRecord->handleSearch($input),
            "medicalRecords" => $this->medicalRecord->getAll($this->medicalRecord->handleSearch($input), true, $input['page_limit'] ?? 10),
        ];
    }


    public function getEditViewData($id): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("Doctors"),
            'subModuleTitle' => __("Medical Histories"),
            'subTitle' => __("Medical History View"),
        ];
        $view_data["doctors"] = (new User())->getDoctors();
        $view_data["patients"] = (new User())->getPatients();
        // $view_data["statuses"] = $this->medicalRecord->getStatuses();
        $view_data["model"] = $this->medicalRecord->findById($id);

        return $view_data;
    }

    public function view($id)
    {
        $record = $this->medicalRecord->findOrFail($id);
        $view_data = $this->getEditViewData($id);

        // Pass data to the view
        return view('medical_history.view', $view_data);
    }


}
