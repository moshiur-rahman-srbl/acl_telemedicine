<?php


namespace common\integration;


use App\Utils\Date;
use Carbon\Carbon;
use common\integration\Utility\Arr;
use common\integration\Utility\Str;

class ManipulateDate
{
    const FORMAT_DATE_Ymd = 'Ymd';
    const FORMAT_DATE_DMY = 'dmy';
    const CASE_TYPE_HIS = 18;  //H:i:s
    const CASE_TYPE_DMY_HI = 6; //d.m.Y - H:i
    const CASE_TYPE_DMY = 1; // D/M/Y
    const CASE_TYPE_YMD = 9; // yyyy-m-d
    const FORMAT_DATE_Y_m_d = 'Y-m-d';
    const FORMAT_DATE_d_m_Y = 'd-m-Y';

    const FORMAT_DATE_Y_m_d_SLASH = 'Y/m/d';
    const FORMAT_DATE_Y_m = 'Y-m';
    const FORMAT_DATE_Y_m_d_H_i_s = "Y-m-d H:i:s";
    const FORMAT_DATE_Y_m_d_H_i = "Y-m-d H:i";
    const DIFF_BY_DAYS = "DAYS";
    const DIFF_BY_MINUTES = "MINUTES";
    const FORMAT_DATE_H = "H";
    const FORMAT_DATE_d_m_Y_H_DOT = 'd.m.Y H';
    const FORMAT_DATE_d_m_Y_DOT = 'd.m.Y';

    public static function getSystemDateTime($format = "Y-m-d h:i:s", $days = 0):string
    {
        $dateObj = Carbon::now()->addDays($days);
        return $dateObj->format($format);
    }

    public static function isMidNight($midnight_start = "23:00", $midnight_end = "01:00"):bool
    {
        $midnightStartObj = Carbon::createFromTimeString($midnight_start);
        $midnightEndObj = Carbon::createFromTimeString($midnight_end)->addDay();
        return Carbon::now()->between($midnightStartObj, $midnightEndObj);
    }

    public static function toDateString($datetime, $to_string = true)
    {

        $response = '';
        if(!empty($to_string)){
            $response = Carbon::parse($datetime)->toDateString();
        }else{
            $response = Carbon::parse($datetime);
        }
        return $response;
    }

   public static function startOfTheDay($date = null, $format = "Y-m-d H:i:s")
   {
        if(empty($date)){
           $date = Carbon::now();
        }
        return Carbon::parse($date)->startOfDay()->format($format);
   }

    public static function endOfTheDay($date = null, $format = "Y-m-d H:i:s")
    {
        if(empty($date)){
           $date = Carbon::now();
        }
        return Carbon::parse($date)->endOfDay()->format($format);
    }

   public static function toNow()
   {
        return Carbon::now();
   }

   public static function differentInSeconds($start_time, $end_time)
   {
        $start_time = Carbon::parse($start_time);
        $end_time = Carbon::parse($end_time);
        return $start_time->diffInSeconds($end_time);
   }

   public static function isGreaterThanOrEqualToMonths ($start_month, $end_month)
   {
        $start_time = Carbon::parse($start_month);
        $end_time = Carbon::parse($end_month);
        return $start_time->greaterThanOrEqualTo($end_time);
   }

    public static function isGreaterThanDates ($start_date, $end_date)
    {
        $start_date = !self::isInstanceOfCarbon($start_date) ? Carbon::parse($start_date) : $start_date;
        $end_date = !self::isInstanceOfCarbon($end_date) ? Carbon::parse($end_date) : $end_date;
        return $start_date->greaterThan($end_date);
    }

   public static function isSameMonth ($start_month, $end_month)
   {
        $start_time = Carbon::parse($start_month);
        $end_time = Carbon::parse($end_month);
        return $start_time->isSameMonth($end_time);
   }

   public static function getYesterdayDate()
   {
        return Carbon::yesterday();
   }

   public static function getDateFormat($date, $format='Y/m/d', $timezone = null)
   {
       $return_date = Carbon::parse($date);

       if(!empty($timezone)) {
           $return_date->setTimezone($timezone);
       }

       return $return_date->format($format);
   }

