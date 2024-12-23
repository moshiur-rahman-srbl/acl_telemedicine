<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ Storage::url(config('brand.favicon')) }}" rel="shortcut icon"/>
    <title>{{config('brand.name')}} | {{__('Reset Password')}}</title>
    <!-- GLOBAL MAINLY STYLES-->
{{--    <link href="{{asset('adminca')}}/assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" />--}}
    <link href="{{asset('adminca')}}/assets/vendors/bootstrap/dist/css/bootstrap-v5.min.css" rel="stylesheet"></link>
    <link href="{{asset('adminca')}}/assets/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
    <link href="{{asset('adminca')}}/assets/vendors/line-awesome/css/line-awesome.min.css" rel="stylesheet" />
    <link href="{{asset('adminca')}}/assets/vendors/themify-icons/css/themify-icons.css" rel="stylesheet" />
    <link href="{{asset('adminca')}}/assets/vendors/animate.css/animate.min.css" rel="stylesheet" />
    <link href="{{asset('adminca')}}/assets/vendors/toastr/toastr.min.css" rel="stylesheet" />
    <link href="{{asset('adminca')}}/assets/vendors/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet" />
    <!-- PLUGINS STYLES-->
    <!-- THEME STYLES-->
    <link href="{{asset('adminca')}}/assets/css/main.min.css" rel="stylesheet" />
    <link href="{{ asset(config('brand.styles.colors')) }}" rel="stylesheet"/>
    <link href="{{asset('adminca')}}/assets/css/main-v5.css" rel="stylesheet"/>
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
    </style>
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
    <div class="page-preloader">Loading</div>
</div> -->
<!-- CORE PLUGINS-->
<script src="{{asset('adminca')}}/assets/vendors/jquery/dist/jquery.min.js"></script>
<script src="{{asset('adminca')}}/assets/vendors/popper.js/dist/umd/popper.min.js"></script>
{{--<script src="{{asset('adminca')}}/assets/vendors/bootstrap/dist/js/bootstrap.min.js"></script>--}}
<script src="{{asset('adminca')}}/assets/vendors/bootstrap/dist/js/bootstrap-v5.min.js"></script>
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
@include('partials.css_blade.alertify')
@include('partials.js_blade.alertify')
<script>
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


        // Float Number only check
        $('.float-number-only').on('input', function() {
            this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
        });
        // Number only check
        $(".number-only").keypress(function(e){
            var keyCode = e.which;
            if (keyCode < 48 || keyCode > 57) {
                return false;
            }
        });
        //Number only check
        $(".digit-len").keypress(function(e){
            var len = $(this).val().length;
            if (len > 5) {
                return false;
            }
        });

    @if(\common\integration\BrandConfiguration::secretQuestionResetPassword())
    $("#submit_btn").on("click", function (event) {
        event.preventDefault();
        $(".selectLoader").prop('disabled',true).append('<i class="fa fa-refresh fa-spin" style="font-size:15px;margin:10px;height:15px;"></i>');
        var formData = $("#forgetPasswordfrm").serialize();
        $.ajax({
            url: "{{route('password.checkSecretQuestion')}}",
            type: 'post',
            data: formData + '&action=checkSecretQuestion',
            success: function (response, textStatus, jQxhr) {
                $(".fa-spin").remove();
                 $(".selectLoader").prop('disabled',false);
                if (response.status == true && response.otp == true) {
                    $('#otp_confirmation').modal('show');
                    $('#otp_confirmation #confirmed').click(function () {
                        var otp = $('#otp_confirmation #otp').val();
                        var csrf = "{{csrf_token()}}"
                        var formData = {
                            'otp': otp,
                            'action': 'checkOtp',
                            '_token': csrf,
                        };
                        $.ajax({
                            url: "{{route('password.checkSecretQuestion')}}",
                            type: 'post',
                            data: formData,
                            success: function (response, textStatus, jQxhr) {

                                if (response.otp_status == true) {
                                    $('#otp_confirmation').modal('hide');
                                    $("#forgetPasswordfrm").submit();
                                } else if (response.otp_status == false) {
                                    $('.otp-invalid-feedback').text(response.message).css({'font-size':'0.9rem','color': 'red'});
                                } else {
                                    $('.otp-invalid-feedback').text(response).css({'font-size':'0.9rem','color': 'red'});
                                }
                            }
                        });
                    });
                }else if(response.status == false){
                   var msg = "<strong class='p-0 m-0 d-block text-danger'>" + response.message + "</strong>";
                   alertify.okBtn("{{__('Ok')}}").cancelBtn("{{__('Cancel')}}").confirm(msg, function () {
                       if(typeof(response.redirect_to_login) != "undefined" && response.redirect_to_login !== null && response.redirect_to_login) {
                            window.location.href = '{{ route('login') }}'
                       }else {
                           window.location.reload();
                       }
                   }, function () {
                       return false;
                   });
               }else {
                   var msg = "<strong class='p-0 m-0 d-block text-danger'>" + response + "</strong>";
                   alertify.okBtn("{{__('Ok')}}").cancelBtn("{{__('Cancel')}}").confirm(msg, function () {
                       window.location.reload();
                   }, function () {
                       return false;
                   });


               }
            },
            error: function (jqXhr, textStatus, errorThrown) {
                console.log(errorThrown);
                $(".fa-spin").remove();
            }
        });
    });
    @endif

</script>
@stack('scripts')
@if(\common\integration\BrandConfiguration::disableWindoAnimation())
    @include('js_blades/modal/animation_disable')
@endif
</body>

</html>
