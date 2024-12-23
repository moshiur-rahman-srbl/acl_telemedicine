<?php

namespace App\Models;

use common\integration\Override\Model\CustomBaseModel as Model;

class UserSetting extends Model
{
    protected $fillable = ['user_id', 'is_sms_enable', 'is_email_enable','is_app_notification_enable','action_name'];
    public $timestamps = false;
    const SMSENABLED = 1;
    const EMAILENABLED = 1;
    const PUSH_NOTIFICATION_ENABLED = 1;

    const SMS_DISABLED = 0;
    const EMAIL_DISABLED = 0;
    const PUSH_NOTIFICATION_DISABLED = 0;

    const APP_NOTIFICATION_ENABLED=1;
    const APP_NOTIFICATION_DISABLED=0;

    const UPLOAD_FILE_TYPES = ['png', 'jpg', 'jpeg', 'webp', 'tif', 'tiff', 'pdf']; //file extension
    const UPLOAD_FILE_SIZE = 5; //in MB (mega byte)

    const REGISTER = 100,
        KYC_SUCCESS = 100,
        FORGET_PASSWORD = 100,
        CHANGE_PHONE = 100,
        ACCOUNT_BLOCK = 100,
        ACCOUNT_BLOCK_WRONG_PASSWORD = 100,
        ACCOUNT_BLOCK_WRONG_OTP = 100,
        ACCOUNT_BANNED = 100,
        NEW_BANK_ACCOUNT = 100,
        DELETE_BANK_ACCOUNT = 100,
        MONEY_RECEIVE_CASHOUT = 100,
        CHANGE_PASSWORD = 1,
        CHANGE_EMAIL = 2,
        DEPOSIT_REQUEST = 3,
        DEPOSIT_APPROVE = 4,
        DEPOSIT_REJECT = 4,
        WITHDRAW_REQUEST = 5,
        WITHDRAW_APPROVE = 6,
        WITHDRAW_REJECT = 6,
        MONEY_TRANSFER = 7,
        MONEY_TRANSFER_NONSIPAY = 7,
        MONEY_REQUEST_SEND = 9,
        MONEY_REQUEST_RECEIVE = 11,
        MONEY_RECEIVE = 8,
        MONEY_RECEIVE_NONSIPAY = 8,
        MONEY_REQUEST_APPROVE = 10,
        MONEY_REQUEST_REJECT = 10,
        TICKET_CLOSE = 12,
        TICKET_REPLY = 12,
        WALLET_REFUND = 13,
        PAYMENT_CHANGE = 14,
        NEW_MESSAGE = 15,
        OTP = 100;

