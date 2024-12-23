@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')

    <div class="page-content fade-in-up">
        @include('partials.flash')

        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}} <a href="{{route(Config::get('constants.defines.APP_USERS_INDEX'))}}"
                                                    class="ml-3 btn btn-sm btn-primary pull-right {{(isset($logger) && !empty($logger)) ? 'd-none' : ''}}"><i
                            class="fa fa-list-ul"></i> {{__('List')}}</a>
                </div>
            </div>
            <div class="ibox-body">
                <div class="box-header with-border">
                    {{--<h3 class="box-title">{{__('Create User')}}</h3>--}}
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                @php
                    $name_validation = \common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\Backend\BackendMix::class, "isFirstNameAndSurnameRequiredForUser"]);
                @endphp
                <form role="form" action="{{$dynamic_route}}" method="post" enctype="multipart/form-data"
                      id="edit_form">
                    @csrf
                    @if((isset($logger) && empty($logger)))
                        @method('PUT')
                    @endif
                    <div class="row">

                        <div class="col-md-6">
                            <div class="ibox">
                                <div class="ibox-body">
                                    <div class="form-group">
                                        <label for="language">{{__('Languages')}}</label>
                                        <select class="form-control select2 select_check" name="language" id="language"
                                                style="width: 100%;"
                                                required {{$disabled}} {{(isset($logger) && !empty($logger)) ? 'disabled' : ''}}>
                                            <option
                                                value="en" {{(isset($user) && $user->language == "en") ? "selected" : ""}}>{{__("English")}}</option>
                                            <option
                                                value="tr" {{(isset($user) && $user->language == "tr") ? "selected" : ""}}>{{__("Turkish")}}</option>
                                        </select>
                                    </div>
                                    @if (! empty($user->first_name))
                                        <div class="form-group">
                                            <label for="name">{{__('Name')}}</label>'
                                            <input type="text"
                                                   class="form-control {{ $name_validation ? 'first_name_validation' : '' }}"
                                                   name="first_name" id="name" placeholder="{{ __('Your Name') }}"
                                                   value="{{isset($user->name) ? \common\integration\GlobalFunction::nameCaseConversion($user->first_name) : ''}}"
                                                   required {{$disabled}}>
                                        </div>
                                        <div class="form-group">
                                            <label for="name">{{__('Surname')}}</label>
                                            <input type="text"
                                                   class="form-control {{ $name_validation ? 'last_name_validation' : '' }}"
                                                   name="last_name" id="name" placeholder="{{ __('Your Surname') }}"
                                                   value="{{isset($user->name) ? \common\integration\GlobalFunction::nameCaseConversion($user->last_name) : ''}}"
                                                   required {{$disabled}}>
                                        </div>
                                    @else
                                        @php
                                            $formatted_name = \common\integration\GlobalUser::getNameSurnameByFullName($user->name);
                                        @endphp
                                        <div class="form-group">
                                            <label for="name">{{__('Name')}}</label>
                                            <input type="text"
                                                   class="form-control {{ $name_validation ? 'first_name_validation' : '' }}"
                                                   name="first_name" id="name" placeholder="{{ __('Your Name') }}"
                                                   value="{{isset($user->name) ? \common\integration\GlobalFunction::nameCaseConversion($formatted_name['name']) : ''}}"
                                                   required {{$disabled}}>
                                        </div>
                                        <div class="form-group">
                                            <label for="name">{{__('Surname')}}</label>
                                            <input type="text"
                                                   class="form-control {{ $name_validation ? 'last_name_validation' : '' }}"
                                                   name="last_name" id="name" placeholder="{{ __('Your Surname') }}"
                                                   value="{{isset($user->name) ? \common\integration\GlobalFunction::nameCaseConversion($formatted_name['surname']) : ''}}"
                                                   required {{$disabled}}>
                                        </div>
                                    @endif
                                    <div class="form-group">
                                        <label for="email">{{__('Email')}}</label>
                                        <input type="text" class="form-control" name="email" id="email"
                                               placeholder="{{ __('Enter email') }}"
                                               value="{{isset($user->email) ? $user->email : ''}}"
                                               required {{$disabled}}>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">{{__('Phone')}}</label>
                                        <input name="phone_" {{$disabled}}
                                        value="{{isset($user->phone) ? $user->phone : ''}}" type="text"
                                               id="phone" class="form-control number-only telNoSelector"/>
                                        <input type="hidden" name="phone"
                                               value="{{isset($user->phone) ? $user->phone : ''}}"
                                               id="phonecode" class="telNoValue"/>
                                    </div>


                                    @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'isAllowAdminApi']))
                                        <div class="row">
                                            @if(!$is_show_client_id_client_secret)
                                                <div class="form-group">
                                                    <label>{{ __('Show Client Id, Client Secret') }}</label>
                                                    <a href="#" id="show_client_info_btn" class="fa-2x ml-4"> <i
                                                            class="fa fa-unlock-alt" aria-hidden="true"></i> </a>
                                                </div>
                                            @else
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>{{__('Client Id')}}</label>
                                                        <div class="input-group" id="">
                                                            <input class="form-control" id="client_id" type="text"
                                                                   placeholder="{{ __('Client Id') }}"
                                                                   value="{{isset($userApiCredentialData) ? $userApiCredentialData?->client_id : ''}}">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-{{ isset($secretField) ? '12': '8' }}">
                                                    <div class="form-group">
                                                        <label>{{__('Client Secret')}}</label>
                                                        <div class="input-group" id="show_hide_secret">
                                                            <input class="form-control" name="client_secret"
                                                                   id="client_secret" type="password"
                                                                   placeholder="{{ __('Client Secret') }}"
                                                                   value="{{isset($userApiCredentialData) ? $userApiCredentialData?->client_secret : ''}}"
                                                                   readonly>
                                                            <div class="input-group-addon">
                                                                <a href=""><i class="fa fa-eye-slash"
                                                                              aria-hidden="true"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if(!isset($secretField))
                                                    <div class="col-md-4">
                                                        <button type="button"
                                                                class="btn btn-info client_secret_gen_button"
                                                                onclick="generateClientSecret()">
                                                            {{ __('Generate') }}
                                                        </button>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endif


                                    <div class="form-group">
                                        <label class="btn btn-primary file-input mr-2">
                                            <span class="btn-icon"><i class="la la-cloud-upload"></i>{{__('Profile Picture')}}</span>
                                            <input type="file" name="img_path" {{$disabled}}>
                                        </label>
                                        @if(!empty($user->img_path))
                                            <img src="{{secure_file_link($user->img_path, 'public')}}" alt=""
                                                 width="70">
                                        @endif
                                        {{--<input type="file" id="exampleInputFile" name="img_path">--}}
                                    </div>


                                </div>
                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="ibox">
                                <div class="ibox-body">

                                    @if (!\common\integration\BrandConfiguration::isAllowAdminWelcomeMail())
                                        <div class="form-group">
                                            <label for="old_password">{{__('Old Password')}}</label>
                                            <input type="password" class="form-control" name="user_password"
                                                   id="user_password" placeholder="{{__('Old Password')}}"
                                                   autocomplete="new-password" {{$disabled}}>
                                            {{--<!--                                        @if($errors->has('user_password'))--}}
                                            <label class="help-block error">{{$errors->first('user_password')}}</label>
                                            {{--@endif-->--}}
                                        </div>
                                        <div class="form-group">
                                            <label for="password">{{__('New Password')}}</label>
                                            <input type="password" class="form-control" name="password" id="password"
                                                   placeholder="{{__('New Password')}}"
                                                   autocomplete="new-password" {{$disabled}}>
                                            {{--@if($errors->has('password'))--}}
                                            {{--<label class="help-block error">{{$errors->first('password')}}</label>--}}
                                            {{--@endif--}}
                                        </div>
                                        <div class="form-group">
                                            <label for="password_confirmation">{{__('Confirm Password')}}</label>
                                            <input type="password" class="form-control" name="password_confirmation"
                                                   id="password_confirmation" placeholder="{{__('Confirm Password')}}"
                                                   autocomplete="new-password" {{$disabled}}>
                                            {{--@if($errors->has('password_confirmation'))--}}
                                            {{--<label class="help-block error">{{$errors->first('password_confirmation')}}</label>--}}
                                            {{--@endif--}}
                                        </div>
                                    @endif

                                    <div class="form-group">
                                        <label for="usergroup_id">{{__('User Group')}}</label>
                                        <select data-style-base="form-control" data-actions-box="true"
                                                class="form-control selectpicker" data-title="Nothing Selected"
                                                multiple="" name="usergroup_id[]" id="usergroup_id"
                                                {{$disabled}} {{(isset($logger) && !empty($logger)) ? 'disabled' : ''}} required>
                                            <option value="">{{__('Please select')}}</option>
                                            @foreach($usergroups as $usergroup)
                                                <option
                                                    value="{{$usergroup->id}}" {{(in_array($usergroup->id, $assaigned_usergroups)) ? 'selected' : ''}}>{{$usergroup->group_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="language">{{__('Status')}}</label>
                                        <select class="form-control select2 select_check" name="is_admin_verified"
                                                id="is_admin_verified" style="width: 100%;"
                                                required {{$disabled}} {{(isset($logger) && !empty($logger)) ? 'disabled' : ''}}>
                                            <option
                                                value="{{\App\Models\Profile::ADMIN_VERIFIED_PENDING}}" {{(isset($user) && $user->is_admin_verified == \App\Models\Profile::ADMIN_VERIFIED_PENDING) ? "selected" : ""}}>{{__("Pending")}}</option>
                                            <option
                                                value="{{\App\Models\Profile::ADMIN_VERIFIED_APPROVED}}" {{(isset($user) && $user->is_admin_verified == \App\Models\Profile::ADMIN_VERIFIED_APPROVED) ? "selected" : ""}}>{{__("Active")}}</option>
                                            <option
                                                value="{{\App\Models\Profile::ADMIN_VERIFIED_NOT_APPROVED}}" {{(isset($user) && $user->is_admin_verified == \App\Models\Profile::ADMIN_VERIFIED_NOT_APPROVED) ? "selected" : ""}}>{{__("Inactive")}}</option>
                                        </select>
                                    </div>

                                    @php
                                        $authority = true;
                                        if(\common\integration\BrandConfiguration::isUserAuthoritiesHideInUserManagement()){
                                            $authority = false;
                                        }
                                    @endphp
                                    <div class="authority">

                                        @if(\common\integration\BrandConfiguration::otpChannelEnableForBothSmsAndEmail() && !\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\Frontend\FrontendAdmin::class, 'hideOtpChannel']) )
                                            <div class="form-group">
                                                <label for="language">{{__('OTP Channel')}}</label>
                                                <select data-style-base="form-control" data-actions-box="true"
                                                        class="form-control selectpicker" name="otp_channel"
                                                        id="otp_channel" required>
                                                    {{--                                            <option value="" >{{__('Please select')}}</option>--}}
                                                    @foreach(\App\User::getOtpChannelList() as $channel_key => $channel_value)
                                                        <option
                                                            value="{{$channel_key}}" {{(!empty($user) && isset($user->otp_channel) && $user->otp_channel == $channel_key) ? 'selected' : ''}} >{{$channel_value}}</option>
                                                    @endforeach
                                                </select>

                                                {{--                                        <label class="checkbox checkbox-ebony">{{__("SMS OTP Required to Login")}}--}}
                                                {{--                                            <input {{$disabled}} type="checkbox" class="" {{(!empty($user) && isset($user->otp_channel) && $user->otp_channel == App\User::OTP_CHANNEL_ALL) ? 'checked' : ''}} name="otp_channel">--}}
                                                {{--                                            <span class="input-span"></span>--}}
                                                {{--                                        </label>--}}
                                            </div>
                                        @endif
                                        <br>
                                        @if($authority)
                                            <div class="form-group">
                                                <label class="checkbox checkbox-ebony">{{__("Allow Direct Export")}}
                                                    <input type="checkbox" class=""
                                                           {{(!empty($userUGS) && isset($userUGS->is_allow_direct_export) && $userUGS->is_allow_direct_export == 1) ? 'checked' : ''}} name="is_allow_direct_export">
                                                    <span class="input-span"></span>
                                                </label>
                                            </div>

                                            <div class="form-group"
                                                 style="display: {{$user_panel_sections_show == false ? 'none' : 'show'}}">
                                                <label
                                                    class="checkbox checkbox-ebony">{{__("Allow B2B Approve without Document")}}
                                                    <input {{$disabled}} type="checkbox" class=""
                                                           {{(!empty($userUGS) && isset($userUGS->is_allow_b2b_without_doc) && $userUGS->is_allow_b2b_without_doc == 1) ? 'checked' : ''}} name="is_allow_b2b_without_doc">
                                                    <span class="input-span"></span>
                                                </label>
                                            </div>

                                            <div class="form-group">
                                                <label class="checkbox checkbox-ebony">{{__("Allow Create Deposit")}}
                                                    <input {{$disabled}} type="checkbox" class=""
                                                           {{(!empty($userUGS) && isset($userUGS->is_allow_create_deposit) && $userUGS->is_allow_create_deposit == 1) ? 'checked' : ''}} name="is_allow_create_deposit">
                                                    <span class="input-span"></span>
                                                </label>
                                            </div>

                                        @endif

                                        <div class="form-group"
                                             style="display: {{$user_panel_sections_show == false ? 'none' : 'show'}}">
                                            <label class="checkbox checkbox-ebony">{{__("Allow Create B2B")}}
                                                <input {{$disabled}} type="checkbox" class=""
                                                       {{(!empty($userUGS) && isset($userUGS->is_allow_create_b2b) && $userUGS->is_allow_create_b2b == 1) ? 'checked' : ''}} name="is_allow_create_b2b">
                                                <span class="input-span"></span>
                                            </label>
                                        </div>

                                        <div class="form-group"
                                             style="display: {{$user_panel_sections_show == false ? 'none' : 'show'}}">
                                            <label class="checkbox checkbox-ebony">{{__("Allow Create B2C")}}
                                                <input {{$disabled}} type="checkbox" class=""
                                                       {{(!empty($userUGS) && isset($userUGS->is_allow_create_b2c) && $userUGS->is_allow_create_b2c == 1) ? 'checked' : ''}} name="is_allow_create_b2c">
                                                <span class="input-span"></span>
                                            </label>
                                        </div>


                                        @if($authority)

                                            <div class="form-group">
                                                <label
                                                    class="checkbox checkbox-ebony">{{__("Show Merchant Auth Email")}}
                                                    <input {{$disabled}} type="checkbox" class=""
                                                           {{(!empty($userUGS) && isset($userUGS->is_show_auth_email) && $userUGS->is_show_auth_email == 1) ? 'checked' : ''}} name="is_show_auth_email">
                                                    <span class="input-span"></span>
                                                </label>
                                            </div>

                                            <div class="form-group">
                                                <label class="checkbox checkbox-ebony">{{__("Show Merchant Website")}}
                                                    <input {{$disabled}} type="checkbox" class=""
                                                           {{(!empty($userUGS) && isset($userUGS->is_show_website) && $userUGS->is_show_website == 1) ? 'checked' : ''}} name="is_show_website">
                                                    <span class="input-span"></span>
                                                </label>
                                            </div>
                                            @if(\common\integration\BrandConfiguration::call([common\integration\Brand\Configuration\Backend\BackendAdmin::class, 'adminRemoteLogin']))
                                                <div class="form-group">
                                                    <label
                                                        class="checkbox checkbox-ebony">{{__("OTP Required to Login")}}
                                                        <input type="checkbox" class=""
                                                               {{(!empty($user) && isset($user->is_otp_required) && $user->is_otp_required == 1) ? 'checked' : ''}} name="is_otp_required"
                                                               value="{{ (!empty($user) && isset($user->is_otp_required)) ? $user->is_otp_required : 0 }}">
                                                        <span class="input-span"></span>
                                                    </label>
                                                </div>
                                            @endif

                                            @if(!\common\integration\BrandConfiguration::otpChannelEnableForBothSmsAndEmail())
                                                <div class="form-group">
                                                    <label
                                                        class="checkbox checkbox-ebony">{{__("SMS OTP Required to Login")}}
                                                        <input {{$disabled}} type="checkbox" class=""
                                                               {{(!empty($user) && isset($user->otp_channel) && $user->otp_channel == App\User::OTP_CHANNEL_SMS) ? 'checked' : ''}} name="otp_channel"
                                                               value="{{ App\User::OTP_CHANNEL_SMS }}">
                                                        <span class="input-span"></span>
                                                    </label>
                                                </div>
                                            @endif
                                            @if(\common\integration\BrandConfiguration::restrictMerchantByAccountManager())
                                                <div class="form-group">
                                                    <label
                                                        class="checkbox checkbox-ebony">{{__("Restrict Merchant By Account Manager")}}
                                                        <input {{$disabled}} type="checkbox" class=""
                                                               {{(!empty($user) && isset($user->is_merchant_restricted_by_account_manager) && $user->is_merchant_restricted_by_account_manager == 1) ? 'checked' : ''}} name="is_merchant_restricted_by_account_manager">
                                                        <span class="input-span"></span>
                                                    </label>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="box-footer">
                                @if(empty($disabled))
                                    <button type="submit" class="btn btn-primary">{{__('Update')}}</button>
                                @endif
                                <a href="{{route(Config::get('constants.defines.APP_USERS_INDEX'))}}"
                                   class="btn btn-primary">{{__('Cancel')}}</a>
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
    @include('partials.css_blade.custom')

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

    <script src="{{asset('adminca')}}/assets/js/scripts/form-plugins.js"></script>
    <script>
        $('#edit_form').on('submit', function () {
            $('.select_check').prop('disabled', false);
        });

        function generateClientSecret() {
            let loader = `<span class="spinner-border spinner-border-sm sec-spinner" role="status" aria-hidden="true"></span>{{ __('Generateing') }}`;
            let removeLoader = `{{ __('Generate') }}`;
            var get_url = "{{route(config('constants.defines.APP_USERS_EDIT'), ['id' => $user->id, 'action' => 'generateClientSecret'])}}";
            $('.client_secret_gen_button').html(loader);
            $('.client_secret_gen_button').prop("disabled", true);

            $.ajax({
                type: 'get',
                url: get_url,
                success: function (response) {
                    $('.client_secret_gen_button').html(removeLoader);
                    $('.client_secret_gen_button').prop("disabled", false);
                    $('#client_secret').val(response);
                },
                error: function (xhr, status, error) {
                    $('.client_secret_gen_button').html(removeLoader);
                    $('.client_secret_gen_button').prop("disabled", false);
                    console.error(xhr.responseText);
                }
            });
        }

        $(document).ready(function () {
            $("#show_hide_secret a").on('click', function (event) {
                event.preventDefault();
                if ($('#show_hide_secret input').attr("type") == "text") {
                    $('#show_hide_secret input').attr('type', 'password');
                    $('#show_hide_secret i').addClass("fa-eye-slash");
                    $('#show_hide_secret i').removeClass("fa-eye");
                } else if ($('#show_hide_secret input').attr("type") == "password") {
                    $('#show_hide_secret input').attr('type', 'text');
                    $('#show_hide_secret i').removeClass("fa-eye-slash");
                    $('#show_hide_secret i').addClass("fa-eye");
                }
            });
            @if(!$is_show_client_id_client_secret)
            $('#show_client_info_btn').click(function (e) {
                e.preventDefault();
                $('#show_client_info_form').submit();
            });
            @endif

        });
        const first_name = document.querySelector('.first_name_validation');
        first_name.addEventListener('keydown', function (event) {
            if ((/\d/g).test(event.key)) event.preventDefault();
        })

        const last_name = document.querySelector('.last_name_validation');
        last_name.addEventListener('keydown', function (event) {
            if ((/\d/g).test(event.key)) event.preventDefault();
        })
    </script>
@endpush
