<?php

namespace App\Models;
use App\User;
use common\integration\Utility\Arr;
use Illuminate\Database\Eloquent\Model;

class Appoinment extends Model
{
    protected $guarded = [];
    const MOVE_TO_TRASH = "move_to_trash";

    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELED = 'canceled';

    /**
     * Get the list of possible statuses for appointments.
     */
    public function getStatuses(): array
    {
        return [
            self::STATUS_SCHEDULED => __("Scheduled"),
            self::STATUS_COMPLETED => __("Completed"),
            self::STATUS_CANCELED => __("Canceled"),
        ];
    }

    /**
     * Fetch all appointments with optional filters and pagination.
     */
    public function getAll($filters = [], $paginate = false, $page_limit = 10)
    {
        $query = $this->filters($filters);
        return $paginate ? $query->paginate($page_limit) : $query->get();
    }

    /**
     * Save a new appointment.
     */
    public function saveData($input)
    {
        return self::query()->create($input);
    }

    /**
     * Find an appointment by ID.
     */
    public function findById($id)
    {
        return self::query()->where("id", $id)->first();
    }

    /**
     * Update an existing appointment by ID.
     */
    public function updateData($id, $input)
    {
        return self::query()
            ->where("id", $id)
            ->update($input);
    }

    /**
     * Delete appointment(s) by ID(s).
     */
    public function deleteData($ids)
    {
        $query = self::query();
        if (Arr::isOfType($ids)) {
            $query->whereIn("id", $ids);
        } else {
            $query->where("id", $ids);
        }
        return $query->delete();
    }

    public function getPatients($filters = [], $paginate = false, $page_limit = 10)
    {
        $query = User::Query()->where('type',5);
        return $paginate ? $query->paginate($page_limit) : $query->get();
    }

 public function getDoctors($filters = [], $paginate = false, $page_limit = 10)
    {
        $query = User::Query()->where('type',2);
        return $paginate ? $query->paginate($page_limit) : $query->get();
    }




    /**
     * Filters for fetching appointments based on given criteria.
     */
    private function filters($filters = [])
    {
        $query = self::query();

        if (!empty($filters["status"])) {
            $query->where("status", $filters["status"]);
        }

        if (!empty($filters["doctor_id"])) {
            $query->where("doctor_id", $filters["doctor_id"]);
        }

        if (!empty($filters["patient_id"])) {
            $query->where("patient_id", $filters["patient_id"]);
        }

        if (!empty($filters["start_date"]) && !empty($filters["end_date"])) {
            $query->whereBetween('appointment_date', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters["filter_key"])) {
            $like_operator = "like";
            $query->where(function ($q) use ($filters, $like_operator) {
                $q->where('doctor_id', $like_operator, '%' . $filters["filter_key"] . '%')
                    ->orWhere('patient_id', $like_operator, '%' . $filters["filter_key"] . '%')
                    ->orWhere('status', $like_operator, '%' . $filters["filter_key"] . '%');
            });
        }

        return $query;
    }

    /**
     * Handles search inputs and returns a structured array of filters.
     */
    public function handleSearch($input): array
    {
        $filters["page_limit"] = $input["page_limit"] ?? 10;
        $filters["filter_key"] = $input["filter_key"] ?? '';
        $filters["status"] = $input["status"] ?? '';
        $filters["doctor_id"] = $input["doctor_id"] ?? '';
        $filters["patient_id"] = $input["patient_id"] ?? '';
        $filters["start_date"] = $input["start_date"] ?? '';
        $filters["end_date"] = $input["end_date"] ?? '';
        return $filters;
    }

    /**
     * Prepares data for inserting a new appointment.
     */
    public function prepareInsertData($input): array
    {
        return [
            "status" => $input["status"],
            "doctor_id" => $input["doctor_id"],
            "patient_id" => $input["patient_id"],
            "appointment_date_time" => $input["appointment_date_time"],
            "notes" => $input["notes"] ?? '', // Optional field for additional notes
        ];
    }

    /**
     * Validation rules and messages for appointments.
     */
    // public function validateData(): array
    // {
    //     $rules = [
    //         "status" => "required|string|in:1,2,3",
    //         // "doctor_id" => "required|integer|exists:users,id",
    //         // "patient_id" => "required|integer|exists:patients,id",
    //         // "appointment_date" => "required|date|after:today",
    //     ];

    //     $messages = [
    //         'status.required' => __("The status is required."),
    //         // 'doctor_id.required' => __("The doctor is required."),
    //         // 'patient_id.required' => __("The patient is required."),
    //         // 'appointment_date.after' => __("The appointment date must be in the future."),
    //     ];

    //     return [$rules, $messages];
    // }


    public function validateData(): array
    {
        $rules = [
            "doctor_id" => "required",
            "patient_id" => "required",
            "appointment_date_time" => "required",
            "notes" => "required|string|max:255",
            "status" => "required",

        ];
    
        $messages = [
        //     'doctor_id.required' => __("The doctor is required."),
        // 'name.max' => __("The name must not exceed 191 characters."),
        // 'email.required' => __("The email is required."),
        // 'email.email' => __("The email must be a valid email address."),
        // 'email.unique' => __("The email has already been taken."),
        //     'phone.required' => __("The phone number is required."),
        //     'phone.digits_between' => __("The phone number must not exceed 50 characters."),
        //     'address.required' => __("The address is required."),
        //     'address.max' => __("The address must not exceed 255 characters."),
        ];
    
        return [$rules, $messages];
    }
}