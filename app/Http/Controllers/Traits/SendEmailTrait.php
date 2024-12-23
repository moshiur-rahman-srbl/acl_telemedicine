<?php
namespace App\Http\Controllers\Traits;

use App\Mail\SendMail;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\GlobalFunction;
use common\integration\Models\OutGoingEmail;
use common\integration\RMCMailService;
use common\integration\Utility\Helper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use common\integration\BrandConfiguration;
use common\integration\ManageLogging;
use common\integration\SoapMailer;
use Illuminate\Support\Facades\View;

trait SendEmailTrait {

    private $global_notification_priority = OutGoingEmail::PRIORITY_NULL;

    public function setGNPriority ($priority)
    {
        $this->global_notification_priority = $priority;
    }

    public function getGNPriority ()
    {
        return $this->global_notification_priority;
    }


    public function sendEmail($data, $subject_label, $from, $to, $attachment, $template, $language,
                                $custom_subject='', $priority=OutGoingEmail::PRIORITY_NULL, $unlink_attachment=false, $extras=[])
    {

        /*
         * START NEW FLOW
         */
        return $this->sendEmailProcess($data, $subject_label, $from, $to, $attachment, $template, $language, $custom_subject, $priority, $unlink_attachment, $extras);
        /*
         * END NEW FLOW
         */

        $manageLogObj = new ManageLogging();
        try {

        /*
         * WHEN TO MAIL IS EMPTY IT WILL THROW EXCEPTION
         */
        throw_if(empty($to), __('to mail required.'));


        $priority = $this->getGNPriority();

        if (isset($extras['from_cronjob']) && $extras['from_cronjob']) {

            $body = $data;
            $email_subject = $subject_label;
            $physical_template = $template;
            $type = $extras['type'] ?? $this->getEmailType();
            $is_rendered_view = true;

        } else {

            if (empty($language) || (!empty($language) && !in_array($language, config('constants.SYSTEM_SUPPORTED_LANGUAGE')))) {
                $language = "tr";
            }

            if ($template == 'automation') {
                $physical_template = $template;
                $email_subject = $subject_label;
            } elseif ($template == 'cashout.file_uploaded') {
                $physical_template = $template . "_" . $language;
                $email_subject = $subject_label;
            } else {
                $physical_template = $template . "_" . $language;
                $email_subject = $this->getEmailSubject($subject_label, $language, $custom_subject);
            }

            if (isset($data["value1"])) {
                $email_subject = str_replace("<var1>", $data["value1"], $email_subject);
            }
            if (isset($data["value2"])) {
                $email_subject = str_replace("<var2>", $data["value2"], $email_subject);
            }
            if (isset($data["value3"])) {
                $email_subject = str_replace("<var3>", $data["value3"], $email_subject);
            }

            $body = View::make('email.' . $physical_template, ['data' => $data])->render();
            $type = $this->getEmailType();
            $is_rendered_view = false;

        }


        if(GlobalFunction::checkEmailNotification($data) && !empty($body)){
            GlobalFunction::sentEmailNotification($data, $body);
        }


        $exception = '';
        $log_write = true;
        $manageLogObj = new ManageLogging();
        $email_via = '';

            if (BrandConfiguration::sendEmailAndSmsViaCron() && $priority != OutGoingEmail::PRIORITY_NULL) {
                $to = is_array($to) ? implode(',', array_filter($to)) : $to;
                $email_via = 'Cron';
                $inputData = [
                    "from" => $from,
                    "to" => $to,
                    "cc" => $extras['cc'] ?? null,
                    "bcc" => $extras['bcc'] ?? null,
                    "subject" => $email_subject,
                    "body" => $body,
                    "attachment" => $attachment,
                    "unlink" => $unlink_attachment,
                    "priority" => $priority,
                    "type" => $type,
                    "status" => OutGoingEmail::STATUS_PENDING
                ];
                $result = (new OutGoingEmail())->saveData($inputData);
                /*$manageLogObj->createLog([
                    'action' => 'OUT_GOING_EMAIL',
                    'from' => $from,
                    'to' => $to,
                    'subject' => $email_subject,
                    'priority' => $priority,
                    // 'inputs' => $inputData,
                    'result' => !empty($result)
                ]);*/
            } else {
                $to = is_array($to) ? $to : explode(',', str_replace(' ', '', $to));

                if ($type == OutGoingEmail::TYPE_SOAP) {
                    $log_write = false;
                    $email_via = 'Soap';
                    (new SoapMailer())->sendEmail($to, $from, $email_subject, $body, $attachment);
                }elseif ($type == OutGoingEmail::TYPE_API_MAIL_SERVICE){
                    $email_via = 'RMCMail';
                    (new RMCMailService())->sendEmail($to, $from, $email_subject, $body, $attachment);
                }  else {
                    $email_via = 'Mail';
                    $is_sent = Mail::to($to)->send(new SendMail($data, $email_subject, $from, $attachment, $physical_template, $is_rendered_view));

                    if ($is_sent) {
                        if ($unlink_attachment) {
                            File::delete($attachment);
                        }
                    }
                }
            }

            if ($log_write) {
                $manageLogObj->createLog([
                    'action' => 'EMAIL_SENDING_SUCCESS',
                    'via' => $email_via,
                    'from' => $from,
                    'to' => $to,
                    'subject' => $email_subject,
                ]);
            }

        } catch(\Throwable $e) {

            /*if ($unlink_attachment) {
                File::delete($attachment);
            }*/
            $exception = $e->getMessage();
            $manageLogObj->createLog([
                'action' => 'EMAIL_SENDING_FAILED',
                'via' => $email_via ?? null,
                'reason' => $e->getMessage(),
                'from' => $from,
                'to' => $to,
                'subject' => $email_subject ?? null,
            ]);

        }

        if (property_exists($this, 'exception_msg')) {
            $this->exception_msg = $exception;
        }

        return true;
    }

