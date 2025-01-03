@extends('layouts.adminca')

@section('content')
<div class="container"><br><br>
    <h1 class="text-center"> Prescription</h1><br><br>
    <form action="{{ route(config('constants.defines.APP_PRESCRIPTION_EDIT'), $prescription) }}" method="POST">
    @csrf

        <div class="form-group {{$errors->has('appointment_id') ? 'has-error':''}}">
            <label for="appointment_id">{{__('Appointments ')}}</label>
            <select class="form-control" name="appointment_id" id="appointment_id" disabled>
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
            <input type="date" class="form-control" id="prescription_date" name="prescription_date" value="{{ $prescription->prescription_date }}" required disabled>
        </div>

        <div class="form-group">
            <label for="medications">Medications</label>
            <textarea class="form-control" id="medications" name="medications" required disabled>{{ $prescription->medications }}</textarea>
        </div>

        <div class="form-group">
            <label for="instructions">Instructions</label>
            <textarea class="form-control" id="instructions" name="instructions" required disabled>{{ $prescription->instructions }}</textarea>
        </div>

        <div class="row">
            <div class="col-sm-8">
                <a href="{{ route(Config::get('constants.defines.APP_PRESCRIPTION_INDEX')) }}"
                   class="btn btn-secondary">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
