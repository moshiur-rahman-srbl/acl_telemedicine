@if (!common\integration\BrandConfiguration::isCustomMailAllow())
    The password you need to use to continue the profile update: {{$OTP}}.
@else
    Your one-time verification code to continue the profile update: {{$OTP}} Please do not share this code with anyone.
@endif