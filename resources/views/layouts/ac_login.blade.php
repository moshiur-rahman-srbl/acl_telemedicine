<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ Storage::url(config('brand.favicon')) }}" rel="shortcut icon"/>
    <title>{{ config('brand.name') }} | {{__('Admin Panel')}}</title>
    <!-- GLOBAL MAINLY STYLES-->
{{--    <link href="{{asset('adminca')}}/assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" />--}}
    @php
      StyleScript::bootstrapV5MinCss();
      StyleScript::fontAwesomeMinCss();
      StyleScript::lineAwesomeMinCss();
      StyleScript::themifyIconsCss();
      StyleScript::animateMinCss();
      StyleScript::toastrMinCss();
      StyleScript::bootstrapSelectMinCss();
      StyleScript::mainMinCss();
      StyleScript::brandStylesColors();
      StyleScript::mainV5Css();
      StyleScript::print(\common\integration\Design\StyleAndScript::CSS);
    @endphp


    <!-- PAGE LEVEL STYLES-->
    <style>
        body {
            background-repeat: no-repeat;
            background-size: cover;
            background-image: url('{{ Storage::url('assets/images/cover.jpg') }}');
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
            max-width: 400px;
            margin: 100px auto 50px;
        }

        .auth-head-icon {
            position: relative;
            height: 60px;
            width: 60px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            background-color: #fff;
            color: #5c6bc0;
            box-shadow: 0 5px 20px #d6dee4;
            border-radius: 50%;
            transform: translateY(-50%);
            z-index: 2;
            overflow: hidden;
        }
        a{
            text-decoration: none !important;
        }
    </style>
    @stack('css')
</head>

<body>
<div class="cover"></div>
<div class="ibox login-content">
    <div class="text-center">
        <span class="auth-head-icon"><img src="{{ Storage::url(config('brand.logo_2')) }}" alt="{{ config('brand.name') }}"></span>
    </div>
    @yield('content')
</div>
<!-- BEGIN PAGA BACKDROPS-->
<!-- <div class="sidenav-backdrop backdrop"></div>
<div class="preloader-backdrop">
    <div class="page-preloader">{{__("Loading")}}</div>
</div> -->
<!-- CORE PLUGINS-->

    @php
        StyleScript::jqueryMinJs();
        StyleScript::popperMinJs();
        StyleScript::bootstrapV5MinJs();
        StyleScript::metisMenuMinJs();
        StyleScript::jquerySlimscrollMinJs();
        StyleScript::idleTimerMinJs();
        StyleScript::toastrMinJs();
        StyleScript::jqueryValidateMinJs();
        StyleScript::bootstrapSelectMinJs();
        StyleScript::appMinJs();
        StyleScript::print(\common\integration\Design\StyleAndScript::JS);
    @endphp

<!-- PAGE LEVEL SCRIPTS-->
<!--<script>
    $(function() {
        $('#login-form').validate({
            errorClass: "help-block",
            rules: {
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true
                }
            },
            highlight: function(e) {
                $(e).closest(".form-group").addClass("has-error")
            },
            unhighlight: function(e) {
                $(e).closest(".form-group").removeClass("has-error")
            },
        });
    });
</script>-->
<script>
    $(document).ready(function () {
        $('#login-from').attr('autocomplete', 'off');
    });
</script>
@if(\common\integration\BrandConfiguration::disableWindoAnimation())
    @include('js_blades/modal/animation_disable')
@endif

@if(!\common\integration\BrandConfiguration::enableAutoComplete())
    @include('js_blades.autocomplete_off.off')
@endif

@stack('scripts')
</body>

</html>