   public static function checkDatesVlidationByDays($start_date, $end_date, $check_days = 1){
        return Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) < $check_days;
   }

   public static function checkTimeValidationbyTimes($current_time, $start_time, $end_time, $check_local_environment = false){

        if(!empty($check_local_environment)){
            return true;
        }

       $current_time = Carbon::parse($current_time);
       $start_time = Carbon::createFromTimeString($start_time);
       $end_time = Carbon::createFromTimeString($end_time);

       return $current_time->between($start_time, $end_time);

   }

       public static function format($case = null, $date = null, $is_localize = false)
       {

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
                if ($is_localize){
                    return Date::localizeDate($case, $currentDate, 'l, jS F Y, H:i:s');
                }else{
                    return date("l, jS F Y, H:i:s", $currentDate);
                }
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
                //Jun 05
                return date("H:i | ", $currentDate).__(date("F", $currentDate)).date(" d", $currentDate);
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
            case 13:
                return date("Y-m-d\TH:i:s.000\Z", $currentDate);
                break;
            case 14:
                return date("Y_m_d", $currentDate);
                break;
            case 15:
                // 01 (Only Month Number)
                return date("m", $currentDate);
                break;
			      case 16:
	            return date("m/d/Y", $currentDate);
	            break;
            case 17:
                // 17.10.2023 - 12:28:12
                return date("d.m.Y - H:i:s", $currentDate);
                break;
            case self::CASE_TYPE_HIS :
                return date("H:i:s", $currentDate);
                break;
            default:
                //2012-02-20 09:30:56
                return date("Y-m-d H:i:s", $currentDate);
        }

    }

    public static function listOfYears($fromYear = null, $yearsToAdd = 10)
    {
        $from = $fromYear ? Carbon::createFromFormat("Y", $fromYear) : Carbon::createFromFormat("Y", now()->year);
        $fromYear = $from->year;
        $to = $from->addYears($yearsToAdd)->year;
        return range($fromYear, $to);
    }

    public static function listOfMonths()
    {
        return [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }

    public static function compareBothDateAndTime ($needle, $haystack, $extra = []): bool
    {
        // separate needle date and time - who will be compared with
        $needle_date = self::getDateFormat($needle, $extra['date_format'] ?? 'Ymd');
        $needle_time = self::getDateFormat($needle, $extra['date_format'] ?? 'His');
        // separate haystack date and time - with whom to be compared
        $haystack_date = self::getDateFormat($haystack, $extra['date_format'] ?? 'Ymd');
        $haystack_time = self::getDateFormat($haystack, $extra['date_format'] ?? 'His');

        return ($needle_date <= $haystack_date) && ($needle_time <= $haystack_time);
    }

    public static function min($arr)
    {
        $values = array_values($arr);
        usort($values, function($a, $b) {
            $tmp_a = strtotime($a);
            $tmp_b = strtotime($b);

            return $tmp_a < $tmp_b ? -1: 1;
        });
        return Arr::first($values);
    }


    public static function max($arr)
    {
        $values = array_values($arr);
        usort($values, function($a, $b) {
            $tmp_a = strtotime($a);
            $tmp_b = strtotime($b);

            return $tmp_a < $tmp_b ? -1: 1;
        });

        return Arr::last($values);
    }

    public static function toIso8601ZuluString($date)
    {
        return Carbon::parse($date)->toIso8601ZuluString();
    }

    public static function getDaysDiffFromDate($date, $current_date = null, $is_absolute = true): int
    {
        return Carbon::parse($date)->diffInDays($current_date, $is_absolute);
    }
    public static function addSeconds(int $seconds, $time = null)
    {
        if ($time == null) {
            return now()->addSeconds($seconds);
        }
        return Carbon::create($time)->addSeconds($seconds);
    }

    /*
     * property should be day, month, year
     */
    public static function getCarbonDayMonthYear($date, $property = 'd'){
        return Carbon::parse($date)->format($property);
    }

    public static function createFormatDate($date, $create_format = 'd-m-Y', $format='Y-m-d'){
        return Carbon::createFromFormat($create_format, $date)->format($format);
    }

    public static function startOfTheMonth ($date = null, $format = "Y-m-d H:i:s")
    {
        if(empty($date)){
            $date = Carbon::now();
        }
        return Carbon::parse($date)->startOfMonth()->format($format);
    }

    public static function endOfTheMonth ($date = null, $format = "Y-m-d H:i:s")
    {
        if(empty($date)){
            $date = Carbon::now();
        }
        return Carbon::parse($date)->endOfMonth()->format($format);
    }

    public static function intYmdToDate($int_ymd, $sign = "_")
    {
        $len = strlen($int_ymd);
        for($i = 0; $i < $len; $i++){
            if($i == 3 || $i == 5){
                $int_ymd[$i] .= $sign;
            }
        }
        return $int_ymd;
    }

    public static function isInstanceOfCarbon($date_time): bool
    {
        return $date_time instanceof Carbon;
    }

    public static function parse(mixed $date_time): Carbon
    {
        return Carbon::parse($date_time);
    }

    public static function today(): Carbon
    {
        return Carbon::today();
    }

    public static function subDaysFromNow($days, $is_start_day = false): Carbon
    {
        $date = Carbon::now()->subDays($days);
        if($is_start_day){
            $date = $date->startOfDay();
        }
        return $date;
    }

    public static function isToday($date) : bool
    {
        $date = Carbon::parse($date);
        return $date->isToday();
    }

    public static function isSameDay($start_date , $end_date)
    {
        $start_date = Carbon::parse($start_date);
        $end_time = Carbon::parse($end_date);
        return $start_date->isSameDay($end_time);
    }

    public static function subMonthsFromNow($month = 1, $format = null)
    {
        $get_month = Carbon::now()->subMonths($month);
        if ($format){
            $get_month = $get_month->format($format);
        }
        return $get_month;
    }

    public static function createFromFormat($date, $format = "d/m/Y"){
        return Carbon::createFromFormat($format, $date);
    }

	public static function createDateByDayMonthYear($day, $month, $year, $format = "d/m/Y"){

		return Carbon::createFromDate($year, $month, $day)->format($format);
	}


    public static function formatValidDateRange($date_range): array
    {
        $dateRange = $fromDate = $toDate = '';
        $date_range = Str::replace(' ', '', $date_range);

        if (!empty($date_range)) {
            $date = Arr::explode('-', $date_range);
            $fromDate = self::modifyDateFormat($date[0] ?? '');
            $toDate = self::modifyDateFormat($date[1] ?? '');

            if (!empty($fromDate) && !empty($toDate)) {
                $dateRange = $fromDate . ' - ' . $toDate;
            }
        }

        return array($dateRange, $fromDate, $toDate);
    }

    /*
     * example: convert "20/10/20" to "2020/10/20",
     * convert "1/10/20" to "2001/10/20" etc.
     */
    public static function modifyDateFormat($date): string
    {
        $result = '';

        if (!empty($date)) {
            $date = Str::replace(['/', '.'], ' ', $date);
            $date_array = Arr::explode(' ', $date);

            if (Arr::count($date_array) == 3) {
                $date_len = Str::len($date_array[0]);

                if ($date_len == 1 || $date_len == 2) {
                    $new_date = \DateTime::createFromFormat('y', $date_array[0]);
                    $date_array[0] = $new_date->format('Y');
                    $result = Arr::implode('/', $date_array);
                } elseif ($date_len == 4) {
                    $result = Arr::implode('/', $date_array);
                }
            }
        }

        return $result;
    }

	public static function lessThenToday($date){
		return Carbon::parse($date)->lessThan(Self::today());
	}
	public static function addDays($date, $days = 1){
		return Carbon::parse($date)->addDays($days);
	}

    public static function isDateLessThenYear($date)
    {
       return Carbon::parse($date)->diffInYears();
    }

    public static function addMonthsToDate($date, $month_number = 1)
    {
        return Carbon::parse($date)->addMonths($month_number);
    }

    public static function getMonthDaysDiffFromDate($date)
    {
        // Define Date
        $start_date = Carbon::parse(self::today());
        $end_date = Carbon::parse($date);
        $diffInMonths = 0;
        $diffInDays = 0;
        if(self::greaterThanOrEqualTo($date,$start_date)){
        // Calculate the difference in years, months, and days
        $diff = $start_date->diff($end_date);

        // Calculate the total months and remaining days
        $diffInMonths = $diff->y * 12 + $diff->m;
        $diffInDays = $diff->d;
        }

        return [
            'months' => $diffInMonths,
            'days' => $diffInDays,
        ];
    }

    public static function checkDiffBetweenTwoDates($start_date, $end_date, $diff_by = self::DIFF_BY_DAYS)
    {
        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);

        if($diff_by == self::DIFF_BY_MINUTES) {
            return $start_date->diffInMinutes($end_date);
        }

        return $start_date->diffInDays($end_date, false);
    }

    public static function strToTime($time){
        return strtotime($time);
    }
    public function diffInMinutes($from_date_time){
        $now = Carbon::now();
        $from_date_time = Carbon::parse($from_date_time);
        return $now->diffInMinutes($from_date_time);
    }

    public static function convertSecondsToTime($seconds, $format = 'i:s')
    {
        return gmdate($format, $seconds);
    }

    public static function addHour($date, $hour,  $format = "Y-m-d h:i:s")
    {
        if(empty($date)){
            $date = Carbon::now();
        }
        return Carbon::parse($date)->addHours($hour)->format($format);
    }

    public static function unixToDateTimeFormat($unixTimeStamp, $format = "Y-m-d h:i:s")
    {
        return date($format, $unixTimeStamp);
    }

    public static function checkValidDateFormat(int $month, int $day, int $year): bool{
        $is_valid = false;
        if(checkDate($month, $day, $year)){
            $is_valid = true;
        }
        return $is_valid;
    }

    public static function microTime(bool $as_float = false): string|float
    {
        return microtime($as_float);
    }

    public static function lessThenOrEqualTo($from_date, $to_date){
        return Carbon::parse($from_date)->lessThanOrEqualTo($to_date);
    }

    public static function greaterThanOrEqualTo($from_date, $to_date){
        return Carbon::parse($from_date)->greaterThanOrEqualTo($to_date);
    }

    public static function copy($date){
        return Carbon::parse($date)->copy();
    }

    public static function subMinutes($minutes = 10, $format = self::FORMAT_DATE_Y_m_d_H_i_s){
        return Carbon::now()->subMinutes($minutes)->format($format);
    }

    public static function subSeconds($seconds = 10){
        return Carbon::now()->subSeconds($seconds);
    }

    public static function formattedPeriod($endtime, $starttime)
    {
        $duration = $endtime - $starttime;

        $hours = (int)($duration / 3600);
        $minutes = (int)(($duration / 60) % 60);
        $seconds = (int)($duration % 60);
        $milliseconds = round(($duration - floor($duration)) * 1000);

        $formatted = sprintf("%02d:%02d:%02d.%03d", $hours, $minutes, $seconds, $milliseconds);

        return $formatted;
    }

    public static function addMinutes($minutes = 0, $date_time = null)
    {
        if(empty($date_time)) {
            $date_time = Carbon::now();
        }
        $return_date_time = $date_time->addMinutes($minutes);

        return $return_date_time;
    }

    public static function isCurrentWeek($date) : bool
    {
        $date = Carbon::parse($date);
        return $date->isCurrentWeek();
    }

    public static function toTimeString($date = null)
    {
        if(empty($date)) {
            $date = Carbon::now();
        } else {
            $date = (new Carbon($date));
        }

        return $date->toTimeString();
    }

    public static function subWeekFromDate($date = null, $week = 7 ){

        if(empty($date)) {
            $date = Carbon::now();
        }elseif( ! $date instanceof Carbon ){
            $date = Carbon::parse($date);
        }

        return $date->subWeeks($week);

    }
}