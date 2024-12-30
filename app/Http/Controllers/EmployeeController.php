<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    private Employee $employee;

    public function __construct()
    {
        $this->employee = new Employee();
    }

    public function index(Request $request)
    {
        $input = $request->all();
        $view_data = $this->getIndexViewData($input);
        return view("employees.index", $view_data);
    }

    public function getIndexViewData($input): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("EmployeeControl"),
            'subModuleTitle' => __("EmployeeManagement"),
            'subTitle' => __("EmployeeList")
        ];
        $view_data["filters"] = $this->employee->handleSearch($input);
        $view_data["models"] = $this->employee->getAll($view_data["filters"], true, $view_data["filters"]["page_limit"]);
        $view_data["page_limit"] = $view_data["filters"]["page_limit"];
        $view_data["genders"] = $this->employee->getGender();
        return $view_data;
    }

    public function create(Request $request)
    {
        if ($request->isMethod("POST")) {

            $input = $request->all();
            [$rule, $message] = $this->employee->validateData();
            $validate = Validator::make($input, $rule, $message);
            if ($validate->fails()) {
                flash(__($validate->errors()->first()), 'danger');
                return redirect()->back();
            }

            $store_data = $this->employee->prepareInsertData($input);
            $mployee = $this->employee->saveData($store_data);

            if (!empty($mployee)) {
                flash(__('Saved successfully!'), 'success');
            } else {
                flash(__('Some Errors!'), 'danger');
            }
            return redirect(route(Config::get('constants.defines.APP_EMPLOYEE_INDEX')));

        }

        $view_data = $this->getCreateViewData();
        return view("employees.create", $view_data);
    }

    public function getCreateViewData(): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("Employee ontrol"),
            'subModuleTitle' => __("Employee Management"),
            'subTitle' => __("Employee Create")
        ];
        $view_data["genders"] = $this->employee->getGender();
        return $view_data;
    }

    public function edit(Request $request, $id)
    {

        if ($request->isMethod("POST")) {

            $input = $request->all();
            [$rule, $message] = $this->employee->validateData();
            $validate = Validator::make($input, $rule, $message);
            if ($validate->fails()) {
                flash(__($validate->errors()->first()), 'danger');
                return redirect()->back();
            }

            $update_data = $this->employee->prepareInsertData($input);
            $mployee = $this->employee->updateData($id, $update_data);

            if (!empty($mployee)) {
                flash(__('Update successfully!'), 'success');
            } else {
                flash(__('Some Errors!'), 'danger');
            }
            return redirect(route(Config::get('constants.defines.APP_EMPLOYEE_INDEX')));
        }

        $view_data = $this->getEditViewData($id);
        return view("employees.edit", $view_data);
    }

    public function getEditViewData($id): array
    {
        $view_data["cmsInfo"] = [
            'moduleTitle' => __("Employee Control"),
            'subModuleTitle' => __("Employee Management"),
            'subTitle' => __("Employee Edit")
        ];
        $view_data["genders"] = $this->employee->getGender();
        $view_data["model"] = $this->employee->findById($id);
        return $view_data;
    }

    public function destroy(Request $request, $id)
    {
        if (!empty($request->action) && $request->action == Employee::MOVE_TO_TRASH) {
            $ids = $request->input('ids', []);
        } else {
            $ids = $id;
        }
        $mployee = $this->employee->deleteData($ids);
        if ($mployee) {
            flash(__('Delete successfully!'), 'success');
        } else {
            flash(__('Some Errors!'), 'danger');
        }
        return redirect()->back();
    }
}
