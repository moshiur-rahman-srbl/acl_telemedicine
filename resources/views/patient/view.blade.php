@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')
    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    <h5>Patient View</h5>
                    <!-- {{__($cmsInfo['subTitle'])}} <a
                        href="{{route(Config::get('constants.defines.APP_PATIENT_INDEX'))}}"
                        class="ml-3 btn btn-sm btn-primary pull-right"><i class="fa fa-list-ul"></i> {{__('List')}}</a> -->
                </div> 
            </div>
            <div class="ibox-body">
                <form role="form">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="ibox">
                                <div class="ibox-body">
                                    <div class="form-group {{$errors->has('name') ? 'has-error':''}}">
                                        <label for="name">{{__('Name')}}</label>
                                        <input type="text" class="form-control" name="name" id="name" disabled
                                               placeholder="{{__('Full Name')}}" value="{{ $model->name }}">
                                        @if($errors->has('name'))
                                            <label class="help-block error">{{__($errors->first('name'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('email') ? 'has-error':''}}">
                                        <label for="email">{{__('Email')}}</label>
                                        <input type="email" class="form-control" name="email" id="email" disabled
                                               placeholder="{{__('Email Address')}}" value="{{ $model->email }}">
                                        @if($errors->has('email'))
                                            <label class="help-block error">{{__($errors->first('email'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('phone') ? 'has-error':''}}">
                                        <label for="phone">{{__('Phone')}}</label>
                                        <input type="text" class="form-control" name="phone" id="phone" disabled
                                               placeholder="{{__('Phone Number')}}" value="{{ $model->phone }}">
                                        @if($errors->has('phone'))
                                            <label class="help-block error">{{__($errors->first('phone'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('address') ? 'has-error':''}}">
                                        <label for="address">{{__('Address')}}</label>
                                        <textarea class="form-control" name="address" id="address" disabled rows="3"
                                                  placeholder="{{__('Enter Address')}}">{{ $model->address }}</textarea>
                                        @if($errors->has('address'))
                                            <label class="help-block error">{{__($errors->first('address'))}}</label>
                                        @endif
                                    </div>

                                    <div class="form-group {{$errors->has('notes') ? 'has-error':''}}">
                                        <label for="notes">{{__('Notes')}}</label>
                                        <textarea class="form-control" name="notes" id="notes" disabled rows="4"
                                                  placeholder="{{__('Enter any notes')}}">{{ $model->notes }}</textarea>
                                        @if($errors->has('notes'))
                                            <label class="help-block error">{{__($errors->first('notes'))}}</label>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Actions section can be added here if needed -->
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
