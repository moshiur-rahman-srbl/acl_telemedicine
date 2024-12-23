<?php

namespace common\integration\Utility;

use App\Http\Controllers\Traits\SendEmailTrait;
use common\integration\InformationMasking;
use common\integration\ManageLogging;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Exception
{
    use SendEmailTrait;




    public static function fullMessage(\Throwable $throwable, $should_trace = false, $should_get_the_exception_code = false, $values_to_be_hidden = []):string
    {
        $full_message = $throwable->getMessage()." at line ".$throwable->getLine()." in ".$throwable->getFile();

        if($should_get_the_exception_code){
            $code = $throwable->getCode();
            $full_message .= " code: ". $code;
        }

        if($should_trace){
            $trace = $throwable->getTraceAsString();
            $full_message .= " trace: ".$trace;
        }

        if(!empty($values_to_be_hidden)){
            $full_message = InformationMasking::hideValues($full_message, $values_to_be_hidden);
        }

        return $full_message;

    }

    public static function log(\Throwable $throwable, $action = "", $should_trace = false, $values_to_be_hidden = [])
    {
        if($throwable instanceof \InvalidArgumentException ||
            $throwable instanceof HttpExceptionInterface ||
            $throwable instanceof AuthenticationException
        ){
            return;
        }
        $dir = substr(__DIR__,0,-14);
        $backtrace =  $throwable->getTrace();
        //$backtrace = str_replace([$dir],"", $backtrace);
       // $backtrace = preg_replace('^(.*vendor.*)\n^','',$backtrace);



        $log = [
            "action:" => !empty($action) ? $action : "APPLICATION_CUSTOM_EXCEPTION",
            "exception: " =>Exception::fullMessage($throwable, false, false, $values_to_be_hidden)
        ];

        if($should_trace){
            $log["backtrace"] = $backtrace;
        }

        if(!empty($values_to_be_hidden)){
            $log = InformationMasking::hideValues($log, $values_to_be_hidden);
        }

        (new ManageLogging())->createLog($log);
    }

    public function sendExceptionEmail($subject, $content,$emails=null)
    {
        $emails=$emails??config('constants.EMERGENCY_NOTIFICATION_EMAILS');
        $data['contents'] = $content;
        $subject = '('. config('app.env') . ') ' . $subject;
        return $this->sendEmail($data, $subject, config('app.SYSTEM_NO_REPLY_ADDRESS'), $emails, '', 'automation', 'en');
    }





    public function handle(\Throwable $exception)
    {
        $valuesToBeHidden = (new InformationMasking())->getConcealableInformation();

        if($exception instanceof \PDOException){
            Exception::log($exception, "PDO_EXCEPTION_LOG", true,$valuesToBeHidden);
            $logId = app()->has('log_id') ? app()->get('log_id'): "";
            $message = "PDO Error with Log Id ".$logId;
            $exception = new \Exception($message);
        }else{
            self::log($exception);
        }


        return $exception;
    }




}