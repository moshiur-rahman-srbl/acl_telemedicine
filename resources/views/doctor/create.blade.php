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
            <div class="ibox-title d-flex justify-content-between">
                <h3>{{ __($cmsInfo['subTitle']) }}</h3>
                <a href="{{ route(Config::get('constants.defines.APP_DOCTOR_INDEX')) }}" class="ml-3 btn btn-sm btn-secondary">
                    <i class="fa fa-arrow-left"></i>&nbsp;{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="ibox-body">
            <form role="form" action="{{ route(Config::get('constants.defines.APP_DOCTOR_CREATE')) }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group {{$errors->has('name') ? 'has-error':''}}">
                            <label for="name">{{ __('Name') }}</label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="{{ __('Enter Name') }}" value="{{ old('name') }}">
                            @if ($errors->has('name'))
                                <span class="help-block error">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{$errors->has('email') ? 'has-error':''}}">
                            <label for="email">{{ __('Email') }}</label>
                            <input type="email" class="form-control" name="email" id="email" placeholder="{{ __('Enter Email') }}" value="{{ old('email') }}">
                            @if ($errors->has('email'))
                                <span class="help-block error">{{ $errors->first('email') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{$errors->has('license_number') ? 'has-error':''}}">
                            <label for="license_number">{{ __('License Number') }}</label>
                            <input type="text" class="form-control" name="license_number" id="license_number" placeholder="{{ __('Enter license_number') }}" value="{{ old('license_number') }}">
                            @if ($errors->has('license_number'))
                                <span class="help-block error">{{ $errors->first('license_number') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{$errors->has('speciality') ? 'has-error':''}}">
                            <label for="speciality">{{ __('Speciality') }}</label>
                            <input type="text" class="form-control" name="speciality" id="speciality" placeholder="{{ __('Enter Speciality') }}" value="{{ old('speciality') }}">
                            @if ($errors->has('speciality'))
                                <span class="help-block error">{{ $errors->first('speciality') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{$errors->has('experience_years') ? 'has-error':''}}">
                            <label for="experience_years">{{ __('Experience Years') }}</label>
                            <input type="number" class="form-control" name="experience_years" id="experience_years" placeholder="{{ __('Enter Experience Years') }}" value="{{ old('experience_years') }}">
                            @if ($errors->has('experience_years'))
                                <span class="help-block error">{{ $errors->first('experience_years') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
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

<!-- @push('css')

    @include('partials.css_blade.select2')
    @include('partials.css_blade.datepicker')
    @include('partials.css_blade.datetimepicker')
    @include('partials.css_blade.clockpicker')
@endpush
@push('scripts')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.datepicker')
    @include('partials.js_blade.datetimepicker')
    @include('partials.js_blade.clockpicker')
@endpush -->