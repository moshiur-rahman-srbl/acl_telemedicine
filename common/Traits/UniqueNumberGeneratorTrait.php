<?php
namespace App\Http\Controllers\Traits;

use App\Models\Merchant;

trait UniqueNumberGeneratorTrait{


    public function generate_unique_payment_id($data_id,$currency,$payment_type,
          $merchantObj=null,$user_id=null,$type="merchantSec"){

        $day_month_digits = date('d').date('m').date('y');

        if(empty($merchantObj)){
            $merchant = new Merchant();
            $merchantObj = $merchant->getMerchantByUserId($user_id);
        }

        if($type == "merchantSec"){
            $user_id = str_pad($merchantObj->id,5,0,STR_PAD_LEFT);
        }else{
            $user_id = str_pad($user_id,5,0,STR_PAD_LEFT);
        }

        //convert PK to equivallent string
        $data_id  =  $this->getRandomString()."-".$this->getUniqueURLById($data_id);
   

        $unique_payment_id = $data_id."-".$currency.$payment_type
            ."-".$user_id."-".$day_month_digits;

        return $unique_payment_id;
    }
    
   private function getRandomString($len = 5){
        
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
        return substr(str_shuffle($permitted_chars), 0, $len);
    }

    
    private function getUniqueURLById($in, $to_num = false, $pad_up = false){
        
        $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $base = strlen($index);
        
        if ($to_num) {
            // Digital number <<-- alphabet letter code
            $in = strrev($in);
            $out = 0;
            $len = strlen($in) - 1;
            for ($t = 0; $t <= $len; $t++) {
                $bcpow = bcpow($base, $len - $t);
                $out = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
            }
            
            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $out -= pow($base, $pad_up);
                }
            }
        } else {
            // Digital number -->> alphabet letter code
            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $in += pow($base, $pad_up);
                }
            }
            
            $out = "";
            for ($t = floor(log10($in) / log10($base)); $t >= 0; $t--) {
                $a = floor($in / bcpow($base, $t));
                $out = $out . substr($index, $a, 1);
                $in = $in - ($a * bcpow($base, $t));
            }
            $out = strrev($out); // reverse
        }
        
        return $out;
        
    }
}
