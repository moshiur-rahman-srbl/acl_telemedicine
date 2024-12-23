<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ config('brand.name') }} Şifremi Unuttum</title>
    <style>
        .font{
            font-size: 16px;
        }
    </style>
</head>
<body class="font">
<br/>
Sayın {{$data['name']}},
<br><br>
<p class="font">Şifre sıfırlama işleminizi gerçekleştirmek için lütfen aşağıdaki linke tıklayınız ya da tarayıcınıza kopyalayınz.</p>

<br><br>
<a href="{{ $data['reset_url'] }}">
    {{ $data['reset_url'] }}
</a>

<p class="font">Eğer şifre sıfırlama talebi sizin tarafınızdan gerçekleştirilmediyse lütfen iligli linke tıklamayınız ya da  tarayıcınıza kopyalamayınız! Derhal şifrenizi değiştirerek hesap güvenliği sağlayın ve müşteri hizmetlerimiz ile iletişime geçerek konu hakkında bilgilendirmede bulunun!</p>

<br/><br/>
Bu e-posta otomatik olarak gönderilmiştir. Lütfen cevap vermeyiniz!
<br/><br/>
<img src="{{ Storage::url(config('brand.logo_2')) }}" alt="{{ config('brand.name') }}" width="120" height="auto">
</body>
</html>
