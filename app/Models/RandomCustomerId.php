<?php


namespace App\Models;

use common\integration\BrandConfiguration;
use common\integration\ManageLogging;
use common\integration\Models\User;
use common\integration\Utility\Exception;
use common\integration\Override\Model\CustomBaseModel as Model;

class RandomCustomerId extends Model
{
    protected $table = 'random_customer_ids';
    protected $guarded = [];
    public $timestamps = false;

    public function getRandomCustomerId($user_type)
    {
        $ran_row = RandomCustomerId::inRandomOrder()->first();
        if (BrandConfiguration::isAllowRandomCustomerIdWithoutPrefix()) {
            return (new User())->generateRandomCustomerId();    // without adding any prefix
        }

        if ($user_type == Profile::CUSTOMER) {
            $cust_num = '5' . $ran_row->id;
        } elseif ($user_type == Profile::MERCHANT) {
            $cust_num = '6' . $ran_row->id;
        } else {
            $cust_num = $ran_row->id;
        }
        return $cust_num;
    }

    public function deleteRandomCustomerId($id)
    {
        $del_id = substr($id, 1);
        if (BrandConfiguration::isAllowRandomCustomerIdWithoutPrefix()) {
            $del_id = $id;
        }

        return $this->where("id", $del_id)->delete();
    }

    public function add(array $id_list){
        return $this->query()->insert($id_list);
    }

    public function countAllId(){
        return $this->query()->count();
    }

    public function getLastIDValue(){
//        return $this->query()->first('id');
        return $this->query()->orderBy('id', 'DESC')->first();
    }

    public function getAndUpdateSequentialCustomerId()
    {
        $log_data['ACTION'] = "RANDOM_CUSTOMER_ID_GENERATE";
        try {
            $customerObj = self::query()
                ->lockForUpdate()
                ->first();
            $log_data['PREVIOUS_RANDOM_CUSTOMER_ID'] = $customerObj->id ?? 0;
            $customer_id = $customerObj->id;
            $customerObj->increment('id');
            $customerObj->save();
            $log_data['NEW_RANDOM_CUSTOMER_ID_GENERATE'] = $customerObj->id ?? 0;
            return $customer_id;
        } catch (\Throwable $e) {
            $log_data['ERROR'] = Exception::fullMessage($e);
        }

        (new ManageLogging())->createLog($log_data);
    }


}
