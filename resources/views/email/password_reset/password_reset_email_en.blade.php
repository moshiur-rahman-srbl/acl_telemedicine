@include('email.partials.header')

<tr style="margin: 1px 20px;">
    <td style="padding:10px 10px; text-align: center; background: rgba(244,244,244,1); border-radius: 50px">
        @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\Frontend\FrontendMix::class, 'isAllowIconForPasswordResetEmail']))
            <p style="padding:10px 10px;text-align: center;">
                <img src="{{ Storage::url('assets/email_template_images/otp_png.png') }}"
                     style="width:60px; height:50px;" width="60" height="50"
                     alt="Approved"/>
            </p>
        @endif
        <p style="padding:10px 20px;line-height: 1.3rem; word-wrap: break-word; width: 600px; text-align: center;">
            In order to reset your password, please click on the link below or copy it to your browser:
            <br/><br/>
            <a href="{{$data['reset_url']}}">{{$data['reset_url']}}</a>
            <br/><br/>
            This request is made on
            <br/>
            IP: {{$data['ip_detail']}}
            <br/><br/>
            If this has not been done by you, please contact our customer service at {{config('brand.contact_info.phone_number')}} and notify us.
        </p>
    </td>
</tr>

@include('email.partials.footer')
