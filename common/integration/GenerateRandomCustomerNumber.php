<?php

namespace common\integration;

use App\Models\RandomCustomerId;
use common\integration\Models\RandomCustomerIdSetting;
use common\integration\Utility\Exception;
use Illuminate\Support\Facades\DB;

class GenerateRandomCustomerNumber
{
    public $randomCustomerIdSetting;
    public $last_start_number;
    const DEFAULT_FIND = 1;

    public function manageRandomCustomerNumber()
    {
        try {

            $log_data['action'] ='PROCESS_RANDOM_CUSTOMER_NUMBER';

            $this->randomCustomerIdSetting = (new RandomCustomerIdSetting())->findById(self::DEFAULT_FIND);
            $log_data['first_started_id'] = $this->randomCustomerIdSetting->start_number;
            $randomCustomerIdCount = (new RandomCustomerId())->countAllId();

            if ($randomCustomerIdCount < $this->randomCustomerIdSetting->threshold_low_limit){
                DB::beginTransaction();
                $this->generateRandomCustomerNumber();

                //update setting by last id;
                $data = [
                    'start_number'  => $this->last_start_number
                ];
                (new RandomCustomerIdSetting())->updateData($data, self::DEFAULT_FIND);
                $log_data['last_started_number'] = $this->last_start_number;
                DB::commit();
            }
        }catch (\Throwable $th){
            DB::rollBack();
            $log_data['errors'] = Exception::fullMessage($th);
        }
        (new ManageLogging())->createLog($log_data);

    }

    private function generateRandomCustomerNumber()
    {
        try {
            $counter = 0;
            $startNumber = $this->randomCustomerIdSetting->start_number;
            $limit = $this->randomCustomerIdSetting->incremented_by;
            $currentId = $startNumber;
            $random_customer_ids = [];

            while ($counter < $limit) {
                $currentId = $startNumber + $counter;
                $random_customer_ids[] = $this->prepareId($currentId);
                $counter++;
            }

            if(!empty($random_customer_ids)){
                (new RandomCustomerId())->add($random_customer_ids);
            }

            // increment last start number by 1 with currentID
            $this->last_start_number = $currentId + 1;

        }catch (\Throwable $th){

            (new ManageLogging())->createLog([
                'action'    => 'RANDOM_CUSTOMER_NUMBER_EXCEPTION',
                'errors'    => Exception::fullMessage($th)
            ]);
        }

    }

    private function prepareId($random_customer_id)
    {
        return [
            'id'    => $random_customer_id
        ];
    }
}