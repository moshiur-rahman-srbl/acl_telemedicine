@extends('layouts.adminca')

@section('content')
<div class="container">
    <br><br>
    
    <h1 class="text-center">Create Prescription Form</h1><br><br>
    <form  method="POST">
        @csrf


        <div class="form-group">
            <label for="appointment_id">Appointment ID</label>
            <input type="text" class="form-control" id="appointment_id" name="appointment_id" required>
        </div>
        
   


         <div class="form-group {{$errors->has('doctor_id') ? 'has-error':''}}">
        <label for="doctor_id">{{__('Doctor ')}}</label>
          <select class="form-control" name="doctor_id" id="doctor_id">
               <option value="">{{__('Select Doctor ')}}</option>
                @foreach($doctors as $doctor)
                   <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                     {{ $doctor->name }}
                   </option>
                @endforeach
         </select>

         <div class="form-group {{$errors->has('patient_id') ? 'has-error':''}}">
        <label for="patient_id">{{__('Patient ')}}</label>
          <select class="form-control" name="patient_id" id="patient_id">
               <option value="">{{__('Select Patient ')}}</option>
                @foreach($patients as $patient)
                   <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                     {{ $patient->name }}
                   </option>
                @endforeach
         </select>

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
