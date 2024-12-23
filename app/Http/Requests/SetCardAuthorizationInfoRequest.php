<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\Traits\ApiResponseTrait;
use common\integration\ApiService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class SetCardAuthorizationInfoRequest extends FormRequest
{
    use ApiResponseTrait;
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
        return [
            'card_no' => 'required',
            'auth_dom_ecom' => ['string', Rule::in(['true', 'false'])],
            'auth_dom_moto' => ['string', Rule::in(['true', 'false'])],
            'auth_int_ecom' => ['string', Rule::in(['true', 'false'])],
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'card_no.required' => __('Card No is required!'),
            'auth_dom_ecom.in' =>  __('The selected Auth DOM ECOM is not valid.'),
            'auth_dom_moto.in' =>  __('The selected Auth DOM MOTO is not valid.'),
            'auth_int_ecom.in' =>  __('The selected Auth INT ECOM is not valid.'),
        ];
    }

    /**
     * @param Validator $validator
     * @return void
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException($this->sendApiResponse("validation error",$errors,ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR));
    }
}
