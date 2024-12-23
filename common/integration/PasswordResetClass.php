<?php

namespace common\integration;

use App\Http\Controllers\Traits\ApiResponseTrait;
use App\Http\Controllers\Traits\CommonLogTrait;
use App\Http\Controllers\Traits\LoginProcessTrait;
use App\Http\Controllers\Traits\NotificationTrait;
use App\Http\Controllers\Traits\OTPTrait;
use App\Http\Controllers\Traits\SendEmailTrait;
use App\Models\ChangePasswordHistory;
use App\Models\PasswordReset;
use App\Models\Profile;
use App\User;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Frontend\FrontendMix;
use common\integration\BrandConfiguration;
use common\integration\GlobalUser;
use common\integration\Models\Merchant;
use common\integration\Models\OutGoingEmail;
use common\integration\Otp\OtpLimitRate;
use common\integration\Utility\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use common\integration\GlobalFunction;

class PasswordResetClass
{
    use CommonLogTrait, SendEmailTrait, LoginProcessTrait, OTPTrait, NotificationTrait, ApiResponseTrait;

    const TYPE_CREATE = 'create';
    const TYPE_RESET = 'reset';
    const TYPES = [
        self::TYPE_CREATE => 'create',
        self::TYPE_RESET => 'reset',
    ];

    public function passwordResetLink ($input_data)
    {
        $status_code = '';
        $description = '';
        $errors = [];
        $rules = [
            'email' => 'required|email',
        ];
        $rules_msg = [
            'email.email'=> __('Email format is invalid'),
        ];
        
//        if(!isset($input_data['new_user']) && BrandConfiguration::allowSalesPanel()){
//            $sales_rules = [
//                'user_type' => 'required',
//                'question_one' => 'required|integer',
//                'answer_one' => 'required|string',
//            ];
//            $rules = array_merge($rules,$sales_rules);
//        }
        $validator = Validator::make($input_data, $rules, $rules_msg);


        if ($validator->fails()) {
            $status_code = config('apiconstants.API_VALIDATION_FAILED');
            $description = __('Validation error');
            $errors = $validator->errors();
        } else {

            /**
             * find the valid user
             */
            if (isset($input_data['phone'])) {
                $user = (new Profile())->getUserByPhoneAndEmail($input_data['phone'], $input_data['email'], $input_data['user_type']);
            } else {
                $user = (new Profile())->getUserByEmail($input_data['email'], $input_data['user_type']);
            }

            if (!empty($user)) {
                if ((new Merchant())->subDealerMerchantRestriction($user)) {
                    return [ApiService::API_SERVICE_MERCHANT_PANEL_RESTRICTION_SUB_DEALER, __("Sub-Dealer Merchants are restricted to reset password"), $errors];
                }

                $data = [
                    "email" => $user->email,
                    "user_type" => $user->user_type,
                    "token" => $this->randomResetStr(),
                    "is_create" => $input_data['is_create'] ?? false,
	                'is_sent_email_notification' => true,
	                'userObj' => $user,
                ];
                $PassOpt = $data['is_create'] ? 'create' : 'reset';

//                if(!isset($input_data['new_user']) && BrandConfiguration::allowSalesPanel()){
//                    //have to validate security question
//                    $security_question_answer = $this->customEncryptionDecryption($input_data['answer_one'], config('app.brand_secret_key'),'decrypt');
//                    if($input_data['question_one'] != $user->question_one && $security_question_answer != $user->answer_one){
//
//                        return [config('apiconstants.API_FAILED'), __("Security Question & answer mismatch"), $errors];
//                    }
//                }

                /**
                 * save password reset token on database
                 */
                $response = (new PasswordReset())->insertData($data);
                $status_code = config('apiconstants.API_FAILED');

                if ($response) {
                    $data['name'] = $user->name;
                    $data['language'] = $user->language;

                    /**
                     * send password reset link via email
                     */
                    if ($this->sendResetEmail($data)) {
                        $status_code = config('apiconstants.API_SUCCESS');
                        $msg = str_replace('<var>', $PassOpt, 'Password <var> link was sent to :var1. Please, check the email');
                        $description = __($msg, ['var1' => $user->email]);

                        if(BrandConfiguration::allowPasswordResetMailMessage()){

                            if(isset($input_data['email'])){
                                $description = __('Your request is received. Your password reset link will be sent to :email if registered.',['email' => $input_data['email']]);
                            }

                        }

                    } else {
                        $description = __(str_replace(':var1', $PassOpt, 'Failed to send password :var1 token'));
                    }
                } else {
                    $description = __(str_replace(':var1', $PassOpt, 'Failed to generate password :var1 token'));
                }
            } else {
                $status_code = config('apiconstants.API_USER_NOT_FOUND');
                $description = __('Data not found');
                
                if(BrandConfiguration::allowPasswordResetMailMessage()){

                    if(isset($input_data['email'])){
                        $description = __('Your request is received. Your password reset link will be sent to :email if registered.',['email' => $input_data['email']]);
                    }
                   
                }
            }
        }

        return [$status_code, $description, $errors];
    }

