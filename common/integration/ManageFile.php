<?php

namespace common\integration;

use common\integration\Utility\Arr;

class ManageFile
{
    public function fileWrite($file_dir, $file_name, $content)
    {
        $final_file_path = $file_dir . '/' . $file_name;
        if (!empty($content)) {
            if (!is_dir($file_dir)) {
                mkdir($file_dir);
                chmod($file_dir, 0777);
            }

            if (file_exists($final_file_path)) {
                unlink($final_file_path);
            }

            $file = fopen($final_file_path, "w+");
            fwrite($file, $content);
            chmod($final_file_path, 0777);

            fclose($file);
        }
    }

    public function otpWrite($content)
    {
        if (config('app.IS_OTP_FILE_WRITE_ENABLE')) {
            if (BrandConfiguration::enableOnlyDigitsForOtp()) {
                preg_match_all('!\d+!', $content, $matches);
                if (!empty($matches)) {
                    foreach ($matches as $match) {
                        if (!empty($match)) {
                            foreach ($match as $val) {
                                if (strlen($val) == 6) {
                                    $content = $val;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            $file_dir = public_path('files');
            $file_name = 'otp.txt';

            $this->fileWrite($file_dir, $file_name, $content);
        }
    }

    public static function checkFileTypeAndSize($file, $allowed_file_types, $allowed_file_size)
    {
        $ext = $file->extension();
        $size = $file->getSize() / 1048576;
        if (!Arr::isAMemberOf($ext, $allowed_file_types) || $size > $allowed_file_size) {
            return false;
        }
        return true;
    }
}
