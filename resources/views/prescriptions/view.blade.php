@extends('layouts.adminca')

@section('content')
<div class="container"><br><br>
    <h1 class="text-center"> Prescription</h1><br><br>
    <form action="{{ route(config('constants.defines.APP_PRESCRIPTION_EDIT'), $prescription) }}" method="POST">
    @csrf

        <div class="form-group">
            <label for="appointment_id">Appointment ID</label>
            <input type="text" class="form-control" id="appointment_id" name="appointment_id" value="{{ $prescription->appointment_id }}" required>
        </div>

        <div class="form-group">
            <label for="doctor_id">Doctor ID</label>
            <input type="text" class="form-control" id="doctor_id" name="doctor_id" value="{{ $prescription->doctor_id }}" required>
        </div>

        <div class="form-group">
            <label for="patient_id">Patient ID</label>
            <input type="text" class="form-control" id="patient_id" name="patient_id" value="{{ $prescription->patient_id }}" required>
        </div>

        <div class="form-group">
            <label for="prescription_date">Prescription Date</label>
            <input type="date" class="form-control" id="prescription_date" name="prescription_date" value="{{ $prescription->prescription_date }}" required>
        </div>

        <div class="form-group">
            <label for="medications">Medications</label>
            <textarea class="form-control" id="medications" name="medications" required>{{ $prescription->medications }}</textarea>
        </div>

        <div class="form-group">
            <label for="instructions">Instructions</label>
            <textarea class="form-control" id="instructions" name="instructions" required>{{ $prescription->instructions }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
