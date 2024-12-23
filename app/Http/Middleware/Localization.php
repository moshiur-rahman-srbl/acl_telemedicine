<?php

namespace App\Http\Middleware;

use Closure;
use App;
use Cookie;

class Localization
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
        if (!$request->ajax() && $request->isMethod('post')){
            $request->session()->put('_token', str_random(40));
        }
        //CSRF token update in every page refresh
        //$request->session()->put('_token', str_random(40));

        if (session()->has('locale')) {
            App::setLocale(session()->get('locale'));
        } elseif (Cookie::has('locale')) {
            App::setLocale(Cookie::get('locale'));
        }

        return $next($request);

    }
}
