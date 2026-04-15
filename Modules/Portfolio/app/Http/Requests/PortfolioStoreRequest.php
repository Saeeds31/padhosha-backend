<?php

namespace Modules\Portfolio\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PortfolioStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'slug'            => ['nullable', 'string', 'max:255'],
            'main_image'             => ['required', 'file', 'max:1024'],
            'link'       => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'description'      => ['nullable', 'string'],
            'status'      => ['nullable', 'string'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:portfolio_category,id'],
            'technologies' => ['nullable', 'array'],
            'technologies.*' => ['exists:technologies,id'],
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
