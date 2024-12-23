@include('email.partials.header')

<tr style="margin: 1px 20px;">
    <td style="padding:10px 10px; text-align: center; background: rgba(244,244,244,1); border-radius: 50px">
        <p style="padding:10px 10px;text-align: center;">
            <img src="{{ Storage::url('assets/email_template_images/otp_png.png') }}" style="width:60px; height:50px;" width="60" height="50"
                 alt="Approved"/>
        </p>
        <b style="padding:10px 20px;line-height: 1.3rem; text-align: center;">
            {{ \common\integration\BrandConfiguration::getEmailChangeVerificationBody()['body'] }}
        </b>
        <p style="padding:25px 10px;text-align: center;">
            @if(\common\integration\BrandConfiguration::customerEmailVerificationRemoveButton())
                {{$data['link']}}
            @else
                <a type="button" id="git_btn" href="{{$data['link']}}" target="_blank">Verify</a>
                <br><br>
                <a href="{{$data['link']}}">{{$data['link']}}</a>
            @endif
            
        </p>
        

        @include('email.partials.support_en')
    </td>
</tr>

@include('email.partials.footer')
