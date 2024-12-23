

@foreach($unread_notifications as $notification)
    <div class="media notification_admin" style="cursor: pointer">
        @if(Config::get('app.locale') == "en")
            <div class="media px-3 checkedNotification notification-item" href="">
                {!! $notification->data_en !!}
            </div>
        @elseif(Config::get('app.locale') == "tr")
            <div class="media px-3 checkedNotification notification-item" href="">
                {!! $notification->data_tr !!}
            </div>
        @endif
    </div>
@endforeach

