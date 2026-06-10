<?php
namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class PayInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'paid_amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}