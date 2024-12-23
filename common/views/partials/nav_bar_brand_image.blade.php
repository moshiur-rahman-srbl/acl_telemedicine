@if(\common\integration\BrandConfiguration::allowSideBarLogo())
    @include('css_blades.sidebar_footer_text_css')
    <div class="top_img_area">
        <img src="{{Storage::url(\App\Models\Setting::first()->logo_path)}}" alt="">
    </div>
@endif