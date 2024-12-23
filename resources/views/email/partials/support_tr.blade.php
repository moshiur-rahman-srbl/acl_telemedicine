@php($aboutTr = config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.FP') ? 'özelinde':'hakkında')
<p style="padding:15px 20px;line-height: 1.3rem; text-align: center;">
    Eğer bu işlem sizin tarafınızdan gerçekleşmedi ise lütfen {{ config('brand.contact_info.email') }} ile iletişime geçerek konu {{ $aboutTr }} bilgilendirmede bulunun.
</p>
