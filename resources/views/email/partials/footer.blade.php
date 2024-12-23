<?php
$youtube = config('brand.social_media.youtube');
?>
<tr style="">
    <td style="padding:35px 20px; text-align: center;" class="custom-footer-design">
        <p style="marginh: 0; padding: 0; text-align: center;">
            @if(\common\integration\BrandConfiguration::socialMediaButtonsRemoved())
                <a href="https://{{ config('brand.contact_info.website') }}" target="_blank"><img
                        src="{{ Storage::url(config('brand.logo_2')) }}" alt="{{ config('brand.name') }}"></a>
            @else
                <a href="{{ config('brand.social_media.facebook') }}" target="_blank"
                   style="margin: 5px; display: inline-block"><img
                        src="{{ Storage::url('assets/email_template_images/facebook.png') }}" width="35"
                        height="35" alt="facebook"></a>
                <a href="{{ config('brand.social_media.twitter') }}" target="_blank"
                   style="margin: 5px; display: inline-block"><img
                        src="{{ Storage::url('assets/email_template_images/twitter.png') }}" width="35" height="35"
                        alt="twitter"></a>
                <a href="{{ config('brand.social_media.linkedin') }}" target="_blank"
                   style="margin: 5px; display: inline-block"><img
                        src="{{ Storage::url('assets/email_template_images/linkedin.png') }}" width="35"
                        height="35" alt="linkedin"></a>
                <a href="{{ config('brand.social_media.instagram') }}" target="_blank"
                   style="margin: 5px; display: inline-block"><img
                        src="{{ Storage::url('assets/email_template_images/instagram.png') }}"
                        width="35" height="35" alt="instagram"></a>
                @if(!\common\integration\BrandConfiguration::disableYoutubeIconInEmailFooter())
                @if(isset($youtube) && !empty($youtube) && $youtube !='#')
                    <a href="{{ config('brand.social_media.youtube') }}" target="_blank"
                       style="margin: 5px; display: inline-block"><img
                            src="{{ Storage::url('assets/email_template_images/youtube.png') }}"
                            width="35" height="35" alt="youtube"></a>
                @endif
                @endif
            @endif
        </p>
        <br>
        @if(\common\integration\BrandConfiguration::receiptAndEmailContentChanges())
            <a target="_blank" href="https://{{ config('brand.contact_info.website') }}" >{{ config('brand.contact_info.website') }}</a> | {{ config('brand.contact_info.email') }}
        @elseif(empty($allow_custom_footer_welcome_mail))
            {{ config('brand.contact_info.phone_number') }} | {{ config('brand.contact_info.email') }}
        @endif
        <br>
        <br>

        @if(!empty($allow_custom_footer_welcome_mail) && $allow_custom_footer_welcome_mail)

            @include('email.custom_footer_welcome_mail_'.config('brand.name_code'))
        @else

        <p class="brand_name">{{ config('brand.contact_info.company_full_name') }}</p>
        <p class="address">
            {{ config('brand.contact_info.address_line_1') }}
            <br/>
            {{ config('brand.contact_info.address_line_2') }}
        </p>

        @endif
    </td>
</tr>
</tbody>
</table>
</body>
</html>
