<a class="media p-3 checkedNotification" href="{{config('app.app_merchant_url')}}">
    <div class="media-img">
        <img class="img-circle" src="{{Storage::url("assets/images/icons/others/user.svg")}}" alt="image"/>
    </div>
    <div class="media-body">
        <div class="media-heading">Welcome!
            <small data-created_at="{{$data['updated_at']}}" class="notification-time-calculation text-muted float-right"></small>
        </div>
        <div class="font-13 text-muted">Welcome to {{ config('brand.name') }}!</div>
    </div>
</a>
