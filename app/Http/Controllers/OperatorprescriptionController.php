<?php

namespace App\Http\Controllers;

use App\Models\Appoinment;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Models\Prescription;

class OperatorprescriptionController extends Controller
{
    private Prescription $prescription;

    public function __construct()
    {
        $this->prescription = new Prescription();
    }

    public function index(Request $request)
    {
        $input = $request->all();
        $view_data = $this->getIndexViewData($input);
        return view("prescriptions.index", $view_data);
    }

    public function getIndexViewData($input): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("Prescription Data"),
            'subModuleTitle' => __("Prescription Management"),
            'subTitle' => __("Prescription List")
        ];
        //$view_data["filters"] = $this->prescription->handleSearch($input);
        $view_data["filters"] = $filters = $this->prescription->handleSearch($input);
        $view_data["prescriptions"] = (new Prescription())->getAll($filters);
        $view_data["page_limit"] = $view_data;
        return $view_data;
    }

    public function create(Request $request)
    {
        if ($request->isMethod("POST")) {
            $input = $request->all();
            $rule = [
                'appointment_id' => 'required|integer',
                'prescription_date' => 'required|date',
                'medications' => 'required|string',
                'instructions' => 'nullable|string',
            ];
            $validate = Validator::make($input, $rule);
            if ($validate->fails()) {
                flash(($validate->errors()->first()), 'danger');
                return redirect()->back();
            }

            Prescription::create($input);

            flash(('Saved successfully!'), 'success');
            return redirect(route(Config::get('constants.defines.APP_PRESCRIPTION_INDEX')));
        }

        $view_data = $this->getCreateViewData();

        return view("prescriptions.create", $view_data);
    }

    public function getCreateViewData(): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("Prescription Data"),
            'subModuleTitle' => __("Prescription Management"),
            'subTitle' => __("Create Prescription")
        ];
        $view_data["appointments"] = Appoinment::all();
        $view_data["doctors"] = (new User())->getDoctors();
        $view_data["patients"] = (new User())->getPatients();
        $view_data["prescriptions"] = $this->prescription->getAll();
        return $view_data;
    }

    public function edit(Request $request, $id)
    {
        if ($request->isMethod("POST")) {

            $input = $request->all();
            $rule = [
                'appointment_id' => 'required|integer',
                'prescription_date' => 'required|date',
                'medications' => 'required|string',
                'instructions' => 'nullable|string',
            ];
            $validate = Validator::make($input, $rule);
            if ($validate->fails()) {
                flash(($validate->errors()->first()), 'danger');
                return redirect()->back();
            }

            $prescription = Prescription::findOrFail($id);
            $prescription->update($input);

            flash(('Update successfully!'), 'success');
            return redirect(route(Config::get('constants.defines.APP_PRESCRIPTION_INDEX')));
        }

        $view_data = $this->getEditViewData($id);
        return view("prescriptions.edit", $view_data);
    }

    public function getEditViewData($id): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("Prescription Data"),
            'subModuleTitle' => __("Prescription Management"),
            'subTitle' => __("Edit Prescription")
        ];
        $view_data["doctors"] = (new User())->getDoctors();
        $view_data["patients"] = (new User())->getPatients();
        $view_data["prescriptions"] = $this->prescription->getAll();
        $view_data["prescription"] = Prescription::findOrFail($id);
        $view_data["appointments"] = Appoinment::all();
        return $view_data;
    }

    public function view($id)
    {
        $prescription = Prescription::findOrFail($id);
        $view_data = $this->getEditViewData($id);

        return view('prescriptions.view', $view_data);
    }

    public function destroy(Request $request, $id)
    {
        $prescription = Prescription::findOrFail($id);
        $prescription->delete();

        flash(('Delete successfully!'), 'success');
        return redirect()->back();
    }
}
