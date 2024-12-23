<?php

namespace App\Http\Requests;

use common\integration\Models\ServiceCredential;
use common\integration\Utility\Arr;
use Illuminate\Foundation\Http\FormRequest;

class ServiceAddRequest extends FormRequest
{
    public function rules()
    {
        return [
            'type' => 'required|in:'.Arr::implode(',', Arr::keys(ServiceCredential::getTypes())),
            'service_id' => 'required|numeric|min:'.ServiceCredential::SERVICE_ID_MIN_LEN.'|unique:service_credentials,service_id',
            'service_client_id' => 'required|string|min:'.ServiceCredential::SERVICE_CLIENT_ID_LEN.'|max:'.ServiceCredential::SERVICE_CLIENT_ID_LEN.'|unique:service_credentials,service_client_id',
            'service_client_secret' => 'required|string|min:'.ServiceCredential::SERVICE_CLIENT_SECRET_LEN.'|max:'.ServiceCredential::SERVICE_CLIENT_SECRET_LEN.'|unique:service_credentials,service_client_secret',
        ];

        $type = $this->get('type');
        $addManually = $this->has('addManually');

        if (($type === ServiceCredential::TYPE_BASIC_AUTH || $type === ServiceCredential::TYPE_API_KEY) && $addManually) {
            $rules['username'] = 'required';
            $rules['password'] = 'required';
        }
        return $rules;
    }

    public function messages() {
        return [
            'type.in' => __('Type is unknown.'),
            'service_id.numeric' => __('Service ID should be numeric.'),
            'service_id.min' => __('Service ID min length should be :var.', ['var' => ServiceCredential::SERVICE_ID_MIN_LEN]),
            'service_id.unique' => __('Service ID is already existed, please use different one.'),
            'service_client_id.min' => __('Service Client ID min length should be :var.', ['var' => ServiceCredential::SERVICE_CLIENT_ID_LEN]),
            'service_client_id.unique' => __('Service Client ID is already existed, please use different one.'),
            'service_client_secret.min' => __('Service Client Secret min length should be :var.', ['var' => ServiceCredential::SERVICE_CLIENT_SECRET_LEN]),
            'service_client_secret.unique' => __('Service Client Secret is already existed, please use different one.'),
            'username.required' => __('Username / Key is required'),
            'password.required' => __('Password / value is required'),
        ];

    }
}
