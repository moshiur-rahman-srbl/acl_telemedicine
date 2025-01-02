@extends('layouts.adminca')
@section('content')
@include('partials.page_heading')

    @php
        $name_validation = \common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\Backend\BackendMix::class, "isFirstNameAndSurnameRequiredForUser"]);
    @endphp
    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}} <a href="{{route(Config::get('constants.defines.APP_USERS_INDEX'))}}" class="ml-3 btn btn-sm btn-primary pull-right"><i class="fa fa-list-ul"></i> {{__('List')}}</a>
                </div>
            </div>
            <div class="ibox-body">
                    <div class="box-header with-border">
                        {{--<h3 class="box-title">{{__('Create User')}}</h3>--}}
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    <form role="form" action="{{ route(Config::get('constants.defines.APP_USERS_CREATE')) }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="language">{{__('Languages')}}</label>
                                        <select class="form-control select2" name="language" id="language" style="width: 100%;" required>
                                            <option value="en" >{{__("English")}}</option>
                                            <option value="tr" >{{__("Turkish")}}</option>
                                        </select>
                                    </div>
                                    <div class="form-group {{$errors->has('name') ? 'has-error':''}}">
                                        <label for="name">{{__('Name')}}</label>
                                        <input type="text" class="form-control {{ $name_validation ? 'first_name_validation' : '' }}" name="first_name" id="name" placeholder="{{__('Name')}}" value="{{old('name')}}" required>
                                        @if($errors->has('name'))
                                        <label class="help-block error">{{$errors->first('name')}}</label>
                                        @endif
                                    </div>
                                    <div class="form-group {{$errors->has('name') ? 'has-error':''}}">
                                        <label for="name">{{__('Surname')}}</label>
                                        <input type="text" class="form-control {{ $name_validation ? 'last_name_validation' : '' }}" name="last_name" id="name" placeholder="{{__('Surname')}}" value="{{old('last_name')}}">
                                        @if($errors->has('name'))
                                        <label class="help-block error">{{$errors->first('name')}}</label>
                                        @endif
                                    </div>
                                    <div class="form-group {{$errors->has('email') ? 'has-error':''}}">
                                        <label for="email">{{__('Email')}}</label>
                                        <input type="email" class="form-control" name="email" id="email" placeholder="{{__('Email')}}" value="{{old('email')}}" required>
                                        @if($errors->has('email'))
                                        <label class="help-block error">{{__($errors->first('email'))}}</label>
                                        @endif
                                    </div>


                                    <div class="form-group {{$errors->has('phone') ? 'has-error':''}}">
                                        <label for="phone">{{__('Phone')}}</label><br>

                                        <input name="phone_"
                                            value="{{old('phone')}}" type="text"
                                            id="phone" class="form-control number-only telNoSelector"/>
                                        <input type="hidden" name="phone"
                                            value="{{old('phone')}}"
                                            id="phonecode" class="telNoValue"/>

                                        @if($errors->has('phone'))
                                            <label class="help-block error">{{__($errors->first('phone'))}}</label>
                                        @endif
                                    </div>

                                    @if (!\common\integration\BrandConfiguration::isAllowAdminWelcomeMail())
                                        <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                                            <?php
                                            $err_label = config('constants.PASSWORD_SECURITY_TYPE') == \App\Models\Profile::ALPHANUMERIC_PASSWORD ? __('The password must be 8 characters long, must contain a mix of upper/lowercase letters, numbers, and special characters') : __('Password must be 6 digit number only');
                                            ?>
                                            <label for="password">{{ __('Password') }}
                                                <br><small class="text-primary">[{{ $err_label }}]</small>
                                                <input type="password" class="form-control mt-2" name="password" id="password" placeholder="{{ __('Password') }}" required>
                                                @if ($errors->has('password'))
                                                <label class="help-block error">{{ $errors->first('password') }}</label>
                                                @endif
                                        </div>
                                        <div class="form-group {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                                            <label for="password_confirmation">{{ __('Confirm Password') }}</label>
                                            <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="{{ __('Confirm Password') }}" required>
                                            @if ($errors->has('password_confirmation'))
                                                <label class="help-block error">{{ $errors->first('password_confirmation') }}</label>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="form-group {{$errors->has('usergroup_id') ? 'has-error':''}}">
                                        <label for="usergroup_id">{{__('User Group')}}</label>
                                        <select data-style-base="form-control" data-actions-box="true" class="form-control selectpicker" data-title="Nothing Selected" multiple="" name="usergroup_id[]" id="usergroup_id" required>
                                            <option value="" >{{__('Please select')}}</option>
                                            @foreach($usergroups as $usergroup)
                                                <option value="{{$usergroup->id}}" >{{$usergroup->group_name}}</option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('usergroup_id'))
                                        <label class="help-block error">{{$errors->first('usergroup_id')}}</label>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label class="btn btn-primary file-input mr-2">
                                            <span class="btn-icon"><i class="la la-cloud-upload"></i>{{__('Profile Picture')}}</span>
                                            <input type="file" name="img_path">
                                        </label>
                                        {{--<input type="file" id="exampleInputFile" name="img_path">--}}
                                    </div>
                                </div>
                                <!-- /.box-body -->

                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary">{{__('Submit')}}</button>
                                    <a href="{{route(Config::get('constants.defines.APP_USERS_INDEX'))}}" class="btn btn-primary">{{__('Cancel')}}</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.box -->
            </div>
        </div>
@endsection
@push('css')
    @include('partials.css_blade.bootstrap-select')
    @include('partials.css_blade.select2')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.datepicker')
    @include('partials.css_blade.datetimepicker')
    @include('partials.css_blade.clockpicker')
    @include('partials.css_blade.ionRangeSlider')
    @include('partials.css_blade.bootstrap-tagsinput')
    @include('partials.css_blade.bootstrap-touchspin')
    @include('partials.css_blade.multi-select')
    @include('partials.css_blade.intlTelInput_new')

@endpush
@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.bootstrap-select')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.datepicker')
    @include('partials.js_blade.datetimepicker')
    @include('partials.js_blade.clockpicker')
    @include('partials.js_blade.knob')
    @include('partials.js_blade.ionRangeSlider')
    @include('partials.js_blade.bootstrap-tagsinput')
    @include('partials.js_blade.bootstrapMaxLength')
    @include('partials.js_blade.bootstrap-touchspin')
    @include('partials.js_blade.multi-select')
    @include('partials.js_blade.intlTelInput_new')
    <script>
        const first_name = document.querySelector('.first_name_validation');
        first_name.addEventListener('keydown', function(event){
            if((/\d/g).test(event.key)) event.preventDefault();
        })

        const last_name = document.querySelector('.last_name_validation');
        last_name.addEventListener('keydown', function(event){
            if((/\d/g).test(event.key)) event.preventDefault();
        })
    </script>
    <script src="{{asset('adminca')}}/assets/js/scripts/form-plugins.js"></script>
@endpush
