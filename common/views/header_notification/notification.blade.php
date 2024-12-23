
@if($panel_id == \App\Models\Profile::MERCHANT)

    @foreach($notifications as $notification)
        <span data-notificationid="{{$notification->id}}">

            @if(Config::get('app.locale') == "en")
                {!! $notification->data_en !!}
            @elseif(Config::get('app.locale') == "tr")
                {!! $notification->data_tr !!}
            @endif

        </span>
    @endforeach

@endif


@if($panel_id == \App\Models\Profile::ADMIN)

    @foreach($notifications as $notification)
        <div class="media notification_admin" style="cursor: pointer" >
            @if(Config::get('app.locale') == "en")
                <div class="media px-3 checkedNotification notification-item" href="{{ route(config('constants.defines.APP_NOTIFICATION_MARK_AS_READ'), $notification->id) }}">
                    {!! $notification->data_en !!}
                </div>
            @elseif(Config::get('app.locale') == "tr")
                <div class="media px-3 checkedNotification notification-item" href="{{ route(config('constants.defines.APP_NOTIFICATION_MARK_AS_READ'), $notification->id) }}">
                    {!! $notification->data_tr !!}
                </div>
            @endif
        </div>
    @endforeach

@endif
