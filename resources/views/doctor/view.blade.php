@extends('layouts.adminca')

@section('content')
    @include('partials.page_heading')

    <div class="page-content fade-in-up">
        @include('partials.flash')

        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{ $cmsInfo['subTitle'] }}
                    <a href="{{ route(Config::get('constants.defines.APP_DOCTOR_INDEX')) }}" 
                       class="ml-3 btn btn-sm btn-primary pull-right">
                        <i class="fa fa-list-ul"></i> {{ __('List') }}
                    </a>
                </div>
            </div>

            <div class="ibox-body">
                <form>
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">{{ __('Name') }}</label>
                                <input type="text" class="form-control" name="name" id="name" disabled 
                                       value="{{ $doctor->name }}" placeholder="{{ __('Name') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">{{ __('Email') }}</label>
                                <input type="email" class="form-control" name="email" id="email" disabled 
                                       value="{{ $doctor->email }}" placeholder="{{ __('Email') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="license_number">{{ __('License Number') }}</label>
                                <input type="text" class="form-control" name="license_number" id="license_number" disabled 
                                       value="{{ $doctor->license_number }}" placeholder="{{ __('License Number') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="speciality">{{ __('Speciality') }}</label>
                                <input type="text" class="form-control" name="speciality" id="speciality" disabled 
                                       value="{{ $doctor->speciality }}" placeholder="{{ __('Speciality') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="experience_years">{{ __('Experience Years') }}</label>
                                <input type="number" class="form-control" name="experience_years" id="experience_years" disabled 
                                       value="{{ $doctor->experience_years }}" placeholder="{{ __('Experience Years') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-8">
                            <a href="{{ route(Config::get('constants.defines.APP_DOCTOR_INDEX')) }}" 
                               class="btn btn-secondary">
                                {{ __('Back to List') }}
                            </a>
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
