<?php
/**
 * User: md Yeasin
 * Date: 8/21/2019
 * Time: 4:06 PM
 * Licence: MIT
 *
 * description:
 *  this sample date helper for build dynamic date formate
 */

namespace App\Utils;

use common\integration\ManipulateDate;
use Illuminate\Support\Carbon;

class Date
{
    //01/02/2012
    const DATE_CASE_1 = 1;

    //Monday, Ist January 2012, 09:30:56
    const DATE_CASE_2 = 2;

    //2012-02-20-09-30-56 AM/PM
    const DATE_CASE_3 = 3;

    //02:55 AM/PM
    const DATE_CASE_4 = 4;

    //Jun 05
    const DATE_CASE_5 = 5;

    /**
     * @param null $case
     * @param null $date
     * @return false|string
     */
    public static function format($case = null, $date = null){

        return ManipulateDate::format($case,$date);
        if(empty($date)){
            return "";
        }


        date_default_timezone_set("Europe/Istanbul");


        $currentDate = empty($date) ? time() : strtotime($date);
        switch($case){
            case 1:
                //01/02/2012
                return date('d/m/Y',   $currentDate);
                break;
            case 2:
                //Monday, Ist January 2012, 09:30:56
                return date("l, jS F Y, H:i:s", $currentDate);
                break;
            case 3:
                //2012-02-20-09-30-56 AM/PM
                return date("Y-m-d-H-i-s A", $currentDate);
                break;
            case 4:
                //02:55 AM/PM
                return date("h:i A", $currentDate);
                break;
            case 5:
                //Jun 05
                return date("F d", $currentDate);
                break;
            case 6:
                //Jun 05
                return date("d.m.Y - H:i", $currentDate);
                break;
            case 7:
                // 22.48|28.09.2020
                return date('H:i | d.m.Y', $currentDate);
                break;
            case 8:
                //14:55
                return date("H:i - d.m.Y", $currentDate);
                break;
            case 9:
                //2019-10-09
                return date("Y-m-d", $currentDate);
                break;
            case 10:
                // 2022 (Only Year)
                return date("Y", $currentDate);
                break;
            case 11:
                // January (Only Month Name)
                return date("F", $currentDate);
                break;
            case 12:
                //Jun 05
                return date("d.m.Y", $currentDate);
                break;
            default:
                //2012-02-20 09:30:56
                return date("Y-m-d H:i:s", $currentDate);
        }
    }

    public static function getLastMonth(){
        $start = new Carbon('first day of last month');
        $agoDate = $start->startOfMonth();
        $end = new Carbon('last day of last month');
        $nowDate = $end->endOfMonth();

        return [$agoDate,$nowDate];
    }
}
