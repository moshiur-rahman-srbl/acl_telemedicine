<?php

namespace App\Http\Middleware;

use Closure;
use common\integration\ApiService;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Backend\BackendAdmin;
use common\integration\BrandConfiguration;
use common\integration\Override\Headers\ManipulateRequestHeader;
use common\integration\Utility\Exception;
use common\integration\Utility\Url;
use Illuminate\Support\Facades\Session;

class FrameHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->headers->set('X-XSS-Protection', '1; mode=block');
        if (\session()->has('REMOTE_LOGIN') && $request->headers->get('sec-fetch-dest') != "iframe") {
            \session()->forget('REMOTE_LOGIN');
        }
        $response = $next($request);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);
        $response->headers->set('Strict-Transport-Security', 'max-age=16070400; includeSubDomains', true);
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        return $response;
    }

}
