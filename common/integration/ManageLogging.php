<?php


namespace common\integration;

use App\Http\Controllers\Traits\SendEmailTrait;
use App\Models\Statistics;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Backend\BackendAdmin;
use common\integration\Traits\HttpServiceInfoTrait;
use common\integration\Traits\UniqueKeyGeneratorTrait;
use common\integration\Utility\Arr;
use common\integration\Utility\Exception;
use common\integration\Utility\Number;
use common\integration\Utility\Sftp\HttpCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ManageLogging
{
    use UniqueKeyGeneratorTrait, HttpServiceInfoTrait, SendEmailTrait;
    private $resource_path = null;
    private $unique_string = null;

    public static $should_log;
    private static $logManager;
    public static $hide_keys = [];
    public static $action = "";
    const CUSTOM_LOG_FILE_NAME_KEY = 'CUSTOM_LOG_FILE_NAME_KEY';
	
	const PANEL_NAME_LOG_KEY = 'panel';
	const PANEL_NAME_LOG_POST_FIX = '_PANEL_LOG';

    public function checkLog(){
        if (!self::isLoggingEnable()){
            $this->makeApplicationDown();
            return false;
        }
        if (!self::createLog(['action' => 'LOG_WRITING_TEST'])){
            $this->makeApplicationDown();
            return false;
        }

        // write log in a specific file not daly log
        $this->createCustomLog($this->generateUniqueKey(rand(0,999)));  //generate unique string
        // read the log for unique string
        return $this->getLogDataAndProcess();
    }

    private function createCustomLog($unique_string){
        try {
            $resource_path = config('filesystems.disks.public.root');
            $folder_name = 'PaymentLogs';
            $file_name = "log_" . $unique_string . ".log";
            $resource_path .= "/" . $folder_name . "/" . $file_name;
            config(['logging.channels.payment.path' => $resource_path]);
            $data = ['unique_string' => $unique_string];
            $json_data = GlobalFunction::safe_json_encode($data);
            Log::channel('payment')->info($json_data);
            $this->resource_path = $resource_path;
            $this->unique_string = $unique_string;
        } catch (\Throwable $ex) {
            //dd($ex->getMessage());
        }

    }

    public static function iAmHere($action = "I_AM_HERE", $useCounter = true, $data = [])
    {
        if(empty(self::$logManager)){
            self::$logManager = new ManageLogging();
        }
        $logManager = self::$logManager;
        if($logManager instanceof  ManageLogging) {
            $actionCounter = "$action"."counter";
            $counter = $logManager->$actionCounter ?? 1;
            $now = microtime(true);
            if( ! isset($logManager->startTime)){
                $logManager->startTime = $now;
            }else{
                if(isset($logManager->lastTime)){
                    $timeElapsedFromLast = ManipulateDate::formattedPeriod($now, $logManager->lastTime);
                }
                $logManager->lastTime = $now;
                $timeElapsedFromStart = ManipulateDate::formattedPeriod($now, $logManager->startTime);
            }
        }
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $newAction = $action;
        if($useCounter){
            $newAction = $action."_".$counter;
        }
        (new ManageLogging())->createLog([
            "action" => $newAction,
            "now" =>  Carbon::now()->toDateTimeString("m"),
            "time_elapsed_from_start" => $timeElapsedFromStart ?? "",
            "time_elapsed_from_last" => $timeElapsedFromLast ?? "",
            "data" => $data,
            "backtrace" => $backtrace
        ]);
        $logManager->$actionCounter = $counter + 1;
    }


    private function getLogDataAndProcess(){
        $logFile = is_file($this->resource_path) ? file($this->resource_path) : null;

        $result = false;

        if(!empty($logFile)){
            foreach ($logFile as  $line) {
                /*
                 If unique string found on log then remove it
                 If not found then keep it and update Statistics table by 0
                 */
                if (preg_match("/" . preg_quote($this->unique_string , "/") . "/", $line)) {
                    // delete log file
                    if(File::delete($this->resource_path)){
                        $result = true;
                        break;
                    }
                }
            }
        }

        if (!$result){
            $this->makeApplicationDown();
        }

        return $result;
    }

    public function makeApplicationDown(){
        $statistics = new Statistics;
        $statistics->updateById(1, ['is_application_online' => 0]);
	    
	    (new ManageLogging())->createLog([
		    'action' => 'APPLICATION_DOWN',
		    'message' => 'APP SERVER DOWN TRIGGERED BY LOG WRITE ISSUE',
	    ]);
		
	    // (new GrayLog())->sendApplicationDownOrUpEmail(
		//     'APPLICATION IS GOING OFF. APPLICATION LOG NOT ABLE TO WRITEABLE',
		//     'application is going shut down',
		//     'APPLICATION_DOWN_FOR_LOG_NOT_WRITEABLE',
	    // );
		
    }

    public static function isLoggingEnable(){
        return isset(config('logging.channels')[config('logging.default')]);

    }

    public function createLog($data, $merge_common_log = true, $log_type = 'info'){

        // Remove This custom keys all over the applications logs
        $panel = config('constants.defines.PANEL');
        if($panel != BrandConfiguration::PANEL_CCPAYMENT){
            $data = InformationMasking::unsetKeys($data, false, [
                'otp', 'OTP', 'password', 'Password', 'question_one', 'answer_one', 'user_password'
            ]);
        }

        $status = true;
        if ($merge_common_log){
            self::getCommonLogData($data);
        }

		if(BrandConfiguration::isAllowGrayLog()){
			
			$data = Arr::merge($data, [
				self::PANEL_NAME_LOG_KEY => self::getPanelLogString($panel),
			]);
			
		}
		
		
        try {
            $json_data = GlobalFunction::safe_json_encode($data);
            Log::{$log_type}($json_data);
        } catch (\Throwable $e) {
            $status = false;
        }
        return $status;

    }

    public function getExecutionTime($start, $type = 'ms'): string
    {
        $difference = microtime(true) - $start;
        $execTime = Number::round($difference * 1000, 2) . $type;

        if ($type == 's') {
            $execTime = Number::round($difference, 2) . $type;
        }

        return $execTime;
    }


    public function getCommonLogData(&$logData){

        $logData['date_time'] = Carbon::now()->toDateTimeString();
        $logData['auth_user_ip'] = self::getClientIpAddress();
        if (BrandConfiguration::call([Mix::class, 'isShowServerPort'])) {
            $logData['server_port'] = self::getServerPort();
        }
        $logData['auth_user_agent'] = self::getUserAgentInfo();
        if (app()->has('auth') && auth()->user()) {
            $logData['auth_email'] = auth()->user()->email;
            $logData['auth_id'] = auth()->user()->id;
        }
        $logData = GlobalMerchant::getSwitchedMerchantLogData($logData);
        $logData["log_id"] = app()->has('log_id') ? app()->get('log_id') : "";

    }

    public function createBankResponseLog($action, $request)
    {

        $logData['action'] = $action;
        $logData['url_previous'] = url()->previous();
        $logData['current_url'] = url()->current();
        $logData['response'] = InformationMasking::unsetKeys($request->all(),true);
        $this->createLog($logData);

    }

    public function createExceptionLog($action,\Throwable $throwable, $data)
    {
        $exception_message = $throwable->getMessage().' in '.$throwable->getFile().' at '.$throwable->getLine();
        $tmp = [
            "action" => $action,
            "exception" => $exception_message
        ];

        $temp = array_merge($tmp, $data);

        self::createLog($tmp);
    }

    public function createCurlErrorLog($action, $url, $curl, $request = null,$response = null, $headers = null, $should_log_request = false)
    {
        try {
            $action = $action . "_CURL_LOG";
            $curl_error_no = curl_errno($curl);
            $curl_error = curl_error($curl);
            $curl_response_http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if (empty($response) || !empty($curl_error) || HttpCode::isError($curl_response_http_code) || (!empty($should_log_request) || !empty(self::shouldLog()))) {
                if(!empty($hide_keys = self::hideKeys())){
                    $request = InformationMasking::hideValues($request,$hide_keys);
                    $url = InformationMasking::hideValues($url,$hide_keys);
                    $headers = InformationMasking::hideValues($headers,$hide_keys);
                }

                $log["action"] = $action;
                $log["url"] = $url;

                if(!empty($should_log_request) || !empty(self::shouldLog())){
                    $log["request"] = $request;
                    $log['headers'] = $headers;
                }
                $log["response"] = $response;
                $log["curl_error_no"] = $curl_error_no;
                $log["curl_error"] = $curl_error;
                $log["curl_response_http_log"] = $curl_response_http_code;
                $this->createLog($log);
            }
        }catch (\Throwable $throwable){
            Exception::log($throwable, $action);
        }

    }

    public static function uniqueLogId()
    {
        $date=date_create();
        return uniqid('l')."-r".mt_rand(10000,99999)."-d".date_format($date,"Ymd")."-t".date_format($date,"His");
    }


    public static function queryLog($file = "query")
    {
        gc_collect_cycles();
        if(file_exists(storage_path("/logs/$file.log")))
            @unlink(storage_path("/logs/$file.log"));
        $query_count=0;
        DB::listen(function($query) use (&$query_count,$file){
            $sql = self::formatQuery($query->bindings,$query->sql);
            File::append(
                storage_path("/logs/$file.log"),
                PHP_EOL. ++$query_count.'. '.$sql . PHP_EOL . 'Execution Time = '.  $query->time . PHP_EOL.'Date Time = '.  ManipulateDate::toNow() . PHP_EOL
            );

            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            foreach ($backtrace as $trace) {
                if(array_key_exists('file',$trace) && array_key_exists('line',$trace)){
                    if( strpos($trace['file'],'vendor')==false
                        && strpos($trace['file'],'Middleware')==false
                        && strpos($trace['file'],'public')==false
                        && strpos($trace['file'],'server')==false){
                        File::append(
                            storage_path("/logs/$file.log"),
                            $trace['file'] .' '.$trace['line'].PHP_EOL
                        );
                    }
                }
            }
        });
    }

    public static function formatQuery($bindings, $naked_sql){

        $parted = explode('?', $naked_sql);
        if (count($bindings) > 0) {
            $final = '';
            foreach ($parted as $key => $part) {

                if (next($parted)) {
                    $value = $bindings[$key];
                    if (is_string($value)) {
                        $final .= $part . "'" . $value . "'";
                    }elseif (is_object($value)){
                        $final .= $part . current( (Array)$value );
                    } else {

                        $final .= $part . $value;
                    }
                } else {
                    if (isset($bindings[$key])) {
                        $value = $bindings[$key];
                        if (is_string($value)) {
                            $final .= $part . "'" . $value . "'";
                        }elseif (is_object($value)){
                            $final .= $part . current( (Array)$value );
                        } else {

                            $final .= $part . $value;
                        }
                    } else {
                        $final .= $part;
                    }

                }

            }
            $sql = $final . ';';
        }else{
            $sql = $naked_sql;
        }
        return $sql;
    }

    /*
     * @conditions : where conditional key value are present
     *
     */

    public static function queryDebugLog($applicable, $file)
    {
        $test = array(
            "invoice_ids" => array(
                '173348842770471'
            ),
            "merchant_keys" => array(
                '$2y$10$w/ODdbTmfubcbUCUq/ia3OoJFMUmkM1UVNBiIQIuLfUlPmaLUT1he'
            ),
            "sale_ids" => array(
                '00000'
            )
        );

        foreach ($applicable as $k => $v){
            if(in_array($v, $test["$k"."s"])){
                self::queryLog($file);
                break;
            }
        }
    }

    public function createTestLog($data, $allowed_app_environment_list)
    {
        if(!empty($allowed_app_environment_list)){
            $app_environment = \config('constants.APP_ENVIRONMENT');
            if( in_array($app_environment, $allowed_app_environment_list)){
                $this->createLog($data);
            }
        }
    }

    public static function mergeQueriesAndBindings(array $query_logs_data = []): array
    {
        $final_queries = [];
        try {
            foreach ($query_logs_data as $query_log) {
                $query = $query_log['query'];
                $bindings = array_map(function ($item) {
                    if ($item instanceof Carbon || $item instanceof CarbonImmutable) {
                        $item = $item->toDateTimeString();
                    }
                    if ($item === (string) $item) {
                        $item = '\'' . $item . '\'';
                    }
                    return $item;
                }, $query_log['bindings']);

                $final_queries[] = (string) Str::of($query)->replaceArray('?', $bindings);
            }
        } catch (\Throwable $throwable) {
            (new ManageLogging())->createLog([
                'action' => 'MERGE_QUERY_LOGS_WITH_BINDINGS',
                'line' => $throwable->getLine(),
                'file' => $throwable->getFile(),
                'exception' => $throwable->getMessage(),
            ]);
        }
        return $final_queries;
    }

    public static function shouldLog($should_log = null)
    {
        if(!is_null($should_log)){
            return self::$should_log = $should_log;
        }else {
            return self::$should_log;
        }
    }

    public static function hideKeys($hide_keys = null)
    {
        if(!empty($hide_keys)){
            self::$hide_keys  = array_merge(self::$hide_keys, (is_array($hide_keys) ? $hide_keys : [$hide_keys]));
        }else{
            $ret = self::$hide_keys;
            self::$hide_keys = [];
            return $ret;
        }

    }

    public static function setDynamicLogFile($logData, $fileName = 'custom-log-file'){

        Log::build([
            'driver' => 'single',
            'path' => storage_path("logs/$fileName.log"),
            'level' => 'debug',
        ])->info('DEBUG:',$logData);

    }

    public static function createDebuggingLog($logData){
        $fileName = 'custom-log-file';
        $appInstance = app(self::CUSTOM_LOG_FILE_NAME_KEY);
        if (!empty($appInstance)) {
            $fileName = $appInstance;
        }
        self::setDynamicLogFile($logData, $fileName);
    }

    public static function setCustomLogFileName($file_name){

        app()->instance(self::CUSTOM_LOG_FILE_NAME_KEY, $file_name);
    }

    public static function setLogType($data){
        $log_type = 'info';

        if(isset($data['action']) && $data['action'] == 'SMS Sending' && isset($data['status']) && $data['status'] == 'Failed' ){
            $log_type = 'error';
        }

        return $log_type;
    }
	
	public static function getPanelLogString($panel): string
	{
		return \common\integration\Utility\Str::upperCase($panel).self::PANEL_NAME_LOG_POST_FIX;
	}

    public static function getJsonLogs($file)
    {
        $logs = [];
        $file = fopen($file, 'r');

        while (!feof($file)) {
            $line = fgets($file);

            $json_start = strpos($line, '{');
            $json_end = strrpos($line, '}');
            $json_length = $json_end - $json_start + 1;
            $json_data = substr($line, $json_start, $json_length);

            $decoded_json = json_decode($json_data, true);

            $logs[] = $decoded_json;
        }
        fclose($file);

        return $logs;

    }
	
}