<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Traits\CommonLogTrait;
use Closure;
use Session;
use Config;

class VisitorLog
{
    use CommonLogTrait;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $request_url = $request->path();
        $request_url = substr($request_url, strpos($request_url, '/') + 1);

        $logData['action'] = 'ADMIN_VISITED';
        $logData['request_url'] = $request_url;
        $logData['request_method'] = $request->method();
        $this->createLog($this->_getCommonLogData($logData));

        return $next($request);


    }
}
