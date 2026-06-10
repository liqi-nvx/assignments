<?php
namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class BuyProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'goods_id'    => ['required', 'exists:goods,id'],
            'quantity'    => ['required', 'integer', 'min:1'],
        ];
    }
}