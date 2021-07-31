<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CraftRequest extends FormRequest
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
            'name' => 'required|max:255',
            'picturePath' => 'required|image',
            'description' => 'required',
            'materials' => 'required',
            'price' => 'required|max:255',
            'rate' => 'required|numeric',
            'types' => '',
        ];
    }
}
