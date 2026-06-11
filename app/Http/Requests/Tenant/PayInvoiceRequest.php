<?php
namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class PayInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'paid_amount' => ['required', 'numeric', 'min:0.01', 'regex:/^\d+(\.\d{1,2})?$/'],
        ];
    }

        public function messages(): array
        {
            return [
                'paid_amount.regex' => 'The payment amount must have a maximum of two decimal places.',
                'paid_amount.min'   => 'The payment amount must be greater than 0.',
            ];
        }
}