<?php

namespace common\integration\Traits;

use App\Models\CCPayment;
use App\Models\MerchantEmailReceiver;
use App\Models\SalesPFRecords;
use common\integration\ManageLogging;
use common\integration\Utility\Arr;
use common\integration\Utility\Exception;
use common\integration\Utility\Str;

trait MerchantEmailReceiverTrait
{
    private $merchantEmailReceiverObj = null;

    private function getEmailReceiverByMerchantId ($merchantId) {
        $this->merchantEmailReceiverObj = (new MerchantEmailReceiver())->findByMerchantId($merchantId);
    }

    private function getReceiverEmailAddress($emailReceiveType, $merchantObj,$notificationStatusType = '', $receiverEmailAsArray = true , $merchantEmailReceiver = null) {
        $receiverEmail = [];
        $extra = [];

        if(in_array($emailReceiveType, MerchantEmailReceiver::EMAIL_RECEIVE_TYPE) && !empty($merchantObj)) {

            if(!empty($merchantEmailReceiver)){
                $this->merchantEmailReceiverObj = $merchantEmailReceiver;
            }
            if(empty($this->merchantEmailReceiverObj) || (!empty($this->merchantEmailReceiverObj) && $this->merchantEmailReceiverObj->merchant_id != $merchantObj->id)) {
                $this->getEmailReceiverByMerchantId($merchantObj->id);
            }

            $merchantEmailReceiverObj = $this->merchantEmailReceiverObj;

            if ($receiverEmailAsArray) {

                if(!empty($merchantEmailReceiverObj)) {
                    $receiverEmail =  $this->manageWithdrawalSuccessfulEmail($notificationStatusType);
                    if (empty($receiverEmail)){
                        $receiverEmail = !empty($merchantEmailReceiverObj->$emailReceiveType) ? explode(',', $merchantEmailReceiverObj->$emailReceiveType) : $receiverEmail;

                    }
                } elseif(in_array($emailReceiveType, MerchantEmailReceiver::DEFAULT_USER_EMAIL)) {
                    $receiverEmail = [$merchantObj->authorized_person_email];
                }
    
                // if(empty($receiverEmail)) {
    
                //     if(in_array($emailReceiveType, MerchantEmailReceiver::DEFAULT_USER_EMAIL)) {
                //         $extra = $merchantObj->users->map->only('email', 'language', 'id', 'merchant_parent_user_id')->toArray();
    
                //         for($i=0, $len=sizeof($extra); $i<$len; $i++) {
                //             array_push($receiverEmail, $extra[$i]['email']);
                //             unset($extra[$i]['email']);
                //         }
                //     } else {
                //         $receiverEmail = [$merchantObj->authorized_person_email];
                //     }
                // }
            } else {

                if(!empty($merchantEmailReceiverObj)) {
                    $receiverEmail = $merchantEmailReceiverObj->$emailReceiveType == MerchantEmailReceiver::ACTIVE ? true : false;
                } else {
                    $receiverEmail = false;
                }
            }
        }
        
        return [$receiverEmail, $extra];
    }

    private function checkPaymentSource ($payment_source) {
        
        $source_array = [
            CCPayment::PAYMENT_SOURCE_PAID_BY_CC_3D_BRANDING,
            CCPayment::PAYMENT_SOURCE_PAID_BY_CC_2D_BRANDING,
            CCPayment::PAYMENT_SOURCE_WHITE_LABEL_3D,
            CCPayment::PAYMENT_SOURCE_WHITE_LABEL_2D,
            CCPayment::PAYMENT_SOURCE_REDIRECT_WHITE_LABEL_3D,
            CCPayment::PAYMENT_SOURCE_REDIRECT_WHITE_LABEL_2D,
            CCPayment::PAYMENT_SOURCE_PAY_BY_CARDTOKEN_3D,
            CCPayment::PAYMENT_SOURCE_PAY_BY_CARDTOKEN_2D,
            CCPayment::PAYMENT_SOURCE_PAY_BY_MARKETPLACE_3D,
            CCPayment::PAYMENT_SOURCE_PAY_BY_MARKETPLACE_2D,
            CCPayment::PAYMENT_SOURCE_PAY_BY_REDIRECT_DIRECTLY,
            CCPayment::PAYMENT_SOURCE_PAY_BY_WIX_3D,
            CCPayment::PAYMENT_SOURCE_PAY_BY_WIX_2D,
            CCPayment::PAYMENT_SOURCE_OXIVO_PAYMENT,
            CCPayment::PAYMENT_SOURCE_RECURRING_PAYMENT
        ];

        return in_array($payment_source, $source_array);

    }
    public function manageWithdrawalSuccessfulEmail($notificationStatusType){

        $receiver_emails = [];
        if ($notificationStatusType == MerchantEmailReceiver::NOTIFICATION_TYPE_WITHDRAWAL_APPROVE) {
             $receiver_emails = !empty($this->merchantEmailReceiverObj->{MerchantEmailReceiver::WITHDRAWAL_SUCCESSFUL_EMAIL}) ? explode(',', $this->merchantEmailReceiverObj->{MerchantEmailReceiver::WITHDRAWAL_SUCCESSFUL_EMAIL}) : [];
        }
        return $receiver_emails;

    }
	
	public function merchantMailReceiversUpdate($merchant_id, $current_email, $new_email): void
	{
		try{
			$prepare_data = [];
			$merchant_mail_receivers = (new MerchantEmailReceiver())->findByMerchantId($merchant_id);
			if($merchant_mail_receivers && $cols = Arr::keys($merchant_mail_receivers->getOriginal())){
				$cols = Arr::unsetByValue($cols, ['id', 'merchant_id', 'created_at', 'updated_at']);
				
				foreach ($cols as $col){
					$prepare_data[$col] = $merchant_mail_receivers->{$col};
					if(
						Str::isString($merchant_mail_receivers->{$col}) &&
						Str::contains($merchant_mail_receivers->{$col}, $current_email)
					){
						
						if($current_data = Arr::explode(',', $merchant_mail_receivers->{$col})){
							$prepare_data[$col] = Arr::implode(
								',',
								Arr::filter(
									Arr::map(function ($value) use ($current_email, $new_email){
										return $value === $current_email ? $new_email : $value;
									}, $current_data)
								)
							);
						}
						
					}
				}
				if($prepare_data){
					(new MerchantEmailReceiver())->saveData($prepare_data, $merchant_id);
				}
			}
		}catch (\Exception $e){
			
			$log_data['action'] = 'EXCEPTION_FOR_UPDATE_MERCHANT_EMAIL_PREPARE';
			$log_data['datas'] = [
				'merchant_id' => $merchant_id,
				'current_email' => $current_email,
				'new_email' => $new_email
			];
			$log_data['exception'] = Exception::fullMessage($e, true);
			(new ManageLogging())->createLog($log_data);
		}
		
	}
	
}