    private function randomResetStr ()
    {
        $length = 64;
        $string = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($string, ceil($length/strlen($string)))), 1, $length);
    }

    private function sendResetEmail ($data)
    {
        $data['ip_detail'] = $this->getClientIp();
        $geo_location = GlobalFunction::geoLocation($data['ip_detail']);

        if ($geo_location) {
            $data['ip_detail'] .= (isset($geo_location['city']) && !empty($geo_location['city'])) ? ', '.$geo_location['city'] : '';
            $data['ip_detail'] .= (isset($geo_location['country_name']) && !empty($geo_location['country_name'])) ? ', '.$geo_location['country_name'] : '';
        }

        $PassOpt = $data['is_create'] ? 'create' : 'reset';
        
        $encrypted_email = $this->customEncryptionDecryption($data['email'], config('app.brand_secret_key'), 'encrypt' , true, null, null, true);

        if ($data['user_type'] == Profile::INTEGRATOR) {
            // For reason change here integrated made by vue js
            $data['reset_url'] = config('app.app_integrator_panel_url').'/reset-password?token='.$data['token']."&email=".$encrypted_email;
        } else if (in_array($data['user_type'], [Profile::SALES_ADMIN , Profile::SALES_EXPERT])) {
            //this url for sales panel end "sales-panel/create-password"
            //this url for API for sales panel "sales/api/passwordupdate"
            //$data['reset_url'] =config('constants.APP_DOMAIN'). '/sales-panel/create-password/'.$data['token']."/".$encrypted_email."/".$data['user_type'];
            $data['reset_url'] = config('constants.APP_DOMAIN'). '/onboarding/password/'.$PassOpt.'/'.$data['token']."/".$encrypted_email;
        } else {
            $data['reset_url'] =config('app.url'). '/password/'.$PassOpt.'/'.$data['token']."/".$encrypted_email;
        }

        //out_going_email
        $this->setGNPriority(OutGoingEmail::PRIORITY_EXPRESS);
        return $this->sendEmail(
            $data,
            "password_".$PassOpt,
            config('constants.defines.MAIL_FROM_ADDRESS'),
            $data['email'],
            null,
            "password_reset.password_reset_email",
            $data['language']
        );
    }

    public function setNewPassword ($input_data, $is_allow_user_account_activation_history =false)
    {
        $status_code = '';
        $description = '';
        $errors = [];
        $user = null;
        $profile = new Profile();
        $passwordReset = new PasswordReset();
        $data = [
            "token" => $input_data['reset_token'],
            "email" => $input_data['email'],
            "user_type" => $input_data['user_type']
        ];

        /**
         * check if request is from api or panel
         */
        if (isset($input_data['reset_token']) && isset($input_data['email'])) {
            $user = $profile->getUserByEmail($input_data['email'], $input_data['user_type']);

            if (!empty($user)) {

                if (!$passwordReset->validateToken($data)) {
                    $status_code = config('apiconstants.API_VERIFICATION_FAILED');
                    $description = __('Token is invalid or expired');
                }
            }
        } elseif (Auth::check()) {
            $user = Auth::user();
        }

        /**
         * check if it is a valid user or not
         */
        if (empty($user)) {
            $status_code = config('apiconstants.API_USER_NOT_FOUND');
            $description = __('Data not found');
        }

        if (empty($status_code)) {
            $isBrandAllowRegex=BrandConfiguration::allowAlphaNumericPasswordRegex();
            /**
             * check if password type is alphanumeric or 6 digits number only
             */
            if (config('constants.PASSWORD_SECURITY_TYPE') == Profile::SIX_DIGITS_PASSWORD && !in_array($input_data['user_type'],[Profile::SALES_ADMIN, Profile::SALES_EXPERT])) {
                $pass_rules = ['min:6','max:6','regex:/^[0-9]*$/'];
                $min_msg = $rex_msg = 'Password must be 6 digit number only';
            } else {
                $pass_rules = ['min:8','regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-.,]).{8,}$/'];
                $min_msg = $rex_msg = 'The password must be 8 characters long, must contain a mix of upper/lowercase letters, numbers, and special characters';
            }
            if($isBrandAllowRegex) {
                $regex_rules=GlobalUser::getRegexValidationRules();
                $pass_rules=$regex_rules['rules'];
                $rex_msg=$regex_rules['messages']['regex'];
                $min_msg =$regex_rules['messages']['min'];
            }
            $captcha = $input_data['captcha'] ?? '';

            Validator::extend('adminCaptcha', function ($attribute, $captcha, $parameters) {
                return (GlobalFunction::getBrandSession('login_captcha') == $captcha);
            }, __('message.wrong_captcha_text'));

            $rules = [
                'new_password' => array_merge(['required'],$pass_rules),
                'verify_password' => 'required|same:new_password',
            ];
            if (BrandConfiguration::isAllowLoginCaptcha() && !BrandConfiguration::call([FrontendMix::class, 'isNotAllowCaptcha'])){
                $rules = array_merge($rules, ['captcha' => ['required','adminCaptcha']]);
            }

            $validator = Validator::make($input_data, $rules, [
                'new_password.regex'=> __($rex_msg),
                'new_password.min'=> __($min_msg),
                'new_password.max'=> __('Password must be 6 digit number only'),
                'verify_password.same' => __('Passwords do not match'),
                'captcha.required' => 'Captcha is required'
            ]);
            
            $status_code = config('apiconstants.API_VALIDATION_FAILED');

            if ($validator->fails()) {
                $description = __('Validation error');
                $errors = $validator->errors();
            } else {
                /**
                 * check password rules and old password history
                 */
                if($profile->checkOldPassword($input_data['new_password'], $user->id)){
                    $description = __('When changing a password, the new password cannot be the same with the last :limit passwords', ['limit' => config('constants.PASSWORD_DENY_LAST_USED')]);
                    $status_code = config('apiconstants.API_VALIDATION_FAILED');
                }else{
                    $status_code = '';
                    if(!$isBrandAllowRegex){
                        list($is_restricted, $message) = $profile->checkPasswordRestrictionRules($input_data['new_password'], $user);
                        if($is_restricted) {
                            $description = $message;
                            $status_code = config('apiconstants.API_VALIDATION_FAILED');
                        } else {
                            $status_code = '';
                        }
                    }
                }
            }
        }

        if (empty($status_code)) {
            /**
             * now update with new password
             */
            $security_image_id = $input_data['security_image'] ?? NULL;
            
            $result = $profile->updateUser($user->id, ["password" => $input_data['new_password']], "EDIT", ["security_image_id" => $security_image_id]);
            (new ChangePasswordHistory())->createHistory($user->id, $result->password);
            $status_code = config('apiconstants.API_SUCCESS');

            if (count($profile->changePasswordHistories) > Profile::MIN_PASSWORD_COUNT) {
                $description = __('Password has been changed successfully');
            } else {

                if ($is_allow_user_account_activation_history) {
                    (new GlobalMerchant())->updateAccountActivationHistory($user);
                }
                $description = __('Your password has been successfully created');
            }
            $passwordReset->revokeToken($data);


            if(BrandConfiguration::isUserLocked($user)){
                $user->is_admin_verified = Profile::ADMIN_VERIFIED_APPROVED;
                
                if(GlobalFunction::hasBrandSession(BrandConfiguration::FORGET_PASSWORD)){
                    GlobalFunction::unsetBrandSession(BrandConfiguration::FORGET_PASSWORD);
                }

                $user->save();
            }
                
            if(BrandConfiguration::allowChangePasswordMail()) {

                $action_name = '';
                
                if($user->user_type == Profile::MERCHANT){
                    $action_name = 'MERCHANR_CHANGE_PASSWORD_FROM_RESET_EMAIL';
                }elseif($user->user_type == Profile::ADMIN){
                    $action_name = 'ADMIN_CHANGE_PASSWORD_FROM_RESET_EMAIL';
                }elseif($user->user_type == Profile::CUSTOMER){
                    $action_name = 'CUSTOMER_CHANGE_PASSWORD_FROM_RESET_EMAIL';
                }

                (new GlobalUser)->sentChangePasswordEmail($user, $action_name);

            }

        }

        return [$status_code, $description, $errors];
    }

    public function createSecretQA ($input_data)
    {
        $status_code = '';
        $description = '';
        $errors = [];
	    $data = [];
		
		list($rules, $message) = AppRequestValidation::passwordResetRequest($input_data);
		
	    $validator = Validator::make($input_data, $rules, $message);
		
        if ($validator->fails()) {
			
            $status_code = config('apiconstants.API_VALIDATION_FAILED');
            $description = __('Validation error');
            $errors = $validator->errors();
			
        } else {
			
            $profileObj = new Profile();
            $user = $profileObj->getUserByEmail($input_data['email'], $input_data['user_type']);
			
			if(isset($input_data['security_image'])){
				$data = [
					"security_image_id" => $input_data['security_image'],
				];
			}
			
			if(isset($input_data['question_one']) && isset
				($input_data['answer_one'])){
				
				$data = Arr::merge($data, [
					"question_one" => $input_data['question_one'],
					"answer_one" => $this->customEncryptionDecryption($input_data['answer_one'], config('app.brand_secret_key'), 'encrypt')
				]);

			}
			
            if (!empty($user)) {
                $profileObj->updateUser($user->id, $data, "EDIT");
            } else {
                $status_code = config('apiconstants.API_USER_NOT_FOUND');
                $description = __('User not found');
            }
        }
		
        return [$status_code, $description, $errors];
    }




    /*
     * for resetting password without any reset link
     * in this case otp to phone is used
     */

    public function passwordResetWithOtp (Request $request)
    {
        $data['inputs'] = $request->all();
        $check_user = (new Profile())->getUserByPhone($request->phone, $request->user_type);
        if (!empty($check_user)) {

            if (isset($request->otp_code)) {
                $validationResponse = $this->passwordValidation($request);

                if ($validationResponse->fails()) {
                    $data['errors'] = $validationResponse->errors();
                    return $this->sendApiResponse($validationResponse->errors()->first(), $data, config('apiconstants.API_VALIDATION_FAILED'));
                }

                $key = "PASS_RESET_OTP" . $check_user->user_type . "_" . $check_user->id;

                if ($request->otp_code == $this->get_otp_from_cache($key)) {
                    list($status_code, $description) = $this->passwordUpdate($request->all(), $check_user);

                    if ($status_code == config('apiconstants.API_SUCCESS')) {
                        $data = [];
                        $this->forget_otp_from_cache($key);
                    }
                } else {
                    $status_code = config('apiconstants.API_OTP_NOT_MATCHED');
                    $description = __("OTP is expired or invalid");
                }
            } else {
	            
	            $key = "PASS_RESET_OTP" . $check_user->user_type . "_" . $check_user->id;
				
	            list($conditions, $otp_expire_time, $response_status) = OtpLimitRate::isCheckingOtpRateLimit
	            ($key, $check_user, false);
	            
	            if($conditions){
		            
		            if ($this->sendPasswordResetOTP($check_user)) {
			            $status_code = config('apiconstants.API_SUCCESS');
			            $description = __('OTP sent successfully to your Phone');
		            } else {
			            $status_code = config('apiconstants.API_FAILED');
			            $description = __('Failed to send OTP');
		            }
					
	            }else{
		            
		            $status_code = $response_status;
		            $description = OtpLimitRate::prepareOtpLimitMessage($otp_expire_time);
		            
	            }
				
            }
        } else {
            $status_code = config('apiconstants.API_USER_NOT_FOUND');
            $description = __('User not found');
        }

        return $this->sendApiResponse($description, $data, $status_code);
    }

    private function passwordValidation (Request $request)
    {
        if (config('constants.PASSWORD_SECURITY_TYPE') == Profile::ALPHANUMERIC_PASSWORD) {
            $pass_rules = '|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-.,]).{8,}$/';
            $min_msg = $rex_msg = 'The password must be 8 characters long, must contain a mix of upper/lowercase letters, numbers, and special characters';
        } else {
            $pass_rules = '|min:6|max:6|regex:/^[0-9]*$/';
            $min_msg = $rex_msg = 'Password must be 6 digit number only';
        }

        return Validator::make($request->all(), [
            'otp_code' => 'required',
            'new_password' => 'required'.$pass_rules,
            'verify_password' => 'required|same:new_password'
        ], [
            'new_password.regex'=> __($rex_msg),
            'new_password.min'=> __($min_msg),
            'new_password.max'=> __('Password must be 6 digit number only'),
            'verify_password.same' => __('Passwords do not match')
        ]);
    }


    private function passwordUpdate ($input_data, $auth_user)
    {
        $status_code = config('apiconstants.API_SUCCESS');
        $profile = new Profile();
        list($is_restricted, $description) = $profile->checkPasswordRestrictionRules($input_data['new_password'], $auth_user);

        if(!$is_restricted) {
            $is_restricted = $profile->checkOldPassword($input_data['new_password'], $auth_user->id);
            $description = __('When changing a password, the new password cannot be the same with the last :limit passwords', ['limit' => config('constants.PASSWORD_DENY_LAST_USED')]);
        }

        if($is_restricted) {
            $status_code = config('apiconstants.API_VALIDATION_FAILED');
        } else {
            $response = $profile->updateUser($auth_user->id, ['password' => $input_data['new_password']]);

            if (isset($response->id)) {
                (new ChangePasswordHistory())->createHistory($auth_user->id, $response->password);
                $description = __('Password has been changed successfully');
            } else {
                $status_code = config('apiconstants.API_FAILED');
                $description = __('Failed to update password');
            }
        }

        return [$status_code, $description];
    }

}