<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees'; 

    protected $fillable = [
        'name', 'email', 'phone', 'gender', 'address', 'designation'
    ];

    public function getAll($page_limit){

return $this->paginate($page_limit);
}

public function createData($input)
{
    return $this->create($input);  // Uses the Eloquent `create` method to insert the data
}
public function deleteData($id)
{
    $employee = $this->find($id);

    if ($employee) {
        // Perform deletion
        return $employee->delete();
    }

    return false; // Return false if the employee is not found
}


}