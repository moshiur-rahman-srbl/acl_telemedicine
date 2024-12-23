<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class CustomDbChannel
{

    public function send($notifiable, Notification $notification)
    {
        $data = $notification->toDatabase($notifiable);

        return $notifiable->routeNotificationFor('database')->create([
            'id' => $notification->id,
            'notification_subcategory_id' => $data['notification_subcategory_id'] ?? null,
            'type' => get_class($notification),
            'data_en' => $data['data_en'],
            'data_tr' => $data['data_tr'],
            'read_at' => null,
            'user_type' => $data['user_type'] ?? 100
        ]);
    }

}
