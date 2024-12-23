@if(!common\integration\BrandConfiguration::isCustomMailAllow())
    @if(\common\integration\BrandConfiguration::isCustomizedOtpMessage())
        Your one-time verification code to log into your {{ config('brand.name') }} account: {{$OTP}}. Please do not share this code with anyone.
    @else
        Your login OTP is {{$OTP}}
    @endif
@else
    Your one-time verification code to log into your {{ config('brand.name') }} account: {{$OTP}}
@endif