    const action_lists = [
        'admin_ticket_notification' => [
            'email_subject' => 'support_ticket',
            'email_template' => 'support_ticket.support_ticket',
            'notification_template' => 'admin.ticket.notification',
            'action_number' => 602,
        ],
        'admin_merchant_iks_terminal_info' => [
            'email_subject' => 'serial_number_installation',
            'email_template' => 'merchant.merchant_terminal_update',
            'action_number' => 603,
        ],
        'deposit_approve' => [
            'email_subject' => 'deposit_approve',
            'email_template' => 'deposit_by_sipay_bank.deposit_approve',
            'sms_template' => 'deposit_by_sipay_bank.deposit_approve',
            'app_notification_template' => 'deposit_by_sipay_bank.deposit_approve',
            'notification_template' => 'deposit_by_sipay_bank.deposit_approve',
            'pdf_view' => 'pdf_blades.receipts.deposit',
            'pdf_file_name' => 'receipt',
            'action_number' => 4,
        ],
        'deposit_reject' => [
            'email_subject' => 'deposit_reject',
            'email_template' => 'deposit_by_sipay_bank.deposit_reject',
            'sms_template' => 'deposit_by_sipay_bank.deposit_reject',
            'notification_template' => 'deposit_by_sipay_bank.deposit_reject',
            'action_number' => 4,
        ],
        'deposit_refund_bank_failed' => [
            'email_subject' => 'deposit_refund_bank_failed',
            'email_template' => 'deposit_refund_bank_failed.deposit_refund_failed',
            'notification_template' => 'deposit.deposit_refund_failed',
            'action_number' => 500,
        ],
        'ticket_close' => [
            'email_subject' => 'ticket_close',
            'email_template' => 'support_ticket.ticket_close',
            'notification_template' => 'support_ticket.ticket_close',
            'action_number' => 253,
        ],
        'ticket_reply' => [
            'email_subject' => 'ticket_reply',
            'email_template' => 'support_ticket.ticket_reply',
            'sms_template' => 'support_ticket.ticket_reply',
            'notification_template' => 'support_ticket.ticket_reply',
            'action_number' => 12,
        ],
        'withdrawal_approve' => [
            'email_subject' => 'withdrawal_request_completed',
            'email_template' => 'withdrawal_confirmation.withdrawal_confirmation',
            'sms_template' => 'withdrawal.withdrawal_success_message',
            'notification_template' => 'withdraw.withdraw_approve',
            'pdf_view' => 'pdf_blades.receipts.withdrawal',
            'pdf_file_name' => 'withdrawal_receipt',
            'action_number' => 6,
        ],
        'withdrawal_reject' => [
            'email_subject' => 'withdrawal_request_rejected',
            'email_template' => 'withdrawal_rejection.withdrawal_rejection',
            'sms_template' => 'withdrawal.withdrawal_reject_message',
            'notification_template' => 'withdraw.withdraw_reject',
            'pdf_view' => 'pdf_blades.receipts.withdrawal',
            'pdf_file_name' => 'withdrawal_receipt',
            'action_number' => 6,
        ],
        'non_sipay_send_money_transfer_failed' => [
            'email_subject' => 'non_sipay_send_money_transfer_failed',
            'email_template' => 'money_transfer.non_sipay_failed',
            'sms_template' => 'money_transfer.non_sipay_send_money_transfer_failed',
            'notification_template' => 'money_transfer.non_sipay_send_money_transfer_failed',
            'action_number' => 215
        ],
        'account_banned' => [
            'email_subject' => 'account_banned',
            'email_template' => 'account_banned.account_banned',
            'action_number' => 258,
        ],
        'account_suspended' => [
            'email_subject' => 'account_suspended',
            'email_template' => 'account_suspended.account_suspended',
            'action_number' => 259,
        ],
        'account_activated' => [
            'email_subject' => 'account_activated',
            'email_template' => 'account_activated.account_activated',
            'action_number' => 260,
        ],
        'refund_request_approve' => [
            'email_subject' => 'refund_made_to_wallet',
            'email_template' => 'refund_request.refund_request_approve',
            'notification_template' => 'refund.refund_approve',
            'action_number' => 500,
        ],

        'merchant_wallet_update_at_cron_job' => [
            'email_subject' => 'available_balance_updated',
            'email_template' => 'merchant_wallet_update.available_balance_update',
            'action_number' => 501,
        ],

        'send_money_transfer_success' => [
            'email_subject' => 'money_transfer_success',
            'email_template' => 'money_transfer.sender_success',
            'notification_template' => 'money_transfer.completed.completed',
            'sms_template' => 'money_transfer.send_money_success',
            'pdf_view' => 'pdf_blades.receipts.money_transfer_attacment',
            'pdf_file_name' => 'receipt',
            'action_number' => '7',
        ],
        'receive_money_transfer_success' => [
            'email_subject' => 'receive_money_transfer_success',
            'email_template' => 'money_transfer.receiver_success',
            'notification_template' => 'money_transfer.incoming_completed.completed',
            'sms_template' => 'money_transfer.receive_money_success',
            'pdf_view' => 'pdf_blades.receipts.receive_money_transfer_attacment',
            'pdf_file_name' => 'receipt',
            'action_number' => '8',
        ],
        'aml_approve' => [
            'email_subject' => 'aml_approve_subject',
            'email_template' => 'aml_transaction.aml_approve',
            'sms_template' => 'aml_transaction.aml_approve',
            'pdf_view' => 'pdf_blades.receipts.deposit',
            'pdf_file_name' => 'receipt',
            'action_number' => 651,
        ],
        'aml_reject' => [
            'email_subject' => 'aml_reject_subject',
            'email_template' => 'aml_transaction.aml_reject',
            'sms_template' => 'aml_transaction.aml_reject',
            'action_number' => 652,
        ],

        'aml_approve_withdrawal' => [
            'email_subject' => 'aml_approve_subject',
            'email_template' => 'aml_transaction.aml_approve',
            'sms_template' => 'aml_transaction.aml_approve',
            'pdf_view' => 'pdf_blades.receipts.withdrawal',
            'pdf_file_name' => 'receipt',
            'action_number' => 653,
        ],
        'aml_approve_sendmoney' => [
            'email_subject' => 'aml_approve_subject',
            'email_template' => 'aml_transaction.aml_approve',
            'sms_template' => 'aml_transaction.aml_approve',
            'action_number' => 654,
        ],
        'merchant_user_create' => [
            'email_subject' => 'merchant_user_create',
            'email_template' => 'merchant.user_create',
            'notification_template' => 'login.first_time_login',
            'action_number' => 655,
        ],
        'merchant_user_create_v1' => [
            'email_subject' => 'merchant_user_create',
            'email_template' => "merchant.merchant_create_v1",
            'notification_template' => 'login.first_time_login',
            'action_number' => 655,
        ],
        'merchant_chargeback_request' => [
            'email_subject' => 'merchant_chargeback_request',
            'email_template' => 'merchant_chargeback.request',
            'notification_template' => 'merchant_chargeback.request',
            'sms_template' => 'merchant_chargeback.request',
            'action_number' => 656,
        ],
        'merchant_chargeback_status' => [
            'email_subject' => 'merchant_chargeback_status',
            'email_template' => 'merchant_chargeback.status',
            'notification_template' => 'merchant_chargeback.status',
            'action_number' => 657,
        ],
        'merchant_chargeback_reject' => [
            'email_subject' => 'merchant_chargeback_reject',
            'email_template' => 'merchant_chargeback.reject',
            'notification_template' => 'merchant_chargeback.reject',
            'action_number' => 658,
        ],
        'merchant_chargeback_negative_balance' => [
            'email_subject' => 'merchant_chargeback_negative_balance',
            'email_template' => 'merchant_negative_balance.negative_balance',
            'notification_template' => 'merchant_negative_balance.negative_balance',
            'action_number' => 659,
        ],
        'merchant_refund_negative_balance' => [
            'email_subject' => 'merchant_refund_negative_balance',
            'email_template' => 'merchant_negative_balance.negative_balance',
            'notification_template' => 'merchant_negative_balance.negative_balance',
            'action_number' => 660,
        ],
        'b2b_completed_sender' => [
            'email_subject' => 'b2b_completed_sender',
            'email_template' => 'b2b_payments.b2b_completed_sender',
            'sms_template' => 'b2b_payments.b2b_completed_sender',
            'notification_template' => 'b2b_payments.b2b_completed_sender',
            'action_number' => 661,
        ],
        'b2b_completed_receiver' => [
            'email_subject' => 'b2b_completed_receiver',
            'email_template' => 'b2b_payments.b2b_completed_receiver',
            'sms_template' => 'b2b_payments.b2b_completed_receiver',
            'notification_template' => 'b2b_payments.b2b_completed_receiver',
            'action_number' => 662,
        ],
        'b2b_rejected_sender' => [
            'email_subject' => 'b2b_rejected_sender',
            'email_template' => 'b2b_payments.b2b_rejected_sender',
            'sms_template' => 'b2b_payments.b2b_rejected_sender',
            'notification_template' => 'b2b_payments.b2b_rejected_sender',
            'action_number' => 663,
        ],
        'b2b_rejected_receiver' => [
            'email_subject' => 'b2b_rejected_receiver',
            'email_template' => 'b2b_payments.b2b_rejected_receiver',
            'sms_template' => 'b2b_payments.b2b_rejected_receiver',
            'notification_template' => 'b2b_payments.b2b_rejected_receiver',
            'action_number' => 664,
        ],
        'awaiting_refund_approved' => [
            'notification_template' => 'refund.awaiting_refund_approved',
            'action_number' => 665,
        ],
        'admin_refund_notification' => [
            'notification_template' => 'refund.notification',
            'action_number' => 601,
        ],
        'kyc_success' => [
            'email_subject' => 'kyc_success',
            'email_template' => 'kyc.successful',
            'notification_template' => 'kyc.success',
            'action_number' => 670,
        ],
        'new_user_bank_account' => [
            'email_subject' => 'new_user_bank_account',
            'email_template' => 'user_bank_account.new_user_bank_account',
            'action_number' => 256,
        ],
        'money_request_reject' => [
            'email_subject' => 'money_request_reject',
            'email_template' => 'money_request.reject',
            'notification_template' => 'requestmoney.reject',
            'sms_template' => 'request_money.rejected',
            'action_number' => '213'
        ],
        'b2c_success' => [
            'email_subject' => 'money_transfer_success',
            'email_template' => 'money_transfer.sender_success',
            'pdf_view' => 'pdf_blades.receipts.money_transfer_attacment',
            'pdf_file_name' => 'receipt',
            'action_number' => '667',
        ],
        'b2c_cashout_to_bank' => [
            'email_subject' => 'money_transfer_success',
            'email_template' => 'money_transfer.sender_success',
            'pdf_view' => 'pdf_blades.receipts.cashout_to_bank_attachment',
            'pdf_file_name' => 'receipt',
            'action_number' => '667',
        ],
        'user_status_change' => [
            'email_subject' => 'user_profile_status_change',
            'email_template' => 'user_status_change.user_status_change',
            'action_number' => 668,
        ],
        'inactive_user_alert_notification' => [
            'email_subject' => 'user_login_alert_is_disabled',
            'email_template' => 'inactive_user_login_alert.content',
            'sms_template' => 'inactive_user_login_alert.content',
            'action_number' => '669',
        ],
        'merchant_terminal_status_change_alert_emails' => [
            'email_subject' => 'terminal_number_status_change',
            'email_template' => 'merchant.merchant_terminal_status_change_alert',
            'action_number' => '670',
        ],
        'merchant_package_alert_mails' => [
            'email_subject' => 'terminal_package_alert_mails',
            'email_template' => 'merchant_package_alert.merchant_package_alert',
            'action_number' => '671',
        ],
    ];

