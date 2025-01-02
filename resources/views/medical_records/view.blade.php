@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')
    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}}
                    <a href="{{ route(Config::get('constants.defines.APP_MEDICAL_RECORDS_INDEX')) }}"
                       class="ml-3 btn btn-sm btn-primary pull-right"><i class="fa fa-list-ul"></i> {{ __('List') }}</a>
                </div>
            </div>
            <div class="ibox-body">
                <form role="form">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="ibox">
                                <div class="ibox-body">
                                    <div class="form-group {{$errors->has('doctor_id') ? 'has-error':''}}">
                                        <label for="doctor_id">{{__('Doctor ID')}}</label>
                                        <input type="text" class="form-control" name="doctor_id" id="doctor_id" disabled
                                               placeholder="{{__('Doctor ID')}}"
                                               value="{{ $model->doctor_id }}">
                                        @if($errors->has('doctor_id'))
                                            <label class="help-block error">{{__($errors->first('doctor_id'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('patient_id') ? 'has-error':''}}">
                                        <label for="patient_id">{{__('Patient ID')}}</label>
                                        <input type="text" class="form-control" name="patient_id" id="patient_id" disabled
                                               placeholder="{{__('Patient ID')}}"
                                               value="{{ $model->patient_id }}">
                                        @if($errors->has('patient_id'))
                                            <label class="help-block error">{{__($errors->first('patient_id'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('record_date') ? 'has-error':''}}">
                                        <label for="record_date">{{__('Record Date')}}</label>
                                        <input type="datetime-local" class="form-control" name="record_date" id="record_date" disabled
                                               value="{{ $model->record_date }}">
                                        @if($errors->has('record_date'))
                                            <label class="help-block error">{{__($errors->first('record_date'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('diagonosis') ? 'has-error':''}}">
                                        <label for="diagonosis">{{__('Diagnosis')}}</label>
                                        <textarea class="form-control" name="diagonosis" id="diagonosis" disabled rows="4"
                                                  placeholder="{{__('Diagnosis Details')}}">{{ $model->diagonosis }}</textarea>
                                        @if($errors->has('diagonosis'))
                                            <label class="help-block error">{{__($errors->first('diagonosis'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('treatments') ? 'has-error':''}}">
                                        <label for="treatments">{{__('Treatments')}}</label>
                                        <textarea class="form-control" name="treatments" id="treatments" disabled rows="4"
                                                  placeholder="{{__('Treatment Details')}}">{{ $model->treatments }}</textarea>
                                        @if($errors->has('treatments'))
                                            <label class="help-block error">{{__($errors->first('treatments'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('attachments') ? 'has-error':''}}">
                                        <label for="attachments">{{__('Attachments')}}</label>
                                        <input type="text" class="form-control" name="attachments" id="attachments" disabled
                                               placeholder="{{__('Attachment URL')}}"
                                               value="{{ $model->attachments }}">
                                        @if($errors->has('attachments'))
                                            <label class="help-block error">{{__($errors->first('attachments'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label for="created_at">{{__('Created At')}}</label>
                                        <input type="text" class="form-control" name="created_at" id="created_at" disabled
                                               value="{{ $model->created_at }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="updated_at">{{__('Updated At')}}</label>
                                        <input type="text" class="form-control" name="updated_at" id="updated_at" disabled
                                               value="{{ $model->updated_at }}">
                                    </div>
                                </div>
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
@endpush

@push('scripts')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.datepicker')
    @include('partials.js_blade.datetimepicker')
@endpush
