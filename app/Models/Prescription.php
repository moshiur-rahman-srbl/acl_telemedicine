<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $table = 'prescriptions';

    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'patient_id',
        'prescription_date',
        'medications',
        'instructions',
        'created_at',
        'updated_at'
    ];

    public function getAll($filters = [],$page_limit = 10)
    {
        return $this->filters($filters)->paginate($page_limit);
    }

    public function Appointment()
    {
        return $this->belongsTo(Appoinment::class, 'appointment_id');
    }

    public function handleSearch($input): array
    {
        $filters["page_limit"] = $input["page_limit"] ?? 10;
        $filters["filter_key"] = $input["filter_key"] ?? '';
        $filters["appointment_id"] = $input["appointment_id"] ?? '';
        $filters["start_date"] = $input["start_date"] ?? '';
        $filters["end_date"] = $input["end_date"] ?? '';

        return $filters;
    }

    private function filters($filters = [])
    {
        $query = self::query();

        if (!empty($filters["appointment_id"])) {
            $query->where("appointment_id", $filters["appointment_id"]);
        }



        if (!empty($filters["start_date"]) && !empty($filters["end_date"])) {
            $query->whereBetween('record_date', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters["filter_key"])) {
            $like_operator = "like";
            $query->where(function ($q) use ($filters, $like_operator) {
                $q->where('appointment_id', $like_operator, '%' . $filters["filter_key"] . '%')
                    ->orWhere('id', $like_operator, '%' . $filters["filter_key"] . '%');
            });
        }

        return $query;
    }
}
