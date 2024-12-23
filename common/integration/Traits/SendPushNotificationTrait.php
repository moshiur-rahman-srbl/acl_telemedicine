<?php
namespace common\integration\Traits;

use common\integration\FcmNotificationService;
use common\integration\HmsNotificationService;

trait SendPushNotificationTrait {

    public function sendFcmNotification($notification,$receiver,$type,$payload=[]){
        $notify= new FcmNotificationService($receiver,$notification,$type,$payload);
        return $notify->send();
    }


}