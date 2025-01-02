<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Support\Facades\Config;

class EmployeeController extends Controller
{
    //
    public function index(Request $request){
        $page_limit = $request->input("page_limit", 10);
        $employees = (new Employee())->getAll($page_limit);
        $cmsInfo = [
            'moduleTitle' => __("Master Data"),
            'subModuleTitle' => __("Page Management"),
            'subTitle' => __("Page List")
        ];
        
        
        return view('employees.index', compact('employees', 'cmsInfo','page_limit'));

    }

public function create(Request $request){
    {
        if ($request->isMethod('post')) {
            $input = $request->all();
            $request->validate([
                'name' => 'required|string|max:255',  
                'email' => 'required|email|unique:employees,email', 
                'phone' => 'required|string|max:20',  
                'gender' => 'required|in:male,female,other', 
                'designation' => 'required|string|max:255',  
            ]);

            $employee = (new Employee())->createData($input);
            if(!empty($employee)){
                flash(__('The record has been saved successfully!'), 'success');
            }else{
                flash(__('The record has been saved successfully!'), 'success');
            }

        }

        
        $cmsInfo = [
            'moduleTitle' => __("Master Data"),
            'subModuleTitle' => __("Employee Management"),
            'subTitle' => __("Add New Employee"),
        ];

        return view('employees.create', compact('cmsInfo'));
    }

}
public function destroy($id)
{
    $employee = Employee::find($id);

    if ($employee) {
        $employee->delete();
        return redirect()->route(Config::get('constants.defines.APP_EMPLOYEE_INDEX'))->with('success', 'Employee deleted successfully.');
    }

    return redirect()->route(Config::get('constants.defines.APP_EMPLOYEE_INDEX'))->with('error', 'Employee not found.');
}

public function edit($id)
{
    $employee = Employee::findOrFail($id);

    $cmsInfo = [
        'moduleTitle' => __("Employee Data"),
        'subModuleTitle' => __("Employee Management"),
        'subTitle' => __("Edit Employee")
    ];

    return view('employees.edit', compact('employee', 'cmsInfo'));
}
public function update(Request $request, $id)
{
    $employee = Employee::findOrFail($id);

    $request->validate([
       
        'email' => 'required|email|unique:employees,email,' . $id,
        
    ]);

    $employee->update($request->all());

    return redirect()->route('employees.index')->with('success', 'Employee updated successfully!');
}

}