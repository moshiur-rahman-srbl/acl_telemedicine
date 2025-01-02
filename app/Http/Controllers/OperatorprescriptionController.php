<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prescription;

class OperatorprescriptionController extends Controller
{
    /**
     * Display a listing of prescriptions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cmsInfo = [
            'moduleTitle' => __("Prescription Data"),
            'subModuleTitle' => __("Prescription Management"),
            'subTitle' => __("Prescription List")
        ];

        $page_limit = 10;
        $prescriptions = (new Prescription())->getAll($page_limit);

        return view('prescriptions.index', compact('prescriptions', 'cmsInfo', 'page_limit'));
    }

    /**
     * Show the form for creating a new prescription.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $cmsInfo = [
            'moduleTitle' => __("Prescription Data"),
            'subModuleTitle' => __("Prescription Management"),
            'subTitle' => __("Create Prescription")
        ];

        return view('prescriptions.create', ['cmsInfo' => $cmsInfo]);
    }

    /**
     * Store a newly created prescription in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|integer',
            'doctor_id' => 'required|integer',
            'patient_id' => 'required|integer',
            'prescription_date' => 'required|date',
            'medications' => 'required|string',
            'instructions' => 'nullable|string',
        ]);

        Prescription::create($request->all());

        return redirect()->back()->with('success', 'Prescription created successfully!');
    }

    /**
     * Display the specified prescription.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $prescription = Prescription::findOrFail($id);

        return view('prescriptions.show', compact('prescription'));
    }

    /**
     * Show the form for editing the specified prescription.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $prescription = Prescription::findOrFail($id);

        $cmsInfo = [
            'moduleTitle' => __("Prescription Data"),
            'subModuleTitle' => __("Prescription Management"),
            'subTitle' => __("Edit Prescription")
        ];

        return view('prescriptions.edit', compact('prescription', 'cmsInfo'));
    }

    /**
     * Update the specified prescription in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $prescription = Prescription::findOrFail($id);

        $request->validate([
            'appointment_id' => 'required|integer',
            'doctor_id' => 'required|integer',
            'patient_id' => 'required|integer',
            'prescription_date' => 'required|date',
            'medications' => 'required|string',
            'instructions' => 'nullable|string',
        ]);

        $prescription->update($request->all());

        return redirect()->back()->with('success', 'Prescription updated successfully!');
    }


    

    /**
     * Remove the specified prescription from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $prescription = Prescription::findOrFail($id);
        $prescription->delete();

        return redirect()->back()->with('success', 'Prescription deleted successfully!');
    }
}
