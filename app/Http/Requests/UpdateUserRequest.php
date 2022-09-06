<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'min: 3', 'max: 255'],
            'first_name' => ['nullable', 'string', 'min: 3', 'max: 255'],
            'last_name' => ['nullable', 'string', 'min: 3', 'max: 255'],
            'profile_picture' => ['nullable', 'file', 'dimensions:min_width=500,min_height=500,max_width:1500,max_height:1500', 'mimes:jpg,bmp,png']
        ];
    }
}
