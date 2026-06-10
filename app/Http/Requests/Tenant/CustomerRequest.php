<?php
namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'name'    => ['required'],
            'email'   => ['required', 'email'],
            'phone'   => ['required'],
            'address' => ['nullable'],
        ];
    }
}