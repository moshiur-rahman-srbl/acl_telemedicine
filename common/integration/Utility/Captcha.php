<?php

namespace common\integration\Utility;

use common\integration\GlobalFunction;
use Illuminate\Support\Facades\Storage;

class Captcha
{
    private const PERMITTED_CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    public const STRING_LENGTH = 4;
    private const IMAGE_WIDTH = 200;
    private const IMAGE_HEIGHT = 50;

    /**
     * @return void
     */
    public function captcha():void
    {
        $image = imagecreatetruecolor(self::IMAGE_WIDTH, self::IMAGE_HEIGHT);

        imageantialias($image, true);

        $colors = [];

        $red   = rand(125, 175);
        $green = rand(125, 175);
        $blue  = rand(125, 175);

        for ($i = 0; $i < 5; $i++) {
            $colors[] = imagecolorallocate($image, $red - 20 * $i, $green - 20 * $i, $blue - 20 * $i);
        }

        imagefill($image, 0, 0, $colors[0]);

        for ($i = 0; $i < 10; $i++) {
            imagesetthickness($image, rand(2, 10));
            $line_color = $colors[rand(1, 4)];
            imagerectangle($image, rand(-10, 190), rand(-10, 10), rand(-10, 190), rand(40, 60), $line_color);
        }

        $black      = imagecolorallocate($image, 0, 0, 0);
        $white      = imagecolorallocate($image, 255, 255, 255);
        $textcolors = [$black, $white];


        $fonts          = [Storage::path('fonts/acme/Acme-Regular.ttf'), Storage::path('fonts/ubuntu/Ubuntu-R.ttf'), Storage::path('fonts/merriweather/Merriweather-Italic.otf'), Storage::path('fonts/playfair-display/PlayfairDisplay-Italic.otf')];
        $string_length  = 4;
        $captcha_string = $this->generate_string($string_length);

        GlobalFunction::setBrandSession('login_captcha', $captcha_string);
       // \session()->put('login_captcha', $captcha_string);

        for ($i = 0; $i < $string_length; $i++) {
            $letter_space = 170 / $string_length;
            $initial      = 15;
            imagettftext($image, 24, rand(-15, 15), $initial + $i * $letter_space, rand(25, 45), $textcolors[rand(0, 1)], $fonts[array_rand($fonts)], $captcha_string[$i]);
        }

        header('Content-type: image/png');
        $savePath = Storage::path('fonts/captcha2.png');
        imagepng($image, $savePath);
        imagepng($image);
        readfile($savePath); //added this line for IE as without it captcha image was not showing
        imagedestroy($image);
    }

    /**
     * @return string
     */
    function generate_string()
    {
        $input_length  = strlen(self::PERMITTED_CHARS);
        $random_string = '';
        for ($i = 0; $i < self::STRING_LENGTH; $i++) {
            $random_character = self::PERMITTED_CHARS[mt_rand(0, $input_length - 1)];
            $random_string    .= $random_character;
        }
        return $random_string;
    }

}