<?php

namespace App\Models;

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

    public function getAll($page_limit = 5)
    {
        return $this->paginate($page_limit);
    }
}
