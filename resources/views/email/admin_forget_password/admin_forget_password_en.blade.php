<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ config('brand.name') }} Forgot Password</title>
    <style>
        .font{
            font-size: 16px;
        }
    </style>
</head>
<body class="font">

Dear {{$data['name']}},
<br><br>
<p class="font">To perform your password reset, please click on the link below or copy it to your browser.</p>

<br><br>

<a href="{{ $data['reset_url'] }}">
    {{ $data['reset_url'] }}
</a>

<p class="font">If the password reset request has not been performed by you, please do not click on the link or copy it to your browser! Immediately change your password to ensure account security and contact our customer service to inform about the issue!</p>

<br/><br/>
This email has been sent automatically. Please do not reply!
<br/><br/>
<img src="{{ Storage::url(config('brand.logo_2')) }}" alt="{{ config('brand.name') }}" width="120" height="auto">
</body>
</html>
