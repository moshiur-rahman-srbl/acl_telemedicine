<a class="media p-3 checkedNotification" href="{{config('app.app_merchant_url')."/user-lists/".$data['merchant_id']}}">
    <div class="media-img">
        <img class="img-circle" src="{{Storage::url("assets/images/icons/menu/11-users.svg")}}" alt="image"/>
    </div>
    <div class="media-body">
        <div class="media-heading">Üye İşyeri hesabınıza yeni bir kullanıcı eklendi (admin/ekleyen için)
            <small data-created_at="{{$data['updated_at']}}" class="notification-time-calculation text-muted float-right"></small>
        </div>
    </div>
</a>
