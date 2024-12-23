<a href="{{config('app.app_frontend_url')}}/deposits" class="noti_list checkedNotification">
    <span><img src="{{Storage::url("assets/images/icons/menu/6-MoneyTransfer.svg")}}" alt="icon"></span>
    Bilgileriniz onaylandı! Artık diğer kullanıcılardan para isteyebilir, hesabınıza para yatırabilir ve hesabınızdaki bakiye kadar harcama yapabilirsiniz. Para transfer etmek ve bakiyenizi banka hesabınıza çekmek için en az bir adet başarılı para yatırma işlemi yapmalısınız.
    <br/>
    <small data-created_at="{{$data['created_at']}}">Welcome to {{ config('brand.name') }}!</small>
</a>
