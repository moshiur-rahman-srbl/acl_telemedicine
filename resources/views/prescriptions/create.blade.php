@extends('layouts.adminca')

@section('content')
<div class="container">
    <h1>Create Prescription</h1>
    <form action="{{ route('prescriptions.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="appointment_id">Appointment ID</label>
            <input type="text" class="form-control" id="appointment_id" name="appointment_id" required>
        </div>
        <div class="form-group">
            <label for="doctor_id">Doctor ID</label>
            <input type="text" class="form-control" id="doctor_id" name="doctor_id" required>
        </div>
        <div class="form-group">
            <label for="patient_id">Patient ID</label>
            <input type="text" class="form-control" id="patient_id" name="patient_id" required>
        </div>
        <div class="form-group">
            <label for="prescription_date">Prescription Date</label>
            <input type="date" class="form-control" id="prescription_date" name="prescription_date" required>
        </div>
        <div class="form-group">
            <label for="medications">Medications</label>
            <textarea class="form-control" id="medications" name="medications" required></textarea>
        </div>
        <div class="form-group">
            <label for="instructions">Instructions</label>
            <textarea class="form-control" id="instructions" name="instructions" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
