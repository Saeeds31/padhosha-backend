<?php

namespace Modules\File\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileCategoryUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'title'            => ['sometimes', 'string', 'max:255'],
            'icon'             => ['sometimes', 'file', 'max:1024'],
            'slug'             => ['sometimes', 'string', 'max:1024'],
            'meta_title'       => ['sometimes', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'string'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }
}
