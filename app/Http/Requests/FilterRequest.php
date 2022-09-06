<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
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

    public function rules(): array
    {
        return [
            'price_from' => ['integer', 'min:0', 'max:99999999998', 'lt:price_to', 'nullable'],
            'price_to' => ['integer', 'max:9999999999', 'gt:price_from', 'nullable'],
            'genres' => ['array', 'nullable'],
            'genres.*' => ['integer', 'exists:genres,id'],
            'bpm_from' => ['integer', 'min:0', 'lt:bpm_to', 'nullable'],
            'bpm_to' => ['integer', 'max:300', 'gt:bpm_from', 'nullable'],
            'is_free' => ['nullable', 'boolean']
        ];
    }
}
