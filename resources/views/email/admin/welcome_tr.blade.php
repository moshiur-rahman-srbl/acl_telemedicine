@include('email.partials.header')

<tr>
    <td>
        <p style="padding:30px 20px;">
            Sayın<br>{{ $data['name'] }},
            <br><br>
            {{ config('brand.name') }}'e Hoş geldiniz, Şifrenizi oluşturmak için aşağıdaki linke tıklayınız
            <br><br>
            Admin Panel Link: <a target="_blank" href="{{ $data['admin_panel_link'] }}" style="color: #007bff;">{{ $data['admin_panel_link'] }}</a>
            <br><br>
            <a target="_blank" href="{{ $data['create_password_link'] }}" style="display: block;text-align: center;text-decoration: none;margin: 0;border: solid 1px transparent;border-radius: 4px;padding: 0.5em 1em;color: #FFFFFF;background-color: #007bff;">Şifrenizi Oluşturun</a>
            <br>veya linki tarayıcınıza yapıştırın <strong>{{ $data['create_password_link'] }}</strong>
        </p>
    </td>
</tr>

@include('email.partials.footer', ['allow_custom_footer_welcome_mail' => \common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\Frontend\FrontendMix::class, 'allowCustomFooterInWelcomeMail'])])
