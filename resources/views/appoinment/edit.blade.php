@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')
    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <!-- <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}} <a
                        href="{{route(Config::get('constants.defines.APP_APPOINMENT_INDEX'))}}"
                        class="ml-3 btn btn-sm btn-primary pull-right"><i class="fa fa-list-ul"></i> {{__('List')}}</a>
                </div> -->
            </div>
            <div class="ibox-body">
                <form role="form" action="{{route(Config::get('constants.defines.APP_APPOINMENT_EDIT'), $model->id)}}"
                      method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="ibox">
                                <div class="ibox-body">
                                    <div class="form-group {{$errors->has('doctor_id') ? 'has-error':''}}">
                                        <label for="doctor_id">{{__('Doctor ID')}}</label>
                                        <input type="text" class="form-control" name="doctor_id" id="doctor_id"
                                               placeholder="{{__('Doctor ID')}}"
                                               value="{{ $model->doctor_id }}">
                                        @if($errors->has('doctor_id'))
                                            <label class="help-block error">{{__($errors->first('doctor_id'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('patient_id') ? 'has-error':''}}">
                                        <label for="patient_id">{{__('Patient ID')}}</label>
                                        <input type="text" class="form-control" name="patient_id" id="patient_id"
                                               placeholder="{{__('Patient ID')}}" value="{{ $model->patient_id }}">
                                        @if($errors->has('patient_id'))
                                            <label class="help-block error">{{__($errors->first('patient_id'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('appointment_date_time') ? 'has-error':''}}">
                                        <label for="appointment_date_time">{{__('Appointment Date & Time')}}</label>
                                        <input type="datetime-local" class="form-control" name="appointment_date_time"
                                               id="appointment_date_time"
                                               value="{{ $model->appointment_date_time }}">
                                        @if($errors->has('appointment_date_time'))
                                            <label class="help-block error">{{__($errors->first('appointment_date_time'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('notes') ? 'has-error':''}}">
                                        <label for="notes">{{__('Notes')}}</label>
                                        <textarea class="form-control" name="notes" id="notes" rows="4"
                                                  placeholder="{{__('Enter any notes')}}">{{ $model->notes }}</textarea>
                                        @if($errors->has('notes'))
                                            <label class="help-block error">{{__($errors->first('notes'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('status') ? 'has-error':''}}">
                                        <label for="status">{{__('Status')}}</label>
                                        <select class="form-control" name="status" id="status">
                                            <option value="1" {{ $model->status == 1 ? 'selected' : '' }}>{{ __('Scheduled') }}</option>
                                            <option value="2" {{ $model->status == 2 ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                            <option value="3" {{ $model->status == 3 ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                                        </select>
                                        @if($errors->has('status'))
                                            <label class="help-block error">{{__($errors->first('status'))}}</label>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary pull-right">{{__('Update')}}</button>
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
    @include('partials.css_blade.datetimepicker')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.clockpicker')
    @include('partials.css_blade.clockpicker')
    @include('partials.css_blade.ionRangeSlider')
    @include('partials.css_blade.bootstrap-tagsinput')
    @include('partials.css_blade.bootstrap-touchspin')
    @include('partials.css_blade.multi-select')
    @include('partials.css_blade.intlTelInput_new')
    <style>
        .custom-file-label::after {
            content: "{{ __('Browse') }}" !important;
        }
    </style>
@endpush
@push('scripts')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.datepicker')
    @include('partials.js_blade.datetimepicker')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.clockpicker')
    @include('partials.js_blade.knob')
    @include('partials.js_blade.ionRangeSlider')
    @include('partials.js_blade.bootstrap-tagsinput')
    @include('partials.js_blade.bootstrapMaxLength')
    @include('partials.js_blade.bootstrap-touchspin')
    @include('partials.js_blade.multi-select')
    @include('partials.js_blade.intlTelInput_new')
@endpush
