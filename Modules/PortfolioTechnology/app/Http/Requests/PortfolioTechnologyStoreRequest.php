<?php

namespace Modules\PortfolioTechnology\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PortfolioTechnologyStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'icon'             => ['required', 'file', 'max:1024'],
            'slug'       => ['nullable', 'string', 'max:255'],
            'meta_title'       => ['required', 'string', 'max:255'],
            'meta_description' => ['required', 'string'],
            'description'      => ['nullable', 'string'],
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
