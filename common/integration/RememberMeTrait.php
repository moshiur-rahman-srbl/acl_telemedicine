<?php

namespace common\integration;

use App\Http\Controllers\Traits\PermissionUpdateTreait;
use App\Models\RevokeTokens;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Throwable;

trait RememberMeTrait
{
    use PermissionUpdateTreait;

    private function setupRememberMe($userType, $reset = FALSE)
    {
        try {
            $user = Auth::user();
            if (empty($user->remember_token) || $reset) {
                $user->remember_token = Str::uuid() . $user->id;
                $user->session_id = Session::getId();
                $user->save();
            }
            Cookie::queue(
                Cookie::make(
                    $this->getRememberMeTokenKey($userType),
                    encrypt($user->remember_token),
                    config('app.REMEMBER_ME_LIFETIME')
                )
            );
            return TRUE;
        } catch (Throwable $th) {
            return FALSE;
        }
    }

    private function loginUserByRememberMe($usertype)
    {
        try {
            $rememberMeEncryptedTokenKey = $this->getRememberMeTokenKey($usertype);
            if ($rememberMeEncryptedToken = request()->cookie($rememberMeEncryptedTokenKey)) {
                $user = User::firstWhere('remember_token', decrypt($rememberMeEncryptedToken));
                if (!empty($user)) {

                    Auth::login($user);

                    if ($usertype == User::ADMIN || $usertype == User::MERCHANT) {
                        $this->getPermissionList($user->id);
                    }

                    if ($usertype == User::MERCHANT) {
                        User::setMerchantLoginSession($user->merchant_parent_user_id);
                    }

                    $revokeTokens = new RevokeTokens();
                    $revokeTokens->logoutPastLogin(Auth::User()->id);

                    return TRUE;
                } else {
                    Cookie::queue(Cookie::forget($rememberMeEncryptedTokenKey));
                }
            }
            return FALSE;
        } catch (Throwable $th) {
            return FALSE;
        }
    }

    private function getRememberMeTokenKey($userType)
    {
        $panel = '';
        if ($userType == User::ADMIN) {
            $panel = '_admin';
        } elseif ($userType == User::MERCHANT) {
            $panel = '_merchant';
        } elseif ($userType == User::CUSTOMER) {
            $panel = '_customer';
        }
        return Auth::getRecallerName() . $panel;
    }
}
