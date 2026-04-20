<?php

namespace Modules\Employer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployerUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'link'             => ['sometimes', 'string', 'max:255'],
            'bussines_label'             => ['sometimes', 'string', 'max:255'],
            'bussines_logo'             => ['sometimes', 'file', 'max:1024'],
            'full_name'             => ['sometimes', 'string', 'max:255'],
            'mobile'             => ['sometimes', 'string', 'size:11'],
            'password'             => ['sometimes', 'string', 'max:255'],
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
