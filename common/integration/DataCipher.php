<?php


namespace common\integration;


use common\integration\Utility\Language;
use common\integration\Utility\Str;
class DataCipher
{


    private const IV_LENGTH = 16;
    private const SALT_LENGTH = 4;
    public const ALGO_SHA512 = 'sha512';

    public static function customEncryptionDecryption($value, $secret_key, $action, $isURL = 0, $iv = null, $salt = null, $is_allow_url_encoding = false)
    {
        if ($action == 'encrypt') {
            if (is_array($value)){
                $value = implode('|', $value);
            }
            $value = self::encrypt($value, $secret_key, $iv, $salt);

            if ($isURL) {
                $value = str_replace('/', '__', $value);

                if ($is_allow_url_encoding) {
                    $value = Str::urlencode($value);
                }
            }

            return $value;

        } elseif ($action == 'decrypt') {

            if ($isURL) {
                if ($is_allow_url_encoding && Str::isUrlEncoded($value)) {
                    $value = Str::urldecode($value);
                }

                $value = str_replace('__', '/', $value);
            }
            $result = self::decrypt($value, $secret_key);

            if (strpos($result, '|') !== false){
                $array = explode('|', $result);
                return $array;
            }
            return $result;
        }
        return $value;
    }

    public static function encrypt($data, $password, $iv = null, $salt = null)
    {
        if (empty($data)) {
            return $data;
        }
        if (empty($iv)){
            $iv = substr(sha1(mt_rand()), 0, self::IV_LENGTH);
        }

        $password = sha1($password);

        if(empty($salt)){
            $salt = substr(sha1(mt_rand()), 0, self::SALT_LENGTH);
        }


        $saltWithPassword = hash('sha256', $password . $salt);

        $encrypted = openssl_encrypt(
            "$data", 'aes-256-cbc', "$saltWithPassword", 0, $iv
        );
        $msg_encrypted_bundle = "$iv:$salt:$encrypted";
        return $msg_encrypted_bundle;
    }


    public static function decrypt($msg_encrypted_bundle, $password)
    {
        if (empty($msg_encrypted_bundle)) {
            return $msg_encrypted_bundle;
        }
        $password = sha1($password);

        $components = explode(':', $msg_encrypted_bundle);
        if (count($components) < 3) {
            return $msg_encrypted_bundle;
        }

        $iv = $components[0] ? Str::replace(' ', '' , mb_substr($components[0], 0, self::IV_LENGTH))  : '';
        $salt = $components[1] ? Str::replace(' ', '' , mb_substr($components[1], 0, self::SALT_LENGTH))  : '';
        $salt = hash('sha256', $password . $salt);
        $encrypted_msg = $components[2] ?? '';

        $decrypted_msg = openssl_decrypt(
            $encrypted_msg, 'aes-256-cbc', $salt, null, $iv
        );

        if ($decrypted_msg === false) {
            return $msg_encrypted_bundle;
        }

//        $msg = substr( $decrypted_msg, 41 );
        return $decrypted_msg;
    }

    public static function toBinary($val,$pad_string = '')
    {
        $l = strlen($val);
        $result = '';
        while ($l--) {
            $result = str_pad(decbin(ord($val[$l])), 8, "0", STR_PAD_LEFT) . $pad_string . $result;
        }
        return $result;
    }


    public static function hash($alg, $value)
    {
        return hash($alg, $value);
    }

    public static function hashMac($alg, $value, $secret_key, $binary = false)
    {
        return hash_hmac($alg, $value, $secret_key, $binary);
    }

   public static function validateWalletPaymentHashKey($hash_key, $input, $app_secret= '', $type = 0){ //type 1 = refund, type 2 = orderStatus

      $status_code = '';
      $status_message = '';

      if (!empty($app_secret)){
         $password = $app_secret;
      }else{
         $password = config('app.brand_secret_key');
      }

      $data = self::customEncryptionDecryption($hash_key, $password, 'decrypt', 1);


      if (is_array($data) && count($data) == count($input)) {

         if(isset($input['invoice_id'])){
            if(!in_array($input['invoice_id'], $data)) {
               $status_message = __('Invoice id mismatch with hash key');
            }
         }

         if(isset($input['total'])) {
            if (!in_array($input['total'], $data)) {
               $status_message = __('Total mismatch with hash key');
            }
         }

         if(isset($input['currency_code'])) {
            if (!in_array($input['currency_code'], $data)) {
               $status_message = __('Currency Code mismatch with hash key');
            }
         }

         if(isset($input['gsm_number'])) {
            if (!in_array($input['gsm_number'], $data)) {
               $status_message = __('Gsm Number mismatch with hash key');
            }
         }

         if(isset($input['merchant_key'])) {
            if (!in_array($input['merchant_key'], $data)) {
               $status_message = __('Merchant Key mismatch with hash key');
            }
         }

         if(isset($input['otp'])) {
            if (!in_array($input['otp'], $data)) {
               $status_message = __('otp mismatch with hash key');
            }
         }

         if(isset($input['ref'])) {
            if (!in_array($input['ref'], $data)) {
               $status_message = __('ref mismatch with hash key');
            }
         }

      } else {
         $status_message = 'Invalid hash key';
      }

      if (!empty($status_message)) {
         $status_code = 68;
      }

      $status_message = Language::isLocalize($status_message, [], true);

      return [$status_code, $status_message];

   }

    public static function hashInit(string $algo, int $flags = 0, string $key = "")
    {
        return hash_init($algo, $flags, $key);
    }

    public static function hashFinal($context, bool $binary = false): string
    {
        return hash_final($context, $binary);
    }

    public static function hashUpdate($context, string $data): bool
    {
        return hash_update($context, $data);
    }

}