@extends('layouts.ac_login')

@if(\common\integration\BrandConfiguration::allowHiddenCaptcha())
    <script src='https://www.google.com/recaptcha/api.js?render={{ config('constants.GOOGLE_RECAPTCHA_PUBLIC_KEY') }}'></script>
@endif
@if(\common\integration\BrandConfiguration::isPasswordSaveRestrictedToBrowser())
    <style>
        @font-face {
            font-family: text-security-disc;
            src: url("{{Storage::url('fonts/text-security-disc.woff')}}");
        }

        #password {
            font-family: text-security-disc, serif;
            -webkit-text-security: disc;
        }
    </style>
@endif
<style>
    .red-border {
        border-bottom-color: #f00 !important;
    }
    .sa {
     font-size: 13px;
     color: #219351;
     background-color: #97e6b8;
    }
    .da {
        font-size: 13px;
        color: #a6372b;
        background-color: #f3a69e;
    }
    .grecaptcha-badge {
        visibility: hidden;
    }

    .fa-redo:before {
        content: "\f01e";
    }

    .captcha-input-group {
        position: relative;
    }

    .refresh-captcha {
        position: absolute;
        right: 10px;
        top: 50%;
        border: none !important;
        transform: translateY(-48%);
    }

    .captcha-input-field {
        height: 49px;
        border: 1px solid #dee2e6 !important;
        border-radius: 0 5px 5px 0;
        font-size: 24px;
        font-weight: 600;
    }

    .captcha-image {
        border-radius: 5px 0 0 5px !important;
    }


</style>

@section('content')

    <form autocomplete="off" class="ibox-body" id="login-from" enctype="multipart/form-data" action="{{ route('loginpost') }}" method="POST">
        @csrf
        <h4 class="font-strong text-center mb-5 text-uppercase">{{__('Log In')}}</h4>
        @include('partials.flash')

        @if (Session::has('rp-msg'))
            <span class="d-block mb-4 pt-3 pb-3 pr-4 pl-4 {{ Session::get('rp-typ') }}">
                    {{ Session::get('rp-msg') }}
                </span>
        @endif

        <span class="text-danger">
                        <strong>{{$logoutOtherDeviceWarning ?? ''}}</strong>
                </span>

        <div class="form-group mb-4">
            <input
                class="form-control form-control-line  {{ (($errors->has('email')) && ($errors->first('email') == 'required')) ? 'red-border' : '' }}"
                autocomplete="off" readonly type="email" name="email" id="email"
                onfocus="this.removeAttribute('readonly');"
                placeholder="{{__('Email')}}">
            @if (($errors->has('email')) && ($errors->first('email') != 'required'))
                <span class="text-danger">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
            @endif
            @if (Session::has('not-admin-error'))
                <span class="text-danger">
                        <strong>{{ Session::get('not-admin-error') }}</strong>
                    </span>
                @php(Session::forget('not-admin-error'))
            @endif
            @if(\common\integration\BrandConfiguration::allowHiddenCaptcha())
                <input type="hidden" id="g-recaptcha-response" name="google_captcha">
            @endif
            <input type="hidden" name="apply_captcha" value="{{ !$apply_captcha ? 'no' : 'yes' }}">
        </div>
        <div class="form-group mb-4" id="form_group_password">
            <input
                class="form-control form-control-line  {{ $errors->has('password') ? 'red-border' : '' }}"
                type="{{\common\integration\BrandConfiguration::isPasswordSaveRestrictedToBrowser() ? 'text' :'password'}}"
                name="password"
                readonly
                id="password"
                onfocus="this.removeAttribute('readonly');"
                autocomplete="new-password"
                placeholder="{{__('Password')}}"
            >
            @if (($errors->has('password')) && ($errors->first('password') != 'required'))
                <span class="text-danger">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
            @endif
        </div>

        @if(\common\integration\BrandConfiguration::isAllowLoginCaptcha() && $apply_captcha && !session()->has('REMOTE_LOGIN'))
            <div class="form-group mb-4 text-center">
                <label for="captcha" class="text-muted mb-4">{{ __('Please enter the text shown below') }}</label>
                <div id="captcha-wrapper">
                    <div class="row">
                        <div class="col pr-0">
                            <img src="{{route('captcha')}}" alt="CAPTCHA" style="margin-right: 0px;"
                                 class="captcha-image img-thumbnail" id="captcha_img">
                        </div>
                        <div class="col pl-0">
                            <div>
                                <div class="input-group captcha-input-group">
                                    <input type="text" id="captcha" name="captcha"
                                           class="text-muted captcha-input-field" autocomplete="off"
                                           style="padding: 5px 10px; width: 165px; border: 1px solid silver;">
                                    <div class="input-group-append">
                                        <i class="fas fa-redo fa fa-refresh d-block refresh-captcha text-info"
                                           style="padding: 10px 15px; cursor: pointer; border: 1px solid silver;"></i>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                @error('captcha')
                <small class="text-danger">{{ __($message) }}</small>
                @enderror
            </div>
        @endif

        <div class="flexbox mb-4">
            @if(!\common\integration\BrandConfiguration::removeRememberMeButton())
                <span>
                    <label class="ui-switch switch-icon mr-2 mb-0">
                        <input type="checkbox" name="remember" {{\common\integration\BrandConfiguration::removeRememberMeButton() ? "hidden": (\common\integration\BrandConfiguration::defaultCheckRememberMeButton() ? '': 'checked')}} >
                        <span></span>
                    </label>{{__('Remember')}}
                </span>
            @endif
            @if(!session()->has('REMOTE_LOGIN'))
                <a class="text-primary" href="{{ route('password.request') }}">{{__('Forgot Password?')}}</a>
            @endif
        </div>
        <div class="text-center mb-4">
            <div class="d-grid">
                <button id="login_button"
                        {{\common\integration\BrandConfiguration::isAllowLoginCaptcha() && $apply_captcha && !session()->has('REMOTE_LOGIN') ? 'disabled'  : ''}} type="submit"
                        class="btn btn-primary btn-rounded btn-block text-uppercase">{{__(' Log In ')}}</button>
            </div>
        </div>
    </form>
    @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\Frontend\FrontendAdmin::class, 'isAllowResetTabFlagOnAdminLogin']))
        <script>
            if(localStorage?.openpages) {
                localStorage.removeItem('openpages'); // solved form auto-logout issue after login.
            }
        </script>
    @endif
    @if(\common\integration\BrandConfiguration::allowHiddenCaptcha())
        <script>
            grecaptcha.ready(function () {
                appendToken();
            });

            var appendToken = function () {
                grecaptcha.execute("{{ config('constants.GOOGLE_RECAPTCHA_PUBLIC_KEY') }}", {action: 'homepage'})
                    .then(function (token) {
                        document.getElementById('g-recaptcha-response').value = token;
                    });
            }
        </script>
    @endif
    @if(\common\integration\BrandConfiguration::isAllowLoginCaptcha() && $apply_captcha)
        @push('scripts')
            <script>
                $('#captcha').on('input', function () {
                    let val = $(this).val()
                    $(this).val(val.toUpperCase())
                })
            </script>
            @include('auth.captcha.captcha_script')
        @endpush
    @endif
    @include('partials.preventDoubleClickSubmission', ['form_id' => 'login-from', 'button_id' => 'login_button'])
@endsection
