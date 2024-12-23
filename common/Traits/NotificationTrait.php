<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11/5/2019
 * Time: 3:01 PM
 */

namespace App\Http\Controllers\Traits;


use App\Models\UserSetting;
use common\integration\BrandConfiguration;
use common\integration\GlobalFunction;
use common\integration\CommonNotification;
use common\integration\ManageLogging;
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

        if(!empty($is_email_enable) && !empty($receiver)){
            $this->processToSendEmail($data,$action,$receiver,$extra_param,$email_content_changed);
        }

        if(!empty($is_sms_enable)){
            $this->processToSendSMS($data,$action,$receiver,$extra_param);
        }

        if (!empty($is_push_notification_enable) && !empty($receiver)){
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

        if(isset($data['email_attachment']) && !empty($data['email_attachment'])){

            //$user_locale = session()->get('locale');
            $user_locale = App::getLocale();

            App::setLocale($receiver->language);

            $pdfloc = $this->export(
                'pdf_attacment',$data['email_attachment_data'],
                $action['pdf_file_name']."_".time().$receiver->id,null,$action['pdf_view']
            );

            App::setLocale($user_locale);

        }


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
        }

        //out_going_email - boss instructed to set priority as HIGH
        $this->setGNPriority($extra_param['priority_value'] ?? OutGoingEmail::PRIORITY_HIGH);
        $this->sendEmail($email_data, $subject_name, $from, $to,
            $attachment, $email_template, $email_lang, 0, true);

//        if(!empty($pdfloc)){
//            $this->deleteFile($pdfloc);
//        }
    }

    public function processToSendSMS($data,$action,$receiver,$extra_param){

        if(!empty($receiver)){
            $phone = $receiver->phone;
            $language = !empty($extra_param['sys_lang']) ? $extra_param['sys_lang'] : $receiver->language;
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
        if (!empty($sms_data['data']['OTP']) && !empty($sms_data['data']['is_login_otp']) && $sms_data['data']['is_login_otp']) {
            $sms_data['OTP'] = $sms_data['data']['OTP'];
        }
        $message = view("sms.".$sms_template .'_'. $language,$sms_data)->render();

        $this->setGNPriority($extra_param['priority_value'] ?? OutGoingEmail::PRIORITY_HIGH);
        $this->sendSMS($header, $message, $phone, $sms_data['data']['is_login_otp'] ?? 0);
    }

    public function processToPushNotification($data,$action,$receiver,$extra_param){
        $user_obj = $receiver;
        $notification_data = $data['notification_data'];
        $notification_template = $action['notification_template'] ?? '';
        $language = !empty($extra_param['sys_lang']) ? $extra_param['sys_lang'] : $user_obj->language;
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
            CommonNotification::createNotification($notification_data['admin_notification_action'], $notification_data);
        } else {
            $this->sendNotification($user_obj, $notification_data, $notification_template,$language);
        }

    }

    public function processOutgoingPushNotification($data,$action,$receiver,$extra_param)
    {
        try{
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
            $title=$data['app_notification']['title'];
            if(!empty($data['app_notification']['payload']['type'])){
                $title=CommonNotification::NOTIFICATION_MESSAGES[$data['app_notification']['payload']['type'] . '.'.strtoupper($language)];
            }
            $template_data['data'] = $data['app_notification'];
            $message = view("app_notification.".$template .'_'. $language,$template_data)->render();
            $payload=!empty($data['app_notification']['payload'])?$data['app_notification']['payload']:[];
            $outgoing_notification=new OutGoingPushNotification();
            $outgoing_notification->notify(
                (array)$receiver->id,
                $title,
                $message,
                $payload
            );
        }catch (\Exception $e){
            (new ManageLogging())->createLog([
                'action'=>'OUTGOING_PUSH_NOTIFICATION_EXCEPTION',
                'receiver'=>$receiver,
                'title'=>$data['app_notification']['title'] ?? '',
                'message'=>'',
                'payload'=>!empty($data['app_notification']['payload'])?$data['app_notification']['payload']:[],
                'exception'=>$e->getMessage()
            ]);
        }
    }
}