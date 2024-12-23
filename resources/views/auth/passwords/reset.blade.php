@extends('layouts.app')

@section('content')

    @if(\common\integration\BrandConfiguration::allowHiddenCaptcha())
        <script src='https://www.google.com/recaptcha/api.js?render={{ config('constants.GOOGLE_RECAPTCHA_PUBLIC_KEY') }}'></script>
        <style>
            .grecaptcha-badge {
                visibility: hidden;
            }
        </style>
    @endif

    <style>
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
    @if(\common\integration\BrandConfiguration::isPasswordSaveRestrictedToBrowser())
        @include('partials.secure_font_css')
    @endif
    <?php
    $flipr = false;
    if(isset($create_new) && $create_new) {
        $flipr = true;
    }

    if (config('constants.PASSWORD_SECURITY_TYPE') == \App\Models\Profile::SIX_DIGITS_PASSWORD) {
        $passClass = 'number-only digit-len';
        $minLen = $maxLen = 6;
        $labelTxt = __("Password must be 6 digit number only");
    } else {
        $passClass = '';
        $minLen = 0;
        $maxLen = 64;
        $labelTxt = __("The password must be 8 characters long, must contain a mix of upper/lowercase letters, numbers, and special characters");
    }
    ?>

    <form class="ibox-body" action="" method="POST">
        @csrf
        <h4 class="font-strong text-center mb-5 text-uppercase">
            @if($flipr)
                {{__('Create New Password')}}
            @else
                {{__('Reset Password')}}
            @endif
        </h4>
        @include('partials.flash')
        <?php
        $verrors = session()->has('verrors') ? session()->get('verrors') : [];
        session()->forget('verrors');
        session()->save();

        $inputType = 'password';
        if(\common\integration\BrandConfiguration::isPasswordSaveRestrictedToBrowser()){
            $inputType = 'text';
        }
        ?>

        <input type="hidden" name="reset_token" value="{{ $reset_token }}">
        <div class="form-group mb-4">
            <input id="email" class="form-control form-control-line bg-white" type="text" name="email" placeholder="{{__('Email')}}" value="{{$email}}" readonly>
            <small class="text-danger font-italic">{{$verrors['email'] ?? ''}}</small>
        </div>
        <div class="form-group mb-4">
            <input id="new_password-confirm" class="form-control form-control-line secure_text_font {{$passClass}}" type="{{ $inputType }}"
                   name="new_password" placeholder="{{__('Password')}}" minlength="{{$minLen}}" maxlength="{{$maxLen}}" required>
            <small class="text-danger font-italic">{{$verrors['new_password'] ?? ''}}</small>
            {{--            <small class="text-primary d-block">[{{$labelTxt}}]</small>--}}
        </div>
        <div class="form-group mb-4">
            <input id="verify_password" class="form-control form-control-line secure_text_font {{$passClass}}" type="{{ $inputType }}"
                   name="verify_password" placeholder="{{__('Confirm Password')}}" minlength="{{$minLen}}" maxlength="{{$maxLen}}" required>
            <small class="text-danger font-italic">{{$verrors['verify_password'] ?? ''}}</small>
        </div>
        @if(\common\integration\BrandConfiguration::isAllowLoginCaptcha() && $apply_captcha)
            <div class="form-group mb-4 text-center captcha-div">
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
                <small class="text-danger">{{ __($verrors['captcha'] ?? '') }}</small>
            </div>
        @endif
        @if(\common\integration\BrandConfiguration::allowHiddenCaptcha())
            <input type="hidden" id="g-recaptcha-response" name="google_captcha">
            <script>
                grecaptcha.ready(function() {
                    appendToken();
                });

                var appendToken = function () {
                    grecaptcha.execute("{{ config('constants.GOOGLE_RECAPTCHA_PUBLIC_KEY') }}", {action: 'homepage'})
                        .then(function(token) {
                            document.getElementById('g-recaptcha-response').value=token;
                        });
                }
            </script>
        @endif

        {{--        <div class="form-group">--}}
        {{--            @if ($errors->has('user_type'))--}}
        {{--                <span class="text-danger" >--}}
        {{--                        <strong>{{ $errors->first('user_type') }}</strong>--}}
        {{--                    </span>--}}
        {{--            @endif--}}
        {{--            <input type="hidden" name="user_type" value="2"/>--}}
        {{--        </div>--}}
        <div class="text-center mb-4">
            <button type="submit" class="btn btn-primary btn-rounded btn-block" {{\common\integration\BrandConfiguration::isAllowLoginCaptcha() && $apply_captcha ? 'disabled'  : ''}} id="password-reset-btn">
                @if($flipr)
                    {{__(' Create ')}}
                @else
                    {{__('Reset Password')}}
                @endif
            </button>
        </div>
    </form>
    @if(\common\integration\BrandConfiguration::isAllowLoginCaptcha() && $apply_captcha)
        @push('scripts')
            <script>
                $('#captcha').on('input', function () {
                    let val = $(this).val()
                    $(this).val(val.toUpperCase())
                })
            </script>
            @include('auth.captcha.captcha_script' , [ 'button_id' => 'password-reset-btn'])
        @endpush
    @endif

    @if(!\common\integration\BrandConfiguration::enableAutoComplete())
        @push('scripts')
            @include('js_blades.autocomplete_off.off')
        @endpush
    @endif
    {{--<div class="container">--}}
    {{--<div class="row justify-content-center">--}}
    {{--<div class="col-md-8">--}}
    {{--<div class="card">--}}
    {{--<div class="card-header">{{ __('Reset Password') }}</div>--}}

    {{--<div class="card-body">--}}
    {{--<form method="POST" action="{{ route('password.update') }}">--}}
    {{--@csrf--}}

    {{--<input type="hidden" name="token" value="{{ $token }}">--}}

    {{--<div class="form-group row">--}}
    {{--<label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>--}}

    {{--<div class="col-md-6">--}}
    {{--<input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" required autofocus>--}}

    {{--@if ($errors->has('email'))--}}
    {{--<span class="invalid-feedback" role="alert">--}}
    {{--<strong>{{ $errors->first('email') }}</strong>--}}
    {{--</span>--}}
    {{--@endif--}}
    {{--</div>--}}
    {{--</div>--}}

    {{--<div class="form-group row">--}}
    {{--<label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>--}}

    {{--<div class="col-md-6">--}}
    {{--<input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>--}}

    {{--@if ($errors->has('password'))--}}
    {{--<span class="invalid-feedback" role="alert">--}}
    {{--<strong>{{ $errors->first('password') }}</strong>--}}
    {{--</span>--}}
    {{--@endif--}}
    {{--</div>--}}
    {{--</div>--}}

    {{--<div class="form-group row">--}}
    {{--<label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>--}}

    {{--<div class="col-md-6">--}}
    {{--<input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>--}}
    {{--</div>--}}
    {{--</div>--}}

    {{--<div class="form-group row mb-0">--}}
    {{--<div class="col-md-6 offset-md-4">--}}
    {{--<button type="submit" class="btn btn-primary">--}}
    {{--{{ __('Reset Password') }}--}}
    {{--</button>--}}
    {{--</div>--}}
    {{--</div>--}}
    {{--</form>--}}
    {{--</div>--}}
    {{--</div>--}}
    {{--</div>--}}
    {{--</div>--}}
    {{--</div>--}}
@endsection
