@include('email.partials.header')
@if(isset($data['name']))
    <tr>
        <td>
            <p class="greetings">
                Dear {{ $data['name'] }},
            </p>
        </td>
    </tr>
@endif

<tr style="margin: 1px 20px;">
    <td style="padding:10px 10px; text-align: center; background: rgba(244,244,244,1); border-radius: 50px">
        <p style="padding:10px 10px;text-align: center;">
            <img src="{{ Storage::url('assets/email_template_images/otp_png.png') }}"
                 style="width:60px; height:50px;" width="60" height="50"
                 alt="Approved"/>
        </p>
        <p style="padding:10px 20px;line-height: 1.3rem; text-align: center;">
            @if(!common\integration\BrandConfiguration::isCustomMailAllow())
                Your login OTP is : {{$data['otp']}}
            @else
                The password you need to use to proceed with the profile update is: {{$data['otp']}}
            @endif
        </p>
        @include('email.partials.support_en')
    </td>
</tr>

@include('email.partials.footer')
