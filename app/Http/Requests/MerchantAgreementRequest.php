<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MerchantAgreementRequest extends FormRequest
{
    public function rules()
    {
        if (request()->isMethod('post')) {

            $rule = [
                'en_subject' => 'required',
                'en_body' => 'required',
                'tr_subject' => 'required',
                'tr_body' => 'required',
            ];

        } else {
            $rule = [];
        }

        return $rule;
    }

}
