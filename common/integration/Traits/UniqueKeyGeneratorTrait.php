<?php


namespace common\integration\Traits;


use Exception;

trait UniqueKeyGeneratorTrait
{
    public function generateUniqueKey($unique_string = ''){
        $token = "";
        try {

            $token = md5($unique_string . time() . $this->getSecretKey());
        } catch (\Throwable $ex) {

        }

        return $token;
    }

    private function getSecretKey($length = 32) {

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public function generateUniqueInteger($length = 10, int $postfix = null)
    {
        $min = 1 . str_repeat(0, $length - 1);
        $max = str_repeat(9, $length);
        $unique_integer = mt_rand($min, $max);
        if (!is_null($postfix)) {
            $postfix_length = strlen($postfix);
            $unique_integer = substr_replace($unique_integer, $postfix, - $postfix_length);
        }
        return (int)$unique_integer;
    }

    /**
     * @return string|void
     * @throws Exception
     */

    public static function guidv4()
    {
        $data = random_bytes(16);
        assert(strlen($data) == 16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }


}