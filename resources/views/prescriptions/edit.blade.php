@extends('layouts.adminca')

@section('content')
<div class="container"><br><br>
    <h1 class="text-center">Edit Prescription</h1><br><br>
    <form action="{{ route(Config::get('constants.defines.APP_PRESCRIPTION_EDIT'), $prescription->id) }}" method="POST">
        @csrf

        <div class="form-group {{$errors->has('appointment_id') ? 'has-error':''}}">
            <label for="appointment_id">{{__('Appointments ')}}</label>
            <select class="form-control" name="appointment_id" id="appointment_id">
                <option value="">{{__('Select Appointment ')}}</option>
                @foreach($appointments as $appointment)
                    <option value="{{ $appointment->id }}" {{ $prescription->appointment_id == $appointment->id ? 'selected' : '' }}>

                        {{ $appointment->id }} => Patient: {{$appointment->Patient->name ?? ''}}
                    </option>
                @endforeach
            </select>
            @if($errors->has('appointment_id'))
                <label class="help-block error">{{__($errors->first('appointment_id'))}}</label>
            @endif
        </div>



        <div class="form-group">
            <label for="prescription_date">Prescription Date</label>
            <input type="date" class="form-control" id="prescription_date" name="prescription_date" value="{{ old('prescription_date', $prescription->prescription_date) }}" required>
        </div>

        <div class="form-group">
            <label for="medications">Medications</label>
            <textarea class="form-control" id="medications" name="medications" required>{{ old('medications', $prescription->medications) }}</textarea>
        </div>

        <div class="form-group">
            <label for="instructions">Instructions</label>
            <textarea class="form-control" id="instructions" name="instructions" required>{{ old('instructions', $prescription->instructions) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
