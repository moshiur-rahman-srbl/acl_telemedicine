<?php


use common\integration\DataCipher;
use common\integration\Utility\File;

if (!function_exists('array_keys_exists')) {
    /**
     * Easily check if multiple array keys exist
     *
     * @param  array  $keys
     * @param  array  $arr
     * @return boolean
     */
    function array_keys_exists(array $keys, array $arr) {
        return !array_diff_key(array_flip($keys), $arr);
    }
}

if (!function_exists('setting')) {
    /**
     * Get / set the specified setting value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function setting($key = null, $default = null)
    {
        $setting = new \App\Models\Setting();
        return $setting->setting($key, $default);

    }
}

if (!function_exists('secure_file_link')) {
    /**
     *
     * If a file storage path is passed, it will encode it and return it.
     *
     * @param  string  $path
     * @param  string  $disk
     * @return string
     */
    function secure_file_link($path, $disk = 'public')
    {
        if (!\common\integration\Utility\Url::isValid($path)){
            $file_path = File::getFullStoragePath($path, $disk);
        }else{
            $file_path = $path;
        }
        $encrypt_link = DataCipher::customEncryptionDecryption($file_path, config('app.brand_secret_key'), 'encrypt', 1);
        return route('generated_link',$encrypt_link);
    }
}
