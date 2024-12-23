<!DOCTYPE html>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="keywords" content="">
<title>{{ config('brand.name') }}</title>
<link href="{{ Storage::url(config('brand.favicon')) }}" rel="shortcut icon">
<link href="https://fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800,900&display=swap" rel="stylesheet">
<!-- Bootstrap core CSS -->
<link href="{{asset('adminca')}}/assets/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet"/>
<!-- Custom styles for this template -->
<link href="{{asset('errorpage')}}/style.css" rel="stylesheet">
</head>
<body class="wa_body" style="background-color:#ffffff;">
    <div class="wa_body_in">
        <section class="wa_pagewrapper wa_loginwrapper" style="border-radius:40px;">
            <div class="wa_login error_page">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="error_left">
                            <p><img src="{{ Storage::url(config('brand.logo')) }}" alt="logo" width="40%" height="auto"></p>
                            <p>{{$statuscode}} - {{__('Service Unavailable')}}</p>
                            <h2>{{!empty($message)? $message:__('The service is temporarily unavailable.')}}</h2>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="error_right">
                        <img src="{{asset('errorpage')}}/error-image.png" alt="img">
                        </div>
                    </div>
                </div>
                {{--<p align="center"><a href="{{route('home')}}" class="btn btn-outline-primary">{{__('HOMEPAGE')}}</a></p>--}}
            </div>
        </section>
    </div>
<!-- Bootstrap core JavaScript -->

</body></html>
