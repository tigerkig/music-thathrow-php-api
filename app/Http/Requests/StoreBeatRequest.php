<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBeatRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'artwork' => ['file', 'dimensions:min_width=500,min_height=500,max_width:1500,max_height:1500', 'mimes:jpg,bmp,png'],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['required', 'string', 'min:3'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['required', 'integer', 'exists:genres,id'],
            'parts' => ['required_if:is_exclusive,true', 'array', 'min:1'],
            'parts.*' => ['required', 'required_if:is_exclusive,true', 'integer', 'exists:parts,id'],
            'bpm' => ['required', 'integer', 'min:0', 'max:300'],
            'is_free' => ['required', 'boolean'],
            'is_exclusive' => ['required_if:is_free,false', 'boolean'],
            'preview' => ['required', 'file', 'mimes:mp3'],
            'price' => ['required_if:is_free,false', 'numeric', 'min:0', 'max:99999999'],
            'download'=> ['required_if:is_free,false', 'file', 'mimes:zip,rar'],
        ];
    }
}
