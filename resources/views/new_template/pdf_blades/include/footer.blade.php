<tr style="">
    <td style="padding:30px 20px; text-align: center;">
        <p style="marginh: 0; padding: 0; text-align: center;">

            @include('global.pdf_blades.includes.social_media_footer')

{{--            <a href="{{ config('brand.social_media.facebook') }}" target="_blank"--}}
{{--               style="margin: 0px 5px; display: inline-block"><img--}}
{{--                    src="{{ Storage::url('assets/email_template_images/facebook.png') }}" width="30"--}}
{{--                    height="30" alt="facebook"></a>--}}
{{--            <a href="{{ config('brand.social_media.twitter') }}" target="_blank"--}}
{{--               style="margin: 0px 5px; display: inline-block"><img--}}
{{--                    src="{{ Storage::url('assets/email_template_images/twitter.png') }}" width="30" height="30"--}}
{{--                    alt="twitter"></a>--}}
{{--            <a href="{{ config('brand.social_media.linkedin') }}" target="_blank"--}}
{{--               style="margin: 0px 5px; display: inline-block"><img--}}
{{--                    src="{{ Storage::url('assets/email_template_images/linkedin.png') }}" width="30"--}}
{{--                    height="30" alt="linkedin"></a>--}}
{{--            <a href="{{ config('brand.social_media.instagram') }}" target="_blank"--}}
{{--               style="margin: 0px 5px; display: inline-block"><img--}}
{{--                    src="{{ Storage::url('assets/email_template_images/instagram.png') }}"--}}
{{--                    width="30" height="30" alt="instagram"></a>--}}
{{--            @if(!\common\integration\BrandConfiguration::disableYoutubeIconInEmailFooter())--}}
{{--                <a href="{{ config('brand.social_media.youtube') }}" target="_blank"--}}
{{--                   style="margin: 0px 5px; display: inline-block"><img--}}
{{--                        src="{{ Storage::url('assets/email_template_images/youtube.png') }}"--}}
{{--                        width="30" height="30" alt="youtube"></a>--}}
{{--            @endif--}}
        </p>
        <br>
        @if(\common\integration\BrandConfiguration::receiptAndEmailContentChanges())
            <a target="_blank" href="https://{{ config('brand.contact_info.website') }}" >{{ config('brand.contact_info.website') }}</a> | {{ config('brand.contact_info.email') }}
        @else
            {{ config('brand.contact_info.phone_number') }} | {{ config('brand.contact_info.email') }}
        @endif
        <br>
        <br>
        <p class="brand_name">{{ config('brand.contact_info.company_full_name') }}</p>
        <p class="address">
            {{ config('brand.contact_info.address_line_1') }}
            <br/>
            {{ config('brand.contact_info.address_line_2') }}
        </p>
        <br>
        @php
            if(is_object($data)){
                  $data = \common\integration\GlobalFunction::objectTypeStdClassArray($data);
            }
        @endphp
        @if(\common\integration\BrandConfiguration::allowExtraTextInFooter() && isset($data['extra_text_in_footer'])
        && $data['extra_text_in_footer'])
            <p class="address">
                {{ __("Your Transactions, Payment and Securities Settlement Systems No. 6493, Payment Sipay Elektronik Para ve Dağıtım Hizmetleri A.Ş., which is allowed to operate within the framework of the Law on Services and Electronic Money Institutions. carried out through") }}
            </p>
        @endif
    </td>
</tr>
</tbody>
</table>
</body>
</html>
