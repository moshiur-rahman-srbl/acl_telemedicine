<?php

namespace App\Http\Middleware;

use App\User;
use common\integration\RememberMeTrait;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    use RememberMeTrait;

    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('login');
        }
    }
}
