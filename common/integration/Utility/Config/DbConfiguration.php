<?php

namespace common\integration\Utility\Config;

use common\integration\Brand\Configuration\All\Mix;
use common\integration\BrandConfiguration;
use common\integration\ManageLogging;
use common\integration\Utility\Exception;
use DB;
use Illuminate\Support\Facades\Artisan;

class DbConfiguration
{

    public static function secondaryDbConfiguration($credentials = [], $connection_name = 'db_connection'){

        $logObj = new ManageLogging();
        $log_data['Action'] = 'Secondary Database Connection';

        if(!empty($credentials)){
            $log_data['Message'] = 'Credentials is empty';
            $logObj->createLog($log_data);
        }

        $credentials[$connection_name] = $credentials;

        $db_settings = config()->get('database.connections');
        $add_db = \array_merge($db_settings, $credentials);
        config()->set('database.connections', $add_db);

        $log_data['Message'] = 'Connection configured successfully';

        $logObj->createLog($log_data);

    }

    public static function dbConnection($connection_name = 'db_connection'){
        return DB::connection($connection_name);
    }

    public static function dbDisconnection($connection_name = 'db_connection'){
        return DB::disconnect($connection_name);
    }

    /**
     * @return void
     * CHANGE DB PORT FOR READ ONLY
     */
    public static function dbPortReadAccess(){
        $db_port = config('constants.REPORTING_DB_PORT');
        $default_port = config('database.connections.mysql.port');
        $is_diff_port = !empty($db_port) && $db_port != $default_port;
        
        if ($is_diff_port && BrandConfiguration::call([Mix::class, 'isAllowDBReportingPortChange'])){
            $database_connection = config('database.default');
            
            DB::purge($database_connection);
            config(['database.connections.mysql.port' => $db_port]);
            DB::reconnect($database_connection);
        }
    }
    

}