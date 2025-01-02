@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')
    <div class="page-content fade-in-up">
        @include('partials.flash')
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{ __('Create Medical Record') }}
                </div>
            </div>
            <div class="ibox-body">
                <form role="form" action="{{ route(config('constants.defines.APP_MEDICAL_RECORDS_CREATE')) }}" method="post"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-8">
                            <div class="ibox">
                                <div class="ibox-body">
                                    <!-- <div class="form-group {{$errors->has('doctor_id') ? 'has-error':''}}">
                                        <label for="doctor_id">{{__('Doctor ID')}}</label>
                                        <input type="text" class="form-control" name="doctor_id" id="doctor_id"
                                               placeholder="{{__('Doctor ID')}}"
                                               value="{{ old('doctor_id') }}">
                                        @if($errors->has('doctor_id'))
                                            <label class="help-block error">{{__($errors->first('doctor_id'))}}</label>
                                        @endif
                                    </div> -->
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
                                        @if($errors->has('doctor_id'))
                                            <label class="help-block error">{{__($errors->first('doctor_id'))}}</label>
                                        @endif
                                    </div>
                                   
                                    {{-- <div class="form-group {{$errors->has('patient_id') ? 'has-error':''}}">
                                        <label for="patient_id">{{__('Patient')}}</label>
                                        <select class="form-control" name="patient_id" id="patient_id">
                                            <option value="">{{__('Select Patient')}}</option>
                                            @foreach($patients as $patient)
                                                <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                                    {{ $patient->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('patient_id'))
                                            <label class="help-block error">{{__($errors->first('patient_id'))}}</label>
                                        @endif
                                    </div> --}}
                    {{-- <div class="row">
                        <div class="col-md-8">
                            <div class="ibox">
                                <div class="ibox-body">
                                    <div class="form-group {{$errors->has('doctor_id') ? 'has-error':''}}">
                                        <label for="doctor_id">{{ __('Doctor ID') }}</label>
                                        <input type="text" class="form-control" name="doctor_id" id="doctor_id"
                                               placeholder="{{ __('Enter Doctor ID') }}"
                                               value="{{ old('doctor_id') }}">
                                        @if($errors->has('doctor_id'))
                                            <label class="help-block error">{{ $errors->first('doctor_id') }}</label>
                                        @endif
                                    </div>--}}

                                    <div class="form-group {{$errors->has('patient_id') ? 'has-error':''}}">
                                        <label for="patient_id">{{ __('Patient ID') }}</label>
                                        <input type="text" class="form-control" name="patient_id" id="patient_id"
                                               placeholder="{{ __('Enter Patient ID') }}" value="{{ old('patient_id') }}">
                                        @if($errors->has('patient_id'))
                                            <label class="help-block error">{{ $errors->first('patient_id') }}</label>
                                        @endif
                                    </div> 


                                    <div class="form-group {{$errors->has('record_date') ? 'has-error':''}}">
                                        <label for="record_date">{{ __('Record Date') }}</label>
                                        <input type="date" class="form-control" name="record_date" id="record_date"
                                               value="{{ old('record_date') }}">
                                        @if($errors->has('record_date'))
                                            <label class="help-block error">{{ $errors->first('record_date') }}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('diagnosis') ? 'has-error':''}}">
                                        <label for="diagnosis">{{ __('Diagnosis') }}</label>
                                        <textarea class="form-control" name="diagnosis" id="diagnosis" rows="3"
                                                  placeholder="{{ __('Enter Diagnosis') }}">{{ old('diagnosis') }}</textarea>
                                        @if($errors->has('diagnosis'))
                                            <label class="help-block error">{{ $errors->first('diagnosis') }}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('treatments') ? 'has-error':''}}">
                                        <label for="treatments">{{ __('Treatments') }}</label>
                                        <textarea class="form-control" name="treatments" id="treatments" rows="3"
                                                  placeholder="{{ __('Enter Treatments') }}">{{ old('treatments') }}</textarea>
                                        @if($errors->has('treatments'))
                                            <label class="help-block error">{{ $errors->first('treatments') }}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('attachments') ? 'has-error':''}}">
                                        <label for="attachments">{{ __('Attachments') }}</label>
                                        <input type="file" class="form-control" name="attachments" id="attachments">
                                        @if($errors->has('attachments'))
                                            <label class="help-block error">{{ $errors->first('attachments') }}</label>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary pull-right">{{ __('Save') }}</button>
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
    @include('partials.css_blade.datepicker')
@endpush

@push('scripts')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.datepicker')
@endpush
