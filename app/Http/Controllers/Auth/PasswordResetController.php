<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\Profile;
use App\Models\SecurityImage;
use common\integration\BrandConfiguration;
use common\integration\GlobalUser;
use common\integration\PasswordResetClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    public function passwordReset (Request $request)
    {

        $status_code = '';
        $request->merge(['user_type' => Profile::ADMIN]);

        if (isset($request->is_create) && $request->is_create && BrandConfiguration::secretQuestionResetPassword()) {
            $request->merge(['decoded_email' => $this->customEncryptionDecryption($request->encoded_email, config('app.brand_secret_key'), 'decrypt', true)]);
            list($status_code, $description, $data) = (new PasswordResetClass())->createSecretQA($request->all());
        }

        if (empty($status_code) || $status_code == config('apiconstants.API_SUCCESS')){
            list($status_code, $description, $data) = (new PasswordResetClass())->passwordResetLink($request->all());
        }

        if ($status_code == config('apiconstants.API_SUCCESS')) {
            flash($description, 'success');
        } else {
            if (!empty($data)) {
                $errors = $data->toArray();
                $verrors = [];

                foreach ($errors as $key => $value) {
                    $verrors[$key] = is_array($value) ? $value[0] : $value;
                }
                session()->flash('verrors', $verrors);
            }
            flash($description, 'danger');
        }
        return redirect()->back();
    }

    public function passwordUpdate (Request $request)
    {
        $type = $request->type;

        if(!in_array($type, PasswordResetClass::TYPES)){
            abort(config('apiconstants.API_FAILED'), __('Type is invalid'));
        }

        $request->merge(['user_type' => Profile::ADMIN]);
        list($status_code, $description, $data) = (new PasswordResetClass())->setNewPassword($request->all());

        if ($status_code == config('apiconstants.API_SUCCESS')) {
            GlobalUser::deleteCookieForCaptcha(GlobalUser::cookieNameForCaptcha(BrandConfiguration::PANEL_ADMIN));
            flash($description, 'success');
            return redirect()->route('login');
        } else {
            if (!empty($data)) {
                $errors = $data->toArray();
                $verrors = [];

                foreach ($errors as $key => $value) {
                    $verrors[$key] = is_array($value) ? $value[0] : $value;
                }
                session()->flash('verrors', $verrors);
            }
            flash($description, 'danger');
            return redirect()->to($request->url());
        }
    }

    public function passwordResetForm (Request $request)
    {
        $create_new = false;

        $type = $request->type;

        if(!in_array($type, PasswordResetClass::TYPES)){
            abort(config('apiconstants.API_FAILED'), __('Type is invalid'));
        }

        if($type == PasswordResetClass::TYPE_CREATE){
            $create_new = true;
        }
         $decrypt_email = $this->customEncryptionDecryption($request->email, config('app.brand_secret_key'), 'decrypt', true);

        $data = [
            "token" => $request->reset_token,
            "email" => $decrypt_email,
            "user_type" => Profile::ADMIN
        ];

        if ((new PasswordReset())->validateToken($data)) {
            $security_images = [];
            if(BrandConfiguration::allowSecurityImage()){

                $security_images = (new SecurityImage())->getSecurityImagesWithBrandCodeAndStatus(SecurityImage::STATUS_ACTIVE);
            }

            $apply_captcha = false;
            if(BrandConfiguration::applyCaptchaRulesForAdminAndMerchant()){
                $apply_captcha = true;
            }

            return view('auth.passwords.reset')->with([
                'reset_token' => $request->reset_token,
                'email' => $decrypt_email,
                'create_new' => $create_new,
                'security_images' => $security_images,
                'apply_captcha' => $apply_captcha
            ]);
        } else {
            abort(config('apiconstants.API_VERIFICATION_FAILED'), __('Token is invalid or expired'));
        }
    }

    public function passwordCreateForm(Request $request)
    {
        $data = [
            "is_create" => true,
            "email" => $this->customEncryptionDecryption($request->encoded_email, config('app.brand_secret_key'), 'decrypt', true)
        ];

        return view('auth.passwords.email')->with($data);
    }
}
