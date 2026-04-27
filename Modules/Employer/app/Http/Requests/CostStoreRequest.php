<?php

namespace Modules\Employer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CostStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:3',
            'description' => 'nullable|string|min:3',
            'amount' => 'integer|string|min:10000',
            'costable_id' => 'nullable|integer',
            'costable_type' => 'required|string',
            'employer_id' => 'required|integer'
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
