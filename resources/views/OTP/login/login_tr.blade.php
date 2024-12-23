@if(!common\integration\BrandConfiguration::isCustomMailAllow())
    @if(\common\integration\BrandConfiguration::isCustomizedOtpMessage())
        @if(config('brand.name_code') == "PP")
        {{config('brand.name')}} doğrulama kodunuz: {{$OTP}}. Lütfen kimseyle paylaşmayın.
        @endif
        @if(config('brand.name_code') == "PL")
            {{config('brand.name')}} hesabina {{$OTP}} kodu ile giris yapabilirsin. Guvenligin icin giris kodunu kimse ile paylasma.
        @endif
    @else
        Giriş OTP'niz {{$OTP}}
    @endif
@else
    {{ config('brand.name') }} tek kullanımlık doğrulama kodunuz: {{$OTP}}
@endif