    const actionArray =  [
        1 => 'Notify me of password changes',
        2 => 'Notify me of email exchanges',
        3 => 'Notify me of deposit requests',
        4 => 'Notify me of deposit results',
        5 => 'Notify me of withdraw requests',
        6 => 'Notify me of withdraw results',
        7 => 'Notify me of outgoing money transfer result',
        8 => 'Notify me of incoming money transfers',
        9 => 'Notify me of money transfer requests sent',
        10 => 'Notify me of the results of the money transfer request sent',
        11 => 'Notify me of incoming money transfer requests',
        12 => 'Notify me of support ticket updates',
        13 => 'Notify me of wallet refunds',
        14 => 'Notify me of payment information changes',
        15 => 'Notify me of new chat Message'
    ];

    public function addUserSettings($user_id) {
        foreach (self::actionArray as $key=>$value) {
            $this->create([
                "user_id" => $user_id,
                "is_sms_enable" => self::SMSENABLED,
                "is_email_enable" => self::EMAILENABLED,
                "action_name" => $key
            ]);
        }
    }

    public function addNewUserSetting($user_id, $action_name,$is_email_enable = 0,$is_sms_enable = 0){
        $this->create([
            "user_id" => $user_id,
            "is_sms_enable" => $is_sms_enable,
            "is_email_enable" => $is_email_enable,
            "action_name" => $action_name
        ]);
    }
    public function getUserSettingsById($user_id) {
        return $this->where('user_id', $user_id)->get();
    }

