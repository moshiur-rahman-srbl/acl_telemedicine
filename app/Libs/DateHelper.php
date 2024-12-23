<?php
namespace App\Libs;
/**
 * Created by PhpStorm.
 * User: MD Eyasin
 * Date: 8/10/2019
 * Time: 10:05 AM
 */
class DateHelper
{
    public static function format($case = null, $date = null){
        $currentDate = empty($date) ? time() : strtotime($date);
        switch($case){
            case 1:
                //01/02/2012
                return date('B d/m/Y',   $currentDate);
                break;
            case 2:
                //Monday, Ist January 2012, 09:30:56
                return date("l, jS F Y, H:i:s", $currentDate);
                break;
            case 3:
                //2012-02-20-09-30-56
                return date("Y-m-d-H-i-s", $currentDate);
                break;
            case 4:
                return date("h:i A", $currentDate);
                break;
            case 5:
                return date("F d", $currentDate);
                break;
            default:
                //2012-02-20 09:30:56
                return date("Y-m-d H:i:s", $currentDate);
        }
    }
}