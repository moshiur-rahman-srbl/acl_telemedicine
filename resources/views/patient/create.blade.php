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
                    <!-- {{__($cmsInfo['subModuleTitle'])}} -->
                    {{__($cmsInfo['subTitle'])}}
                </div>
            </div>
            <div class="ibox-body">
                <form role="form" action="{{route(Config::get('constants.defines.APP_PATIENT_CREATE'))}}" method="post"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="ibox">
                                <div class="ibox-body">
                                    <div class="form-group {{$errors->has('name') ? 'has-error':''}}">
                                        <label for="name">{{__('Name')}}</label>
                                        <input type="text" class="form-control" name="name" id="name"
                                               placeholder="{{__('Full Name')}}"
                                               value="{{ old('name') }}">
                                        @if($errors->has('name'))
                                            <label class="help-block error">{{__($errors->first('name'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('email') ? 'has-error':''}}">
                                        <label for="email">{{__('Email')}}</label>
                                        <input type="email" class="form-control" name="email" id="email"
                                               placeholder="{{__('Email Address')}}" value="{{ old('email') }}">
                                        @if($errors->has('email'))
                                            <label class="help-block error">{{__($errors->first('email'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('phone') ? 'has-error':''}}">
                                        <label for="phone">{{__('Phone')}}</label>
                                        <input type="text" class="form-control" name="phone" id="phone"
                                               placeholder="{{__('Phone Number')}}" value="{{ old('phone') }}">
                                        @if($errors->has('phone'))
                                            <label class="help-block error">{{__($errors->first('phone'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('address') ? 'has-error':''}}">
                                        <label for="address">{{__('Address')}}</label>
                                        <textarea class="form-control" name="address" id="address" rows="3"
                                                  placeholder="{{__('Enter Address')}}">{{ old('address') }}</textarea>
                                        @if($errors->has('address'))
                                            <label class="help-block error">{{__($errors->first('address'))}}</label>
                                        @endif
                                    </div>

                                    <!-- <div class="form-group {{$errors->has('notes') ? 'has-error':''}}">
                                        <label for="notes">{{__('Notes')}}</label>
                                        <textarea class="form-control" name="notes" id="notes" rows="4"
                                                  placeholder="{{__('Enter any notes')}}">{{ old('notes') }}</textarea>
                                        @if($errors->has('notes'))
                                            <label class="help-block error">{{__($errors->first('notes'))}}</label>
                                        @endif
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary pull-right">{{__('Save')}}</button>
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
    @include('partials.js_blade.moment')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.datepicker')
@endpush
