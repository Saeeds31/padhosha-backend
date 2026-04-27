<?php

namespace Modules\Employer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title'=>'required|string|min:3',
            'description'=>'required|string|min:3',
            'image'=>'required|file|max:1024',
            'amount'=>'required|integer|min:10000',
            
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
