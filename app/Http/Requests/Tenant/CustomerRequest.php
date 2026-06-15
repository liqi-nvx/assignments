<?php
namespace App\Http\Requests\Tenant;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            'name'    => ['required'],
            'email'   => ['required', 'email', Rule::unique('customers', 'email')->ignore($customer?->id)],
            'phone'   => ['required'],
            'address' => ['nullable'],
        ];
    }
}