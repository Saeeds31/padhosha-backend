<?php

namespace Modules\Employer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployerStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [

            'bussines_label'             => ['required', 'string', 'max:255'],
            'link'             => ['nullable', 'string', 'max:255'],
            'bussines_logo'             => ['required', 'file', 'max:1024'],
            'full_name'             => ['required', 'string', 'max:255'],
            'mobile'             => ['required', 'string', 'size:11'],
            'password'             => ['required', 'string', 'max:255'],

        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
