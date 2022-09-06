<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'beats' => ['required', 'array', 'min:1'],
            'beats.*' => ['required', 'integer', 'exists:beats,id']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
