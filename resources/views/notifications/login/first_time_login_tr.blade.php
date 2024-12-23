<a class="media p-3 checkedNotification" href="{{config('app.app_merchant_url')}}">
    <div class="media-img">
        <img class="img-circle" src="{{Storage::url("assets/images/icons/others/user.svg")}}" alt="image"/>
    </div>
    <div class="media-body">
        <div class="media-heading">Hoş Geldiniz!
            <small data-created_at="{{$data['updated_at']}}" class="notification-time-calculation text-muted float-right"></small>
        </div>
        <div class="font-13 text-muted">{{ config('brand.name') }}'e hoş geldiniz!</div>
    </div>
</a>
