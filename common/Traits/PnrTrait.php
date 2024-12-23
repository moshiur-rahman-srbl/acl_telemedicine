<?php
namespace App\Http\Controllers\Traits;

use App\Models\Deposit;

trait PnrTrait{

    public function generatePNR($length)
    {
        $key = '';
        $keys = array_merge(range(0, 9));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }
        $isExist = $this->pnrCodeIsExist($key);
        if ($isExist) {
            $key = $this->generatePNR($length);
        }
        return $key;
    }

    private function pnrCodeIsExist($key)
    {
        $result = false;
        $depositObj = new Deposit();
        $deposit = $depositObj->getDepositByPnr($key);
        if ($deposit != null) {
            $result = true;
        }
        return $result;
    }
}