    public function getUserSettingsByIdAndAction($user_id, $action_name) {
        return $this->where('user_id', $user_id)->where('action_name', $action_name)->first();
    }

    public function updateUserSettings($id_list, $sms_enable_list, $email_enable_list,$user_id) {
        $i=0;
        foreach ($id_list as $id) {
            $getUserSettings = $this->getUserSettingsByIdAndAction($user_id,$id);
            if($getUserSettings){
                $this->where('action_name', $id)->where('user_id', $user_id)->update([
                    "is_sms_enable" => $sms_enable_list[$i],
                    "is_email_enable" => $email_enable_list[$i],
                ]);
            }else{
                $this->addNewUserSetting($user_id, $id,$email_enable_list[$i],$sms_enable_list[$i]);
            }

            $i++;
        }
    }

    public static function getUploadFileTypes($is_excel = false){
        $upload_file_types = self::UPLOAD_FILE_TYPES;
        if($is_excel){
            $upload_file_types = ['csv','xlsx' ,'xls'];
        }
        return $upload_file_types;
    }

    public static function checkFileTypeAndSize ($file, $extra=[], $is_excel = false) {
        $ext = $file->extension();
        $size = $file->getSize() / 1048576;

        if(!in_array($ext, self::getUploadFileTypes($is_excel)) || $size > self::UPLOAD_FILE_SIZE) {
            return false;
        }
        return true;
    }
}
