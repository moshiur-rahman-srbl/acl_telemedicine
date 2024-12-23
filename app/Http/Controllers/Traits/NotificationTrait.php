<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/5/2019
 * Time: 3:01 PM
 */

namespace App\Http\Controllers\Traits;


use App\Models\NotificationAutomation;
use App\Models\UserSetting;
use common\integration\BrandConfiguration;
use common\integration\GlobalFunction;
use common\integration\CommonNotification;
use common\integration\Models\OutGoingEmail;
use common\integration\Models\OutGoingPushNotification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;


trait NotificationTrait
{

    public function checkAndSendNotification($action,$data,
                                             $is_email_enable=1,$is_sms_enable=1,$is_push_notification_enable=1,$receiver = null,$extra_param = null,$email_content_changed = false) {
        $isAppNotificationEnabled=1;
        if(!empty($receiver)){
            $user_id  = $receiver->id;
        }

        if(!empty($action) && array_key_exists($action['action_number'],UserSetting::actionArray) && !empty($receiver)){
            $userSetting = new UserSetting();
            $userSettingObj = $userSetting->getUserSettingsByIdAndAction($user_id, $action['action_number']);

            if(!empty($userSettingObj)){
                $is_email_enable = $userSettingObj->is_email_enable;
                $is_sms_enable = $userSettingObj->is_sms_enable;
                $isAppNotificationEnabled = $userSettingObj->is_app_notification_enable;
            }
        }

        if(!empty($is_email_enable)){
            $this->processToSendEmail($data,$action,$receiver,$extra_param,$email_content_changed);
        }

        if(!empty($is_sms_enable)){
            $this->processToSendSMS($data,$action,$receiver,$extra_param);
        }

        if (!empty($is_push_notification_enable)){
            $this->processToPushNotification($data,$action,$receiver,$extra_param);
        }
        $isBrandAllowed=BrandConfiguration::allowAppPushNotifications();
        $isUserSettingsEnabled=$isAppNotificationEnabled==UserSetting::APP_NOTIFICATION_ENABLED;
        $isNotificationDataAvailable=!empty($data['app_notification']);

        if($isBrandAllowed && $isUserSettingsEnabled && $isNotificationDataAvailable){
            $this->processOutgoingPushNotification($data,$action,$receiver,$extra_param);
        }
    }

    public function processToSendEmail($data,$action,$receiver,$extra_param,$email_content_changed = false){

        $email_form = config('app.SYSTEM_NO_REPLY_ADDRESS');
        if(isset($data['email_from'])){
            $email_form = $data['email_from'];
        }

        $pdfloc = null;

        if(isset($data['email_attachment'])){

            $user_locale = session()->get('locale');

            App::setLocale($receiver->language);

            $pdfloc = $this->fileExport(
                'pdf_attacment',$data['email_attachment_data'],
                $action['pdf_file_name']."_".time().$receiver->id,null,$action['pdf_view']
            );

            App::setLocale($user_locale);
        }

        if(isset($data['email_data'])){
            $email_data = $data['email_data'];
            $subject_name = $action['email_subject'];
            $from = $email_form;
            $to = $extra_param['receiver_emails'] ?? $receiver->email;
            $email_template = $action['email_template'];
            $attachment = $pdfloc;
            $email_lang = !empty($extra_param['sys_lang']) ? $extra_param['sys_lang'] : $receiver->language;

            if(BrandConfiguration::emailContentChanges()){
                $subject_name = GlobalFunction::brandFileNameConvention($email_content_changed).$action['email_subject'];
                $email_template = GlobalFunction::brandFileNameConvention($email_content_changed).$action['email_template'];
                if($email_content_changed && !empty($email_data['email_attachment_data'])){
                    $attachment = $email_data['email_attachment_data'];
                }
            }

            if (!empty($extra_param['send_via_automation'])) {
                $data['attachment'] = $attachment;
                $data['subject'] = $subject_name;
                $data['email_template'] = $email_template;
                $data['language'] = $email_lang;
                $data['sender_email'] = $from;
                $data['receiver_email'] = $to;
                $data['email_data']['data'] = $email_data;
                $data['send_via_automation'] = $extra_param['send_via_automation'];
                (new NotificationAutomation())->insertEntry($data, true);
            } else {
                //out_going_email - boss instructed to set priority as HIGH
                $this->setGNPriority($extra_param['priority_value'] ?? OutGoingEmail::PRIORITY_HIGH);
                $this->sendEmail($email_data, $subject_name, $from, $to,
                    $attachment, $email_template, $email_lang, '', 0, true);
            }
        }



//        if(!empty($pdfloc)){
//            $this->deleteFile($pdfloc);
//        }
    }

