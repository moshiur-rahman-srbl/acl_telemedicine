<footer class="page-footer">
    {{--<div class="font-13">2018 © <b>Adminca</b> - Save your time, choose the best</div>--}}
    <div>
        <?php

        $footer_copyright = $copyright = '';
        if(\common\integration\GlobalFunction::hasBrandCache('footer')){
            $footer_copyright = \common\integration\GlobalFunction::getBrandCache('footer');
        }
        if(!empty($footer_copyright)){
            if(app()->getLocale() == 'tr'){
                $copyright = $footer_copyright['footer_tr'] ?? '';
            }else{
                $copyright = $footer_copyright['footer_en'] ?? '';
            }
        }


        ?>
        {{$copyright}}
    </div>
    <div>
        <p style="font-weight: bold">{{ config('brand.name') . ' Admin v.' . config('app.app_version') }} </p>
        {{--<a class="px-3 pl-4" href="http://themeforest.net/item/adminca-responsive-bootstrap-4-3-angular-4-admin-dashboard-template/20912589" target="_blank">Purchase</a>--}}
        {{--<a class="px-3" href="http://admincast.com/adminca/documentation.html" target="_blank">Docs</a>--}}
    </div>
    <div class="to-top"><i class="fa fa-angle-double-up"></i></div>
</footer>
