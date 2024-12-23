<?php

namespace common\integration\Models;

use App\Http\Controllers\Traits\SendEmailTrait;
use common\integration\ManageLogging;
use common\integration\Utility\Helper;
use common\integration\Override\Model\CustomBaseModel as Model;

class OutGoingEmail extends Model
{
    use SendEmailTrait;

    protected $table = 'out_going_email';
    protected $guarded = [];
    public $exception_msg = '';

    const CRON_SMS_EXPRESS = 'process:outgoingSMSExpress';
    const CRON_SMS_NORMAL = 'process:outgoingSMSNormal';
    const CRON_EMAIl_EXPRESS = 'process:outgoingEmailExpress';
    const CRON_EMAIL_NORMAL = 'process:outgoingEmailNormal';

    const CRON_COMMON_SMS_AND_EMAIL_EXPRESS_COMMANDS = 'execute:smsAndEmailCommands';

    const LIMIT_EXPRESS = 10;
    const LIMIT_NORMAL = 20;
    const TYPE_SMTP = 1;
    const TYPE_SOAP = 2;
    const TYPE_API_MAIL_SERVICE = 3;

    const PRIORITY_NULL = 0;
    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_EXPRESS = 4;
    const PRIORITY_LIST = [
        self::PRIORITY_LOW => 'LOW',
        self::PRIORITY_MEDIUM => 'MEDIUM',
        self::PRIORITY_HIGH => 'HIGH',
        self::PRIORITY_EXPRESS => 'EXPRESS'
    ];

    const STATUS_PENDING = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_SUCCESSFUL = 3;
    const STATUS_FAILED = 4;

    public static function getCronJobList (): array
    {
        $cron_job_list = [
            // self::CRON_SMS_EXPRESS,
            // self::CRON_EMAIl_EXPRESS
        ];

        if (Helper::isDevServerEnvironment()) {
            $cron_job_list = [
                // self::CRON_COMMON_SMS_AND_EMAIL_EXPRESS_COMMANDS
            ];
        }

        return $cron_job_list;
    }

    public function saveData ($data)
    {
        return self::query()->create($data);
    }

    public function getData ($search)
    {
        $query = self::query();

        if (isset($search['priority'])) {
            if (is_array($search['priority'])) {
                $query->whereIn('priority', $search['priority']);
            } else {
                $query->where('priority', $search['priority']);
            }
        }
        if (isset($search['status'])) {
            $query->where('status', $search['status']);
        }
        if (isset($search['limit']) && $search['limit'] > 0) {
            $query->limit($search['limit']);
        }
        if (isset($search['sort_by']) && !empty($search['sort_by'])) {
            $query = $query->get()->sortBy($search['sort_by']);
        }

        return $query;
    }

    public function processCronJob ($filter)
    {
        $emailData = $this->getData($filter);

        if (count($emailData) > 0) {

            // $priorityLabel = self::PRIORITY_LIST[$filter['priority']] ?? '-';
            // (new ManageLogging())->createLog([
            //     'action' => 'PROCESS_OUTGOING_EMAIL_'.$priorityLabel,
            //     'data_count' => count($emailData)
            // ]);

            foreach ($emailData as $key => $value) {
                $value->status = self::STATUS_IN_PROGRESS;
                $value->save();

                $extras = [
                    'from_cronjob' => true,
                    'type' => $value->type,
                    'cc' => $value->cc,
                    'bcc' => $value->bcc
                ];
                $this->sendEmail(
                    $value->body,
                    $value->subject,
                    $value->from,
                    $value->to,
                    $value->attachment,
                    '',
                    '',
                    self::PRIORITY_NULL,
                    $value->unlink,
                    false,
                    $extras
                );

                if (!empty($this->exception_msg)) {
                    $status = self::STATUS_FAILED;
                    $note = $this->exception_msg;
                } else {
                    $status = self::STATUS_SUCCESSFUL;
                    $note = '';
                }
                $value->status = $status;
                $value->note = $note;
                $value->save();
            }
        }else{

            sleep(5);

        }
    }

    public function cleanOldData(){

        return self::query()
            ->where('status', self::STATUS_SUCCESSFUL)
            ->where('updated_at', '<=', now()->subHours(2)->toDateTimeString())
            ->take(10000)
            ->delete();

    }

}
