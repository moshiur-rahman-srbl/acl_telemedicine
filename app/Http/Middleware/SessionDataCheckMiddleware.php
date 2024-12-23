<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Traits\CommonLogTrait;
use Closure;
use common\integration\BrandConfiguration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SessionDataCheckMiddleware
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

        if (!empty(Auth::user())) {
            //check session ip

            $session_ip = $request->session()->get('APP_ADMIN_LOGIN_IP');
            $current_route = $request->path();
            if (!empty($session_ip) && ($session_ip != $this->getClientIp()) && $current_route != 'logout') {
                if (auth()->check()) {
                    auth()->user()->update(['verified' => 0]);
                    $request->session()->flush();
                    auth()->logout();
                    return redirect()->route('login');
                }
            }


            $modules = $request->session()->get('modules');
            if (empty($modules)) {

                $request->session()->flush(); // remove all the session data

                Auth::logout(); // logout user
                return redirect()->route('login');
            }
        }


        return $next($request);
    }
}
