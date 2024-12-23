<?php

namespace App\Http\Controllers\Traits;

use App\Models\Aml;
use App\Models\Customer;
use App\Models\TemporaryTransaction;
use App\Models\TransactionState;
use Auth;
use App\User;
use App\Models\Send;
use App\Models\Currency;
use App\Models\Receive;
use App\Models\Transaction;
use Carbon\Carbon;
use common\integration\Models\OutGoingEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Wallet;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMoneyMail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Tag\U;
use TCG\Voyager\Models\Page;
use Barryvdh\DomPDF\Facade as PDF;
use Excel;

trait TransactionTrait
{
    use SendEmailTrait, CommonLogTrait;
    private function sendMoneyTransactionUpdate(User $sender, User $receiver, $amount, $sendTableId, $sender_transaction_id,$receiver_transaction_id)
    {

        $currency = Currency::find($sender->currency_id);

        $send = Send::find($sendTableId);
        $send->to_id = $receiver->id;
        $send->save();

        $receive = Receive::find($send->receive_id);
        $receive->user_id = $receiver->id;
        $receive->send_id = $send->id;
        $receive->save();

        $receiveTransaction = Transaction::find($receiver_transaction_id);
        $receiveTransaction->user_id = $receive->user_id;
        $receiveTransaction->save();

        $send_transaction = Transaction::find($sender_transaction_id);
        $send_transaction->entity_name = $receiver->name;
        $send_transaction->save();

        $sender->RecentActivity()->save($send_transaction);

        return $send_transaction;
    }


    private function processSendMoneyUpdate(Transaction $senderTransaction,User $sender,User $receiver)
    {
        $currency = Currency::find($senderTransaction->currency_id);
        $sender_wallet = $sender->walletByCurrencyId($currency->id);

        $send = Send::find($senderTransaction->transactionable_id);
        $receive = Receive::find($send->receive_id);

        $receiver_wallet = $receiver->walletByCurrencyId($currency->id);

        $receive_transaction = Transaction::where('transactionable_type', 'App\Models\Receive')->where('user_id', $receiver->id)->where('transaction_state_id', 3)->where('money_flow', '+')->where('transactionable_id', $receive->id)->first();

        $receive->send_id = $send->id;
        $receive->transaction_state_id = TransactionState::COMPLETED;
        $receive->save();

        $send->transaction_state_id = TransactionState::COMPLETED;
        $send->save();

        $senderTransaction->transaction_state_id = TransactionState::COMPLETED;
        $senderTransaction->balance = (double)$sender_wallet->amount;
        $senderTransaction->save();

        $receive_transaction->transaction_state_id = TransactionState::COMPLETED;
        $receive_transaction->balance = (double)$receiver_wallet->amount + $receive_transaction->net;
        $receive_transaction->save();


        $receiver_wallet->amount = $receiver_wallet->amount + $receive_transaction->net;
        $receiver_wallet->save();

        $senderTransaction->sendMoneyNotification($sender, $receiver, $senderTransaction->net, 1, $currency->name);
        $receive_transaction->sendMoneyNotification($sender, $receiver, $receive_transaction->net, 2, $currency->name);

    }

    private function sendMoneyReject($temporary_transaction_id){

        $temporary_transaction = TemporaryTransaction::find($temporary_transaction_id);

        $sender_transaction = Transaction::find($temporary_transaction->sender_transaction_id);
        $receiver_transaction = Transaction::find($temporary_transaction->receiver_transaction_id);

        $send = Send::find($sender_transaction->transactionable_id);
        $send->transaction_state_id = TransactionState::REJECTED; // 2 = Rejected
        $send->save();

        $receive = Receive::find($receiver_transaction->transactionable_id);
        $receive->transaction_state_id = TransactionState::REJECTED; // 2 = Rejected
        $receive->save();

        $customer = Customer::find($send->user_id);

        $walletConditions = [
            'currency_id' => $sender_transaction->currency_id,
            'user_id' => $send->user_id
        ];

        $wallet = Wallet::where($walletConditions)->first();
        $wallet->amount = (double) $wallet->amount + (double) $sender_transaction->gross;
        $customerBalance = $wallet->amount;
        $wallet->save();

        $sender_transaction->transaction_state_id = 2; // 2 = Rejected
        $sender_transaction->balance = $customerBalance;
        $sender_transaction->updated_at = Carbon::now();
        $sender_transaction->save();

        $receiver_transaction->transaction_state_id = 2; // 2 = Rejected
        $receiver_transaction->balance = 0;
        $receiver_transaction->save();

        $data['name'] = $customer->name;
        $data['amount'] = $this->make_round($sender_transaction->gross);
        $data['receiver_email'] = $temporary_transaction->receiver_email;
        $data['currency_symbol'] = $sender_transaction->currency_symbol;
        $from = config('app.SYSTEM_NO_REPLY_ADDRESS');
        $template = "sendmoney_reject_notification.sendmoney_reject_notification";

        //out_going_email
        $this->setGNPriority(OutGoingEmail::PRIORITY_MEDIUM);
        $this->sendEmail($data, "sendmoney_reject_notification", $from, $customer->email,
            "",$template , $customer->language);

        $temporary_transaction->delete();

    }
}
