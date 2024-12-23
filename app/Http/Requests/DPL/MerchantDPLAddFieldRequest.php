<?php

namespace App\Http\Requests\DPL;

use common\integration\DplService;
use Illuminate\Foundation\Http\FormRequest;

class MerchantDPLAddFieldRequest extends FormRequest
{
    public function rules()
    {
        $dplDynamicFieldLength = DplService::getDPLDynamicFieldLength();

        return [
            'title' => 'required|max:' . $dplDynamicFieldLength,
            'title_tr' => 'required|max:' . $dplDynamicFieldLength,
        ];
    }

    public function messages()
    {
        return [
            '*.required' =>  __('The :attribute field is required.'),
            'title.max' => __('The :attribute field can have max :max character'),
            'title_tr.max' => __('The :attribute field can have max :max character')
        ];
    }
}
