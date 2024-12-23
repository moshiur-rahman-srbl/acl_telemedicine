<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{__('Password Reset')}}</title>
</head>
<body>
{{__('Dear User')}},
<br/><br/>
{{__('Please click on the link below to reset your new password.')}}
<br/>
<a href="{{$actionUrl}}" target="_blank">{{__('Reset Password')}}<a>
        <br><br>
        {{__('Best Regards')}}.<br/>
</body>
</html>