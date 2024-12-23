<?php

namespace App\Http\Controllers;

use common\integration\GlobalFunction;
use Illuminate\Http\Request;
use App;
use Illuminate\Support\Facades\Auth;
use Session;
use Cookie;

class LocalizationController extends Controller
{
    public function index($locale)
    {
        $isSameReferer = GlobalFunction::isSameReferer();
        if (!$isSameReferer) {
            return redirect(config('app.url'));
        }

        if (Auth::check()) {
            $user = App\User::where('id', Auth::id())->first();
            $user->language = $locale;
            $user->save();
        }

        App::setLocale($locale);
        //store the locale in session so that the middleware can register it
        session()->put('locale', $locale);
        $timeStamp = 864000;// for 10 days
        Cookie::queue('locale', $locale, $timeStamp);

        return redirect()->back();
    }


}
