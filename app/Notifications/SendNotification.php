<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SendNotification extends Notification
{
    use Queueable;

    public $data_en;
    public $data_tr;
    public $subject;
    public $user;
    public $notification_subcategory_id;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data_en,$data_tr, $user = null, $notification_subcategory_id = null)
    {
        $this->data_en = $data_en;
        $this->data_tr = $data_tr;
        $this->notification_subcategory_id = $notification_subcategory_id;
        if (!empty($user)){
            $this->user = $user;
        }

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [CustomDbChannel::class];
    }

    // /**
    //  * Get the mail representation of the notification.
    //  *
    //  * @param  mixed  $notifiable
    //  * @return \Illuminate\Notifications\Messages\MailMessage
    //  */
    // public function toMail($notifiable)
    // {
    //     return (new MailMessage)
    //                 ->line('The introduction to the notification.')
    //                 ->action('Notification Action', url('/'))
    //                 ->line('Thank you for using our application!');
    // }


    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return[
            'data_tr'=>$this->data_tr,
            'data_en'=>$this->data_en,
            'notification_subcategory_id' => $this->notification_subcategory_id,
            'user_type' => $this->user->user_type ?? 100
        ];
    }


    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'data_tr'=>$this->data_tr,
            'data_en'=>$this->data_en,
            'notification_subcategory_id' => $this->notification_subcategory_id,
            'user_type' => $this->user->user_type ?? 100
        ];
    }
}
