<div data-url="{{ $notificationData['notification_url'] ?? '#' }}" class="d-flex notification-content">
    <div class="media-img">
        @isset($notificationData['icon_path'])
            <img class="img-circle" src="{{ $notificationData['icon_path'] }}" alt="image"/>
        @endisset
    </div>
    <div class="media-body align-middle">
        <div class="media-heading">{{ $notificationData['message'] }}</div>
    </div>
</div>
