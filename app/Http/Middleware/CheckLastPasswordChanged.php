<?php

namespace App\Http\Middleware;

use App\Models\Profile;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckLastPasswordChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && config('constants.PASSWORD_CHANGE_AFTER_MONTHS') > 0)
        {
            if (Session::has('AdminChangedPasswordStatus'.Auth::user()->id)
                && !in_array($request->path(), Profile::IGNORE_PASS_CHNG_PATH))
            {
                $msg = __("In accordance with our security policy, you must change your password every <var> months");
                $msg = str_replace('<var>', config('constants.PASSWORD_CHANGE_AFTER_MONTHS'), $msg);
                flash($msg, 'warning');

                return redirect()->route(config('constants.defines.APP_USERS_CHANGEPASSWORD'));
            }
        }

        return $next($request);
    }
}
