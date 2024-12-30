@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')
    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}} <a
                        href="{{route(Config::get('constants.defines.APP_EMPLOYEE_INDEX'))}}"
                        class="ml-3 btn btn-sm btn-primary pull-right"><i class="fa fa-list-ul"></i> {{__('List')}}</a>
                </div>
            </div>
            <div class="ibox-body">
                <form role="form" action="{{route(Config::get('constants.defines.APP_EMPLOYEE_EDIT'), $model->id)}}"
                      method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="ibox">
                                <div class="ibox-body">
                                    <div class="form-group {{$errors->has('name') ? 'has-error':''}}">
                                        <label for="name">{{__('Name')}}</label>
                                        <input type="text" class="form-control" name="name" id="name"
                                               placeholder="{{__('name')}}"
                                               value="{{$model->name}}">
                                        @if($errors->has('name'))
                                            <label class="help-block error">{{__($errors->first('name'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('email') ? 'has-error':''}}">
                                        <label for="email">{{__('Email')}}</label>
                                        <input type="email" class="form-control" name="email" id="email"
                                               placeholder="{{__('Email')}}" value="{{ $model->email }}">
                                        @if($errors->has('email'))
                                            <label class="help-block error">{{__($errors->first('email'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('phone') ? 'has-error':''}}">
                                        <label for="phone">{{__('Phone')}}</label><br>

                                        <input name="phone_"
                                               value="{{$model->phone}}" type="text"
                                               id="phone" class="form-control number-only telNoSelector"/>
                                        <input type="hidden" name="phone"
                                               value="{{ $model->phone }}"
                                               id="phonecode" class="telNoValue"/>

                                        @if($errors->has('phone'))
                                            <label class="help-block error">{{__($errors->first('phone'))}}</label>
                                        @endif
                                    </div>


                                    <div class="form-group {{$errors->has('gender') ? 'has-error':''}}">
                                        <label for="gender">{{__('Gender')}}</label>
                                        <select class="form-control" name="gender" id="gender" required>
                                            @foreach($genders as $key => $gender)
                                                <option
                                                    value="{{$key}}" {{$model->gender == $key ? "selected": ""}}>{{$gender}}</option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('gender'))
                                            <label class="help-block error">{{$errors->first('gender')}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('education') ? 'has-error':''}}">
                                        <label for="type">{{__('Education')}}</label>
                                        <input type="text" class="form-control" name="education"
                                               id="education" placeholder="{{__('Education')}}"
                                               value="{{ $model->education }}">
                                        @if($errors->has('education'))
                                            <label class="help-block error">{{__($errors->first('education'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('designation') ? 'has-error':''}}">
                                        <label for="type">{{__('Designation')}}</label>
                                        <input type="text" class="form-control" name="designation"
                                               id="designation" placeholder="{{__('Designation')}}"
                                               value="{{ $model->designation }}">
                                        @if($errors->has('designation'))
                                            <label class="help-block error">{{__($errors->first('designation'))}}</label>
                                        @endif
                                    </div>



                                    <div class="form-group {{$errors->has('address') ? 'has-error':''}}">
                                        <label for="type">{{__('Address')}}</label>
                                        <input type="text" class="form-control" name="address"
                                               id="address" placeholder="{{__('Address')}}"
                                               value="{{ $model->address }}">
                                        @if($errors->has('address'))
                                            <label class="help-block error">{{__($errors->first('address'))}}</label>
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
