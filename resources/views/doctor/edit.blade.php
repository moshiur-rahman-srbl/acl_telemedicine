@extends('layouts.adminca')

@section('content')
    @include('partials.page_heading')
    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">{{ __('Edit Doctor') }}</div>
            </div>
            <div class="ibox-body">
                <form role="form" method="POST" action="{{ route(Config::get('constants.defines.APP_DOCTOR_EDIT'), ['id' => $doctor->id]) }}">
                    @csrf
                    @method('POST') <!-- Use POST method for the form submission -->

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group {{$errors->has('name') ? 'has-error':''}}">
                                <label for="name">{{ __('Name') }}</label>
                                <input type="text" class="form-control" name="name" id="name" placeholder="{{ __('Name') }}" value="{{ old('name', $doctor->name) }}">
                                @if ($errors->has('name'))
                                    <span class="help-block error">{{ $errors->first('name') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group {{$errors->has('email') ? 'has-error':''}}">
                                <label for="email">{{ __('Email') }}</label>
                                <input type="email" class="form-control" name="email" id="email" placeholder="{{ __('Email') }}" value="{{ old('email', $doctor->email) }}">
                                @if ($errors->has('email'))
                                    <span class="help-block error">{{ $errors->first('email') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group {{$errors->has('license_number') ? 'has-error':''}}">
                                <label for="license_number">{{ __('License Number') }}</label>
                                <input type="text" class="form-control" name="license_number" id="license_number" placeholder="{{ __('License Number') }}" value="{{ old('license_number', $doctor->license_number) }}">
                                @if ($errors->has('license_number'))
                                    <span class="help-block error">{{ $errors->first('license_number') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group {{$errors->has('speciality') ? 'has-error':''}}">
                                <label for="speciality">{{ __('Speciality') }}</label>
                                <input type="text" class="form-control" name="speciality" id="speciality" placeholder="{{ __('Speciality') }}" value="{{ old('speciality', $doctor->speciality) }}">
                                @if ($errors->has('speciality'))
                                    <span class="help-block error">{{ $errors->first('speciality') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group {{$errors->has('experience_years') ? 'has-error':''}}">
                                <label for="experience_years">{{ __('Experience Years') }}</label>
                                <input type="number" class="form-control" name="experience_years" id="experience_years" placeholder="{{ __('Experience Years') }}" value="{{ old('experience_years', $doctor->experience_years) }}">
                                @if ($errors->has('experience_years'))
                                    <span class="help-block error">{{ $errors->first('experience_years') }}</span>
                                @endif
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
    @include('partials.css_blade.datepicker')
    @include('partials.css_blade.datetimepicker')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.clockpicker')
    @include('partials.css_blade.ionRangeSlider')
    @include('partials.css_blade.bootstrap-tagsinput')
    @include('partials.css_blade.bootstrap-touchspin')
    @include('partials.css_blade.multi-select')
    @include('partials.css_blade.intlTelInput_new')
@endpush

@push('scripts')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.datepicker')
    @include('partials.js_blade.datetimepicker')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.clockpicker')
    @include('partials.js_blade.ionRangeSlider')
    @include('partials.js_blade.bootstrap-tagsinput')
    @include('partials.js_blade.bootstrapMaxLength')
    @include('partials.js_blade.bootstrap-touchspin')
    @include('partials.js_blade.multi-select')
    @include('partials.js_blade.intlTelInput_new')
@endpush
