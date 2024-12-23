<?php

namespace App\Http\Middleware;

use Closure;
use Session;
class TwoFA
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
      
        $isEnable = config('app.is_otp_enable');
        $isOTP = 0;
        // if(\Auth::check()){
        //     $isEnable = \Auth::user()->is_otp_required ?? config('app.is_otp_enable');
        // }
        if(Session::get('isOTP') != null){
            $isOTP = Session::get('isOTP');
        }

        if($isEnable == 1 && $isOTP == 1){

            return redirect(route('verifyOTP'));
        }
        
        //echo config('app.is_otp_enable');exit;
        
        return $next($request);
    }
}
