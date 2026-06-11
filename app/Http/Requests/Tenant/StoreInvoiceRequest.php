<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id'      => 'required|integer|exists:customers,id',
            'items'            => 'required|array|min:1',
            'items.*.goods_id' => 'required|integer|exists:goods,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}