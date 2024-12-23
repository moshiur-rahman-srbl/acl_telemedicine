@if (!common\integration\BrandConfiguration::isCustomMailAllow())
    Profil güncellemesine devam etmek için kullanmanız gereken şifre: {{$OTP}}.
@else
    Profil güncellemesine devam etmek için kullanmanız gereken tek kullanımlık doğrulama kodunuz: {{$OTP}}. Lütfen bu kodu kimse ile paylaşmayınız.
@endif