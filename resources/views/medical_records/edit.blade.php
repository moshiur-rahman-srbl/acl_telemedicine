@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')
    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{ __('Edit Medical Record') }}
                    {{-- <a href="{{ route(config::get('constants.defines.APP_MEDICAL_RECORDS_INDEX')) }}"
                       class="ml-3 btn btn-sm btn-primary pull-right">
                        <i class="fa fa-list-ul"></i>&nbsp;{{ __('List') }}
                    </a> --}}
                </div>
            </div>
            <div class="ibox-body">

                <form role="form" action="{{ route(config('constants.defines.APP_MEDICAL_RECORDS_EDIT'),$record->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    {{-- @method('PUT') --}}
                    <div class="row">
                        <div class="col-md-8">
                            <div class="ibox">
                                <div class="ibox-body">
                                    <div class="form-group {{$errors->has('doctor_id') ? 'has-error':''}}">
                                        <label for="doctor_id">{{__('Doctor ')}}</label>
                                        <select class="form-control" name="doctor_id" id="doctor_id">
                                            <option value="">{{__('Select Doctor ')}}</option>
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ $record->doctor_id == $doctor->id ? 'selected' : '' }}>
                                                    {{ $doctor->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('doctor_id'))
                                            <label class="help-block error">{{__($errors->first('doctor_id'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('patient_id') ? 'has-error':''}}">
                                        <label for="patient_id">{{__('Patient ')}}</label>
                                        <select class="form-control" name="patient_id" id="patient_id">
                                            <option value="">{{__('Select Patient ')}}</option>
                                            @foreach($patients as $patient)
                                                <option
                                                    value="{{ $patient->id }}" {{ $record->patient_id == $patient->id ? 'selected' : '' }}>
                                                    {{ $patient->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('patient_id'))
                                            <label class="help-block error">{{__($errors->first('patient_id'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{ $errors->has('record_date') ? 'has-error' : '' }}">
                                        <label for="record_date">{{ __('Record Date') }}</label>
                                        <input type="datetime-local" class="form-control" name="record_date"
                                               id="record_date" value="{{ $record->record_date }}">
                                        @if($errors->has('record_date'))
                                            <label class="help-block error">{{ $errors->first('record_date') }}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{ $errors->has('diagonosis') ? 'has-error' : '' }}">
                                        <label for="diagonosis">{{ __('Diagnosis') }}</label>
                                        <textarea class="form-control" name="diagonosis" id="diagonosis" rows="4"
                                                  placeholder="{{ __('Enter the diagnosis') }}">{{ $record->diagnosis }}</textarea>
                                        @if($errors->has('diagonosis'))
                                            <label class="help-block error">{{ $errors->first('diagonosis') }}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{ $errors->has('treatments') ? 'has-error' : '' }}">
                                        <label for="treatments">{{ __('Treatments') }}</label>
                                        <textarea class="form-control" name="treatments" id="treatments" rows="4"
                                                  placeholder="{{ __('Enter the treatments') }}">{{ $record->treatments }}</textarea>
                                        @if($errors->has('treatments'))
                                            <label class="help-block error">{{ $errors->first('treatments') }}</label>
                                        @endif
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-8">
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary pull-right">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('css')
    @include('partials.css_blade.select2')
    @include('partials.css_blade.datetimepicker')
@endpush
@push('scripts')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.datetimepicker')
@endpush