    public function sendEmailProcess($data, $subject_label, $from, $to, $attachment, $template, $language, $custom_subject='', $priority=OutGoingEmail::PRIORITY_NULL, $unlink_attachment=false, $extras=[], int $max_attempt=3, int $retry_sleep=500)
    {
        $manageLogObj = new ManageLogging();
        $attempt_count = 0;
        try {

            /*
             * WHEN TO MAIL IS EMPTY IT WILL THROW EXCEPTION
             */
            throw_if(empty($to), __('to mail required.'));


            $priority = $this->getGNPriority();

            if (isset($extras['from_cronjob']) && $extras['from_cronjob']) {

                $body = $data;
                $email_subject = $subject_label;
                $physical_template = $template;
                $type = $extras['type'] ?? $this->getEmailType();
                $is_rendered_view = true;

            } else {

                if (empty($language) || (!empty($language) && !in_array($language, config('constants.SYSTEM_SUPPORTED_LANGUAGE')))) {
                    $language = "tr";
                }

                if ($template == 'automation') {
                    $physical_template = $template;
                    $email_subject = $subject_label;
                } elseif ($template == 'cashout.file_uploaded') {
                    $physical_template = $template . "_" . $language;
                    $email_subject = $subject_label;
                } else {
                    $physical_template = $template . "_" . $language;
                    $email_subject = $this->getEmailSubject($subject_label, $language, $custom_subject);
                }

                if (isset($data["value1"])) {
                    $email_subject = str_replace("<var1>", $data["value1"], $email_subject);
                }
                if (isset($data["value2"])) {
                    $email_subject = str_replace("<var2>", $data["value2"], $email_subject);
                }
                if (isset($data["value3"])) {
                    $email_subject = str_replace("<var3>", $data["value3"], $email_subject);
                }

                $body = View::make('email.' . $physical_template, ['data' => $data])->render();
                $type = $this->getEmailType();
                $is_rendered_view = false;

            }


            if(GlobalFunction::checkEmailNotification($data) && !empty($body)){
                GlobalFunction::sentEmailNotification($data, $body);
            }


            $exception = '';
            $log_write = true;
            $manageLogObj = new ManageLogging();
            $email_via = '';

            if (BrandConfiguration::sendEmailAndSmsViaCron() && $priority != OutGoingEmail::PRIORITY_NULL) {
                $to = is_array($to) ? implode(',', array_filter($to)) : $to;
                $email_via = 'Cron';
                $inputData = [
                    "from" => $from,
                    "to" => $to,
                    "cc" => $extras['cc'] ?? null,
                    "bcc" => $extras['bcc'] ?? null,
                    "subject" => $email_subject,
                    "body" => $body,
                    "attachment" => $attachment,
                    "unlink" => $unlink_attachment,
                    "priority" => $priority,
                    "type" => $type,
                    "status" => OutGoingEmail::STATUS_PENDING
                ];
                $result = (new OutGoingEmail())->saveData($inputData);
                /*$manageLogObj->createLog([
                    'action' => 'OUT_GOING_EMAIL',
                    'from' => $from,
                    'to' => $to,
                    'subject' => $email_subject,
                    'priority' => $priority,
                    // 'inputs' => $inputData,
                    'result' => !empty($result)
                ]);*/
            } else {
                $to = is_array($to) ? $to : explode(',', str_replace(' ', '', $to));

                $is_sent = false;
                try {


                    if ($type == OutGoingEmail::TYPE_SOAP) {
                        $log_write = false;
                        $email_via = 'Soap';

                        Helper::reTry($max_attempt, function() use($to, $from, $email_subject, $body, $attachment) {

                            (new SoapMailer())->sendEmail($to, $from, $email_subject, $body, $attachment);

                        }, $retry_sleep, attempt_counter: $attempt_count);

                    } elseif ($type == OutGoingEmail::TYPE_API_MAIL_SERVICE) {
                        $email_via = 'RMCMail';
                        Helper::reTry($max_attempt, function() use($to, $from, $email_subject, $body, $attachment) {

                            (new RMCMailService())->sendEmail($to, $from, $email_subject, $body, $attachment);

                        }, $retry_sleep, attempt_counter: $attempt_count);

                    } else {
                        $email_via = 'Mail';
                        Helper::reTry($max_attempt, function() use($data, $email_subject, $from, $attachment, $physical_template, $is_rendered_view, $to) {

                            Mail::to($to)->send(new SendMail($data, $email_subject, $from, $attachment, $physical_template, $is_rendered_view));

                        }, $retry_sleep, attempt_counter: $attempt_count);
                        $is_sent = true;

                    }
                } catch (\Exception $e) {

                    throw $e;
                }

                if ($is_sent && $unlink_attachment) {
                    File::delete($attachment);
                }
            }

            if ($log_write) {
                $manageLogObj->createLog([
                    'action' => 'EMAIL_SENDING_SUCCESS',
                    'via' => $email_via,
                    'from' => $from,
                    'to' => $to,
                    'subject' => $email_subject,
                    'attempt_count' => $attempt_count,
                ]);
            }

        } catch(\Throwable $e) {

            /*if ($unlink_attachment) {
                File::delete($attachment);
            }*/
            $exception = $e->getMessage();
            $manageLogObj->createLog([
                'action' => 'EMAIL_SENDING_FAILED',
                'via' => $email_via ?? null,
                'reason' => $e->getMessage(),
                'from' => $from,
                'to' => $to,
                'subject' => isset($subject_label) ? $subject_label : ($email_subject ?? null),
                'attempt_count' => $attempt_count,
            ]);

        }

        if (property_exists($this, 'exception_msg')) {
            $this->exception_msg = $exception;
        }

        return true;
    }

    private function getEmailSubject($labelName, $language='tr', $custom_subject='')
    {
        $old_lang = app()->getLocale();
        app()->setLocale($language);

        if($custom_subject) {
            $subject = $labelName;
        } else {
            $subject = __('subject.'.$labelName);
        }
        app()->setLocale($old_lang);

        return $subject;
    }

    private function getEmailType ()
    {
        if (BrandConfiguration::sendMailViaWsdl()) {
            $type = OutGoingEmail::TYPE_SOAP;
        }elseif (BrandConfiguration::call([Mix::class, 'isAllowRmcEmailService'])){
            $type = OutGoingEmail::TYPE_API_MAIL_SERVICE;
        } else {
            $type = OutGoingEmail::TYPE_SMTP;
        }

        return $type;
    }

}
