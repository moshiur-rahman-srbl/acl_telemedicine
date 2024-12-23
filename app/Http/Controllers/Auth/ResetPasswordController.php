<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ChangePasswordHistory;
use App\Models\Profile;
use App\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use common\integration\BrandConfiguration;
use common\integration\GlobalFunction;
use common\integration\GlobalUser;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showResetForm(Request $request)
    {
        $token = $request->route()->parameter('token');

        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    protected function rules()
    {
        if (config('constants.PASSWORD_SECURITY_TYPE') == Profile::ALPHANUMERIC_PASSWORD) {
            $pass_rules = '|min:8|regex:' . Profile::ALPHANUMERIC_PASSWORD_REGEX;
        } else {
            $pass_rules = '|min:6|max:6|regex:' . Profile::NORMAL_PASSWORD_REGEX;
        }

        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required'.$pass_rules,
            'user_type' => 'required|in:1',
            'password_confirmation' => 'required_with:password|same:password'
        ];

    }

    public function reset(Request $request)
    {
        $request->merge(['user_type' => User::ADMIN]);

//        $request->validate($this->rules(), $this->validationErrorMessages());

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()){
            flash($validator->errors()->first(), 'danger');
            return back();
        }

        $profileObj = new Profile();
        $userObj = $profileObj->getUserByEmail($request->email, User::ADMIN);

        if (empty($userObj)) {
            flash(__('Email does not exist'), 'danger');
            return back();
        }

        $isOldPassword = $profileObj->checkOldPassword($request->password, !empty($userObj) ? $userObj->id : 0);
        if ($isOldPassword) {
            flash(__('Please, do not use old password again'), 'danger');
            return back();
        }

        list($isPassContainInfo, $errorMsg) = $profileObj->checkIfPasswordContainsInfo($request->password, $userObj);
        if ($isPassContainInfo) {
            flash($errorMsg, 'danger');
            return back();
        }

        // added by jacklin for breaking 8 password restriction of laravel
//        $this->broker()->validator(function (array $credentials){
//            [$password, $confirm] = [
//                $credentials['password'],
//                $credentials['password_confirmation'],
//            ];
//            return $password === $confirm && mb_strlen($password) >= 7;
//        });
        // added by jacklin

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
            $this->resetPassword($user, $password);
        }
        );

        $this->redirectTo = '/'.config('constants.defines.ADMIN_URL_SLUG');

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);

    }

    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);
        $user->updated_password_at = Carbon::now();
        $user->setRememberToken(Str::random(60));

        if(BrandConfiguration::isUserLocked($user)){
            $user->is_admin_verified = Profile::ADMIN_VERIFIED_APPROVED;
        }

        $user->save();

        (new ChangePasswordHistory())->createHistory($user->id, $user->password);


        if(BrandConfiguration::allowChangePasswordMail()) {

            (new GlobalUser)->sentChangePasswordEmail($user, 'ADMIN_CHANGE_PASSWORD_FROM_RESET_EMAIL');

        }

        event(new PasswordReset($user));

//        $this->guard()->login($user);
    }

    protected function sendResetResponse(Request $request, $response)
    {
        Session::flash('rp-msg', __($response));
        Session::flash('rp-typ', __('sa'));
        return redirect($this->redirectTo.'/login');

    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        Session::flash('rp-msg', __($response));
        Session::flash('rp-typ', __('da'));
        return redirect($this->redirectTo.'/login');
    }

    protected function credentials(Request $request)
    {
        return $request->only(
            'email', 'password', 'password_confirmation', 'user_type', 'token'
        );
    }

}
