<?php

namespace App\Providers;

use App\User;
use common\integration\BrandConfiguration;
use common\integration\ManageLogging;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        if (!isset($_SERVER['HTTPS'])){
//            $this->app['request']->server->set('HTTPS','on');
//        }

        $this->app->singleton('log_id', function () {
            return ManageLogging::uniqueLogId();
        });

        ini_set("precision", 14);
        ini_set("serialize_precision", 14);

        $parse_url = parse_url(config('app.url'));
        if(isset($parse_url['host']) && !empty($parse_url['host'])){
            $_SERVER["SERVER_NAME"] = $parse_url['host'];
        }

        if (!BrandConfiguration::allowNoSecureConnection()){
            $this->app['request']->server->set('HTTPS','on');
        }


        Validator::extend('unique_phone', function ($attribute, $value, $parameters, $validator) {

            $user_type = (!empty($parameters)) ? head($parameters) : 1;

            $except_id = (!empty($parameters[1])) ? $parameters[1] : null;

            $userObj = User::where('phone', $value)->where('user_type', $user_type)->first();

            if (empty($userObj)){
                return true;
            }

            if(!empty($except_id)) {
                return !empty($userObj) && $userObj->id == $except_id;
            }

            return false;
        }, __('message.phone_unique_text'));

        Validator::extend('unique_email', function ($attribute, $value, $parameters, $validator) {

            $user_type = (!empty($parameters)) ? head($parameters) : 1;

            $except_id = (!empty($parameters[1])) ? $parameters[1] : null;

            $userObj = User::where('email', $value)->where('user_type', $user_type)->first();

            if (empty($userObj)){
                return true;
            }

            if(!empty($except_id)) {
                return !empty($userObj) && $userObj->id == $except_id;
            }

            return false;
        }, __('message.email_unique_text'));
    }
}
