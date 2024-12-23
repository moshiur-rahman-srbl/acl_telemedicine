<?php

namespace App\Http\Requests;

use common\integration\BrandConfiguration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckSecretQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        if (request('action') == 'checkSecretQuestion') {
            $rules['email'] = 'required|email|max:100';
            if (BrandConfiguration::securityImageForResetPasssword()) {
                $rules['security_image'] = 'required';
            }
            else{
                $rules['question_one'] = 'required';
                $rules['answer_one'] = 'required|max:255';
            }
        }
        elseif (request('action') == 'checkOtp') {
            $rules['otp'] = 'required';
        }

        return $rules;

    }

    public function messages()
    {
        $messages = [];

        if (request('action') == 'checkSecretQuestion') {
            $messages['email.required'] =  __('The email field is required.');
            if (BrandConfiguration::securityImageForResetPasssword()) {
                $messages['security_image.required'] = __('Security image field is required');
            }
            else{
                $messages['answer_one.max'] = __('Answer is too long');
                $messages['question_one.required'] = __('Question field is required');
                $messages['answer_one.required'] = __('Answer field is required');
            }
        }

        return $messages;
    }

    protected function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json($validator->errors()->first(), 200));
    }
}
