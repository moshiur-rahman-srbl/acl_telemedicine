<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\OTPTrait;
use App\Http\Requests\CheckSecretQuestionRequest;
use App\User;
use common\integration\BrandConfiguration;
use common\integration\GlobalUser;
use common\integration\Models\OutGoingEmail;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Models\SecurityImage;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails , OTPTrait;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showLinkRequestForm()
    {

        return view('auth.passwords.email');
    }
    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);
        $request->merge(['user_type' => User::ADMIN]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $request->only('email','user_type')
        );

        $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);

        if(BrandConfiguration::redirectToLoginAtPasswordReset()){
            return redirect()->route('login');
        }
        return back();
    }

    public function checkSecretQuestion(CheckSecretQuestionRequest $request){

        if ($request->ajax() && isset($request->action) && !empty($request->action) && ($request->action == 'checkSecretQuestion')) {
            $globalUser = new GlobalUser();

            if (BrandConfiguration::isResetPasswordRequestHasLimit()){
                $email = $request->has('email') ? $request->email:'';
                $input['key'] = 'RESET_PASSWORD_REQUEST_' . User::ADMIN.'_'.$email;
                $input['time'] = GlobalUser::FORGET_PASSWORD_BLOCKING_TIME_IN_SEC;
                $countAttempt = $globalUser->handleForgotPasswordRequestLimit($input);

                if ($countAttempt > GlobalUser::FORGET_PASSWORD_MAX_ALLOWED_ATTEMPT){
                    $data['status'] = false;
                    $data['otp'] = false;
                    $data['message'] = __("Maximum limit reached! Please try again later");
                    return $data;
                }
            }
            //process for secret question
            return $globalUser->processSecretQuestionAnswer($request, User::ADMIN);

        }elseif ($request->ajax() && isset($request->action) && !empty($request->action) && ($request->action == 'checkOtp')){

            //validation
            $validator = Validator::make($request->all(), [
                'otp' => 'required',
            ]);

            if ($validator->fails()) {
                $description = $validator->errors()->first();
                return response()->json($description, 200);
            }

            //process for secret question otp submit
            return (new GlobalUser())->submitOtpSecretQuestion($request);

        }
        $data['status'] = false;
        $data['message'] = __("Some Error Occurred!");
        return response()->json($data, 200);
    }

}
