
@if(\common\integration\BrandConfiguration::allowSideBarFooterContent())
    @include('css_blades.sidebar_footer_text_css')
    <div class="sidebar_footer_text {{@$css_class}}">
{{--        <p><i class="ti-announcement"></i>--}}
{{--            {{ __('İşlemlerimiz, 6493 sayılı Ödeme ve Menkul Kıymet Mutabakat Sistemleri, Ödeme Hizmetleri ve Elektronik Para Kuruluşları Hakkında Kanunu çerçevesinde faaliyet izni verilen Sipay Elektronik Para ve Ödeme Hizmetleri A.Ş. aracılığıyla gerçekleştirilmektedir.')  }}--}}
{{--        </p>--}}
    </div>
@endif