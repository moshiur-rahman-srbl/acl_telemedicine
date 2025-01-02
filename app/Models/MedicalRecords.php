<?php

namespace App\Models;

use common\integration\Utility\Arr;
use Illuminate\Database\Eloquent\Model;
use App\User;
class MedicalRecords extends Model
{
    protected $guarded = [];
    const MOVE_TO_TRASH = "move_to_trash";

    /**
     * Fetch all medical records with optional filters and pagination.
     */
    public function getAll($filters = [], $paginate = false, $page_limit = 10)
    {
        $query = $this->filters($filters);
        return $paginate ? $query->paginate($page_limit) : $query->get();
    }

    public function getDoctors($filters = [], $paginate = false, $page_limit = 10)
    {
        return User::query()->where('type',5)->get();
    }

    /**
     * Save a new medical record.
     */
    public function saveData($input)
    {
        return self::query()->create($input);
    }

    /**
     * Find a medical record by ID.
     */
    public function findById($id)
    {
        return self::query()->where("id", $id)->first();
    }

    /**
     * Update an existing medical record by ID.
     */
    public function updateData($id, $input)
    {
        return self::query()
            ->where("id", $id)
            ->update($input);
    }

    /**
     * Delete medical record(s) by ID(s).
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

    /**
     * Filters for fetching medical records based on given criteria.
     */
    private function filters($filters = [])
    {
        $query = self::query();

        if (!empty($filters["doctor_id"])) {
            $query->where("doctor_id", $filters["doctor_id"]);
        }

        if (!empty($filters["patient_id"])) {
            $query->where("patient_id", $filters["patient_id"]);
        }

        if (!empty($filters["start_date"]) && !empty($filters["end_date"])) {
            $query->whereBetween('record_date', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters["filter_key"])) {
            $like_operator = "like";
            $query->where(function ($q) use ($filters, $like_operator) {
                $q->where('doctor_id', $like_operator, '%' . $filters["filter_key"] . '%')
                    ->orWhere('patient_id', $like_operator, '%' . $filters["filter_key"] . '%')
                    ->orWhere('diagnosis', $like_operator, '%' . $filters["filter_key"] . '%');
            });
        }

        return $query;
    }

    /**
     * Handles search inputs and returns a structured array of filters.
     */
    public function handleSearch($input): array
    {
        return [
            "page_limit" => $input["page_limit"] ?? 10,
            "filter_key" => $input["filter_key"] ?? '',
            "doctor_id" => $input["doctor_id"] ?? '',
            "patient_id" => $input["patient_id"] ?? '',
            "start_date" => $input["start_date"] ?? '',
            "end_date" => $input["end_date"] ?? '',
        ];
    }

    /**
     * Prepares data for inserting a new medical record.
     */
    public function prepareInsertData($input): array
    {
        return [
            "doctor_id" => $input["doctor_id"],
            "patient_id" => $input["patient_id"],
            "record_date" => $input["record_date"],
            "diagnosis" => $input["diagnosis"] ?? '', // Optional
            "treatments" => $input["treatments"] ?? '', // Optional
            "attachments" => $input["attachments"] ?? '', // Optional
        ];
    }

    /**
     * Validation rules and messages for medical records.
     */
    public function validateData(): array
    {
        $rules = [
            "doctor_id" => "required|integer|exists:users,id",
           "patient_id" => "required|integer|exists:users,id",
            "record_date" => "required|date",
            "diagnosis" => "nullable|string",
            "treatments" => "nullable|string",
            "attachments" => "nullable|string|max:250",
        ];

        $messages = [
            'doctor_id.required' => __("The doctor ID is required."),
            'patient_id.required' => __("The patient ID is required."),
            'record_date.required' => __("The record date is required."),
            'attachments.max' => __("The attachments field must not exceed 250 characters."),
        ];

        return [$rules, $messages];
    }
}
