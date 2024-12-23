<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width initial-scale=1.0">
    <link href="{{ Storage::url(config('brand.favicon')) }}" rel="shortcut icon"/>
    <title>{{ config('brand.name') }} | {{__('Admin Panel')}}</title>
    <!-- GLOBAL MAINLY STYLES-->
{{--    <link href="{{asset('adminca')}}/assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet"/>--}}
    <link href="{{asset('adminca')}}/assets/vendors/bootstrap/dist/css/bootstrap-v5.min.css" rel="stylesheet"/>
    <link href="{{asset('adminca')}}/assets/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet"/>
    <link href="{{asset('adminca')}}/assets/vendors/line-awesome/css/line-awesome.min.css" rel="stylesheet"/>
    <link href="{{asset('adminca')}}/assets/vendors/themify-icons/css/themify-icons.css" rel="stylesheet"/>
    <link href="{{asset('adminca')}}/assets/vendors/animate.css/animate.min.css" rel="stylesheet"/>
    <link href="{{asset('adminca')}}/assets/vendors/toastr/toastr.min.css" rel="stylesheet"/>
    <link href="{{asset('adminca')}}/assets/vendors/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet"/>
    <!-- PLUGINS STYLES-->
    <!-- THEME STYLES-->
    <link href="{{asset('adminca')}}/assets/css/main.min.css" rel="stylesheet"/>
    <link href="{{ asset(config('brand.styles.colors')) }}" rel="stylesheet"/>
    <!-- PAGE LEVEL STYLES-->
    <style>
        body {
            background-repeat: no-repeat;
            background-size: cover;
            {{--background-image: url('{{storage::url("assets/images/cover.jpg")}}');--}}
        }

        .cover {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(117, 54, 230, .1);
        }

        .login-content {
            max-width: 450px;
            margin: 100px auto 50px;
        }

        .auth-head-icon {
            position: relative;
            height: 60px;
            width: 60px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            background-color: #fff;
            color: #5c6bc0;
            box-shadow: 0 5px 20px #d6dee4;
            border-radius: 50%;
            transform: translateY(-50%);
            z-index: 2;
            overflow: hidden;
        }
    </style>
</head>

<body>
<div class="cover"></div>
<div class="ibox login-content">
    <form action="{{route('clearOTP')}}" method="get">
        <button type="submit" type="button" class="btn btn-outline-secondary btn-sm position-right">
            <i class="ti-close"></i>
        </button>
    </form>

    <div class="text-center">
        <span class="auth-head-icon"><img src="{{ Storage::url(config('brand.logo_2')) }}" class="img-rounded"></span>
    </div>

    <?php
    $mins = 0;
    $laravelCacheOTPKey = \common\integration\GlobalUser::getUserCacheOTPKey(auth()->user());
    if (\common\integration\GlobalFunction::hasBrandCache($laravelCacheOTPKey)) {
        $mins = \common\integration\GlobalFunction::getBrandCache($laravelCacheOTPKey)->diffInSeconds(\Carbon\Carbon::now());
    }
    ?>

    {{--<div><button class="btn btn-sm btn-primary"><i class="fa fa-angle-left"></i>--}}
            {{--Back To Login</button></div>--}}
    <form class="ibox-body pt-0" action="{{route('verifyOTP')}}" method="POST">
        @csrf
        <h4 class="font-strong text-center mb-4">{{__(':company Admin', ['company' => config('brand.name')])}}</h4>
        @include('OTP.flash')
        <div class="row pt-3">
            <div class="col-3 text-center">
                <i class="fa fa-5x fa-key img-circle"></i>

                @if (isset($data['check_security_image']) && $data['check_security_image'] && \common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class,'allowSecurityImageForAdminOtpPage']))
                    <img
                        src="{{ !empty($data['security_image_url']) ? Storage::url($data['security_image_url']) : '' }}">
                @endif

            </div>
            <div class="col-9">
                <p class="font-13">{{__('SMS with verification code sent to your mobile phone')}}</p>
                @if(\common\integration\BrandConfiguration::allowOtpTimerAdminLogin())
                    <div id="timer-div">
                        <p class="font-16 m-0 mb-2 p-0 text-purple" id="timersec"><span
                                class="text-muted">03:00</span></p>
                    </div>
                @endif

                <div class="form-group {{$errors->has('otp') ? 'has-error':''}}">
                    <input class="form-control" type="text" name="otp" placeholder="{{__('Verification code')}}">
<!--                    @if($errors->has('otp'))
                        <label class="help-block error">{{$errors->first('otp')}}</label>
                    @endif-->
                </div>
                <div class="form-group mt-3">
                    <div class="row">
                        <div class="col-sm-6 col-12 pb-2">
                            <div class="d-grid">
                                <button class="btn btn-primary" type="submit">
                                    <span class="btn-icon">{{__('Verify')}}</span>
                                </button>
                            </div>
                        </div>
                        <div id="resend-div" class="col-sm-6 col-12 pb-2 pl-sm-0">
                            <button class="btn btn-primary btn-block" id="resend_otp" onclick="resend()" type="button">
                                <span class="btn-icon">{{__('Resend')}}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@if(\common\integration\BrandConfiguration::allowWrongOTPAttempt())
    @include('OTP.secretquestion_password')
@endif

<!-- BEGIN PAGA BACKDROPS-->
<!-- <div class="sidenav-backdrop backdrop"></div>
<div class="preloader-backdrop">
    <div class="page-preloader">{{__("Loading")}}</div>
</div> -->
<form action="{{route('resendOTP')}}" method="post" id="resend">
    @csrf
    <input type="hidden" name="resend_otp" value="resend">
</form>
<!-- CORE PLUGINS-->
<script src="{{asset('adminca')}}/assets/vendors/jquery/dist/jquery.min.js"></script>
<script src="{{asset('adminca')}}/assets/vendors/popper.js/dist/umd/popper.min.js"></script>
<script src="{{asset('adminca')}}/assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="{{asset('adminca')}}/assets/vendors/metisMenu/dist/metisMenu.min.js"></script>
<script src="{{asset('adminca')}}/assets/vendors/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<script src="{{asset('adminca')}}/assets/vendors/jquery-idletimer/dist/idle-timer.min.js"></script>
<script src="{{asset('adminca')}}/assets/vendors/toastr/toastr.min.js"></script>
<script src="{{asset('adminca')}}/assets/vendors/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="{{asset('adminca')}}/assets/vendors/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<!-- PAGE LEVEL PLUGINS-->
<!-- CORE SCRIPTS-->
<script src="{{asset('adminca')}}/assets/js/app.min.js"></script>
<!-- PAGE LEVEL SCRIPTS-->
<script>
    function resend() {
        $('#resend').submit();
    }
    var duration = parseInt("{{$mins}}");
    var countdown;
    var timer = duration, minutes, seconds;
    countdown = setInterval(function () {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);
        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;
        $("#timersec").html(minutes + ":" + seconds);
        if (--timer < 0) {
            clearInterval(countdown);
            $('#timer-div').hide();
            $("#resend-div").empty();
            $("#resend-div").append('<div class="d-grid"><button class="btn btn-primary btn-block" onclick="resend()" type="button" id="resend_otp"><span class="btn-icon">{{__('Resend')}}</span></button></div>');
        }
    }, 1000);
</script>
@stack('scripts')
@section('css')
    <style>
        .position-right{
            position: absolute;
            right: 5px;
            top: 5px;
            border: 0px;
        }
    </style>

@endsection('css')
</body>

</html>
