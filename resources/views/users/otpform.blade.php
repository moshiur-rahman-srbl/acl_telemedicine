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
                    {{__($cmsInfo['subTitle'])}} <a href="{{route(Config::get('constants.defines.APP_USERS_INDEX'))}}" class="ml-3 btn btn-sm btn-primary pull-right {{(isset($logger) && !empty($logger)) ? 'd-none' : ''}}"><i class="fa fa-list-ul"></i> {{__('List')}}</a>
                </div>
            </div>
            <div class="ibox-body">
                <div class="box-header with-border">
                    {{--<h3 class="box-title">{{__('Create User')}}</h3>--}}
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                <form role="form" action="{{ $dynamic_route }}" method="post" enctype="multipart/form-data" id="edit_form">
                    @csrf
                    <input type="hidden" name="action" value="{{ $action ?? \App\User::USER_UPDATE_ACTION }}">
                    <div class="row">

                        <div class="col-md-6">
                            <div class="ibox">
                                <div class="ibox-body">
                                    <div class="form-group {{ $errors->has('otp') ? 'has-error':'' }}">
                                        <label for="otp">{{ __('OTP') }} <span class="red-text">*</span></label>
                                        <input type="text"
                                               class="form-control"
                                               name="otp"
                                               id="otp"
                                               value="{{old('otp')}}"
                                               placeholder="{{__('OTP')}}"
                                               required>
                                        @if($errors->has('otp'))
                                            <label class="help-block error">{{$errors->first('otp')}}</label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">{{__('Verify')}}</button>
                                @if(isset($action) && $action == \App\User::USER_PASSWORD_UPDATE_ACTION )
                                    <a href="{{ route(Config::get('constants.defines.APP_USERS_CHANGEPASSWORD'),['action'=>$action,'resendotp'=>'resend']) }}" class="btn btn-primary">{{__('Resend')}}</a>
                                @else
                                    <a href="{{ route(Config::get('constants.defines.APP_USERS_EDIT'),['id'=>$user_id,'action'=>$action, 'resendotp'=>'resend']) }}" class="btn btn-primary">{{__('Resend')}}</a>
                                @endif
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
    <script src="{{asset('adminca')}}/assets/js/scripts/form-plugins.js"></script>
    <script>
        $('#edit_form').on('submit', function() {
            $('.select_check').prop('disabled', false);
        });
    </script>
@endpush