    public function processToSendSMS($data,$action,$receiver,$extra_param){

        if(!empty($receiver)){
            $phone = $receiver->phone;
            $language = $receiver->language;
        }else{
            $phone = $extra_param['phone'];
            $language = $extra_param['language'];
        }
        // check if language is empty to ignore exception
        if (empty($language)) {
            $language = GlobalFunction::getDefultLanguage();
        }

        $header = "";
        $sms_template = $action['sms_template'];

        $sms_data['data'] = $data['sms_data'];
        $message = view("sms.".$sms_template .'_'. $language,$sms_data)->render();

        if (!empty($extra_param['send_via_automation'])) {
            $data['phone'] = $phone;
            $data['language'] = $language;
            $data['sms_template'] = $sms_template;
            $data['sms_data']['data'] = $data['sms_data'];
            $data['send_via_automation'] = $extra_param['send_via_automation'];
            (new NotificationAutomation())->insertEntry($data, false, true);
        } else {
            // boss instructed to set priority as HIGH
            $this->setGNPriority($extra_param['priority_value'] ?? OutGoingEmail::PRIORITY_HIGH);
            $this->sendSMS($header, $message, $phone);
        }
    }

    public function processToPushNotification($data,$action,$receiver,$extra_param){
        $user_obj = $receiver;
        $assigned_user_id = $data['email_data']['ticket']->assigned_user_id ?? 0;
        $notification_data = $data['notification_data'];
        $notification_template = $action['notification_template'] ?? '';
        $language = $user_obj->language;
        // check if language is empty to ignore exception
        if (empty($language)) {
            $language = GlobalFunction::getDefultLanguage();
        }

        if (
            is_array($data)
            && array_key_exists('notification_data', $data)
            && is_array($data['notification_data'])
            && array_key_exists('admin_notification_action', $data['notification_data'])
            && in_array($data['notification_data']['admin_notification_action'], CommonNotification::NOTIFICATION_ACTIONS)
        ) {
            CommonNotification::createNotification($notification_data['admin_notification_action'], $notification_data, $assigned_user_id);
        }else{
            $this->sendNotification($user_obj, $notification_data, $notification_template,$language);
        }
    }

    public function processOutgoingPushNotification($data,$action,$receiver,$extra_param)
    {

        $template = $action['app_notification_template'];

        if(!empty($receiver)){
            $language = !empty($extra_param['sys_lang']) ? $extra_param['sys_lang'] : $receiver->language;
        }else{
            $language = $extra_param['language'];
        }
        // check if language is empty to ignore exception
        if (empty($language)) {
            $language = GlobalFunction::getDefultLanguage();
        }
        $template_data['data'] = $data['app_notification'];
        $message = view("app_notification.".$template .'_'. $language,$template_data)->render();
        $payload=!empty($data['app_notification']['payload'])?$data['app_notification']['payload']:[];
        $outgoing_notification=new OutGoingPushNotification();
        $outgoing_notification->notify(
            (array)$receiver->id,
            $data['app_notification']['title'],
            $message,
            $payload
        );
    }
}
