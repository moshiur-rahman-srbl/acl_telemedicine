<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HolidayRequest extends FormRequest
{
    public function rules()
    {
        return [
            'codeAndTitle' => 'required',
            'description' => 'required|min:10|max:255',
            'begin_date' =>'required|date',
            'end_date' =>'required|date|after:begin_date',
        ];
    }

    public function messages()
    {
        return [
            '*.required' =>  __('The :attribute field is required.'),
            'max' => __('The :attribute may not be greater than :max'),
            'min' => __('The :attribute may not be lower than :min'),
            'date' =>  __('The :attribute is not a valid date.')
            
        ];
    }
}
