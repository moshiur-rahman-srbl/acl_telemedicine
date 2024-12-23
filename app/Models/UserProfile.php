<?php

namespace App\Models;

use App\Http\Controllers\Traits\CommonLogTrait;
use App\Http\Controllers\Traits\ExportExcelTrait;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Controllers\Traits\SendEmailTrait;
use App\User;
use Carbon\Carbon;
use common\integration\Models\OutGoingEmail;
use common\integration\Override\Model\CustomBaseModel as Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use common\integration\Models\UserProfile as CommonUserProfile;
class UserProfile extends Model
{
    use SendEmailTrait, FileUploadTrait, CommonLogTrait, ExportExcelTrait;

    protected $guarded = [];
    const DISABLED = 0;
    const ENABLED = 1;
    const STATUS_LIST = [
        self::DISABLED => 'Inactive',
        self::ENABLED => 'Active'
    ];

    const ACTIVE = 1;
    const INACTIVE = 4;
    const SUSPECTED = 2;
    const BANNED = 3;
    const BLACKLISTED = 5;

    const FACE_TO_FACE_VERIFIED = 1;
    const FACE_TO_FACE_NOT_VERIFIED = 0;
    const IS_MONEY_TRANSFER_MAX_EXCEED_YES = 1;
    const IS_MONEY_TRANSFER_MAX_EXCEED_NO = 0;
    const MONEY_TRANSFER_FROM_TOTAL_BALANCE_YES = 1;
    const MONEY_TRANSFER_FROM_TOTAL_BALANCE_NO = 0;

    const OTHER_SECTOR_ID = 67;
    const OTHER_SECTOR_CODE = 'OT';

    const CUSTOMER = 0;
    const ADMIN = 1;
    const MERCHANT = 2;

    public static function getUserStatuses (): array
    {
        return [
            self::ACTIVE => 'Active',
            self::SUSPECTED => 'Suspected',
            self::BANNED => 'Banned',
            self::INACTIVE => 'Inactive',
            self::DISABLED => 'Disabled',
        ];
    }

    public function addUserProfiles($user_id, array $data = [])
    {
        return $this->firstOrCreate(
            [
                "user_id" => $user_id
            ],
            [
                "enable_money_transfer" => self::ENABLED,
                "status" => self::ACTIVE,
                "is_show_balance" => array_get($data, 'is_show_balance', self::ENABLED),
                "is_welcome_email_sent" => array_get($data, 'is_welcome_email_sent', false)
            ]
        );
    }

    public function updateUserProfileByUserId($user_id, array $data = []): int
    {
        $updatable_data = [];

        if (array_key_exists('is_welcome_email_sent', $data)) {
            $updatable_data['is_welcome_email_sent'] = (bool) $data['is_welcome_email_sent'];
        }
        if (array_key_exists('status', $data)) {
            $updatable_data['status'] = $data['status'];
        }
        return self::query()
            ->where('user_id', $user_id)
            ->update($updatable_data);
    }

    public function getUserProfilesById($user_id) {
        return $this->where('user_id', $user_id)->first();
    }

    public function updateUserProfiles($user_id, $data) {

        $status = false;

        $userObj = $this->getUserProfilesById($user_id);
        if(empty($userObj)){
            $userObj = $this->addUserProfiles($user_id);
        }

        if (!empty($userObj)){
            $userObj->fillable = CommonUserProfile::FILLABLE_COLUMNS;
            $status = $userObj->update($data);
        }

        return $status;
    }

    public function isMoneyRequestEnable($user_id) {
        $moneyRequest = $this->where('user_id', $user_id)->first();
        if(!empty($moneyRequest)) {
            return $moneyRequest->enable_money_transfer;
        } else {
            return true;
        }
    }


    public function checkDoneKYCAndSendMail() {
        $query = $this->where('kyc_updated_at', '>=', date('Y-m-d 00:00:00', strtotime("-1 days")))
            ->where('kyc_updated_at', '<=', date('Y-m-d 23:59:59', strtotime("-1 days")))
            ->get();

        if(count($query) > 0) {
            $userObj = new User();
            $sectors = (new Sector())->getAllActiveSectors(self::CUSTOMER);

            $filename = 'KYC_List' . '.csv';
            $heading = [
                __('ID'),
                __('Name'),
                __('Surname'),
                __('Phone'),
                __('Email'),
                __('TCKN'),
                __('Sector'),
                __('Date of Birth')
            ];
            $transaction_row = [];
            $flg = 0;

            foreach ($query as $key => $result) {
                $user = $userObj->getUserDetailsById($result->user_id);

                if(!empty($user)) {
                    $sector = $sectors->where('id', $user->sector_id)->first();
                    $transaction_row[$key] = [
                        $user->id,
                        $user->first_name,
                        $user->last_name,
                        $user->phone,
                        $user->email,
                        $user->tc_number,
                        !empty($sector) ? $sector->name : '-',
                        $user->dob
                    ];
                    $flg = 1;
                }
            }

            if($flg) {
                $fullpath = $this->exportNsaveServer($heading, $transaction_row, $filename, true);

                $data['kyc_done_date'] = $data['value1'] = date('d.m.Y', strtotime("-1 days"));
                $template = "kyc_completed_list.list";
                $from = Config::get('constants.defines.MAIL_FROM_ADDRESS');
                $to = Config::get('constants.defines.COMPLIANCE_EMAIL');
                $attachment = Storage::url($fullpath);

                //out_going_email
                $this->setGNPriority(OutGoingEmail::PRIORITY_LOW);
                $this->sendEmail($data, "kyc_completed_list", $from, $to, $attachment, $template, "tr", '', 0, true);
//                Storage::delete($fullpath);
            }
        }
    }


}
