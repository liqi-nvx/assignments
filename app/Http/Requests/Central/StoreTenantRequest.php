<?php
namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'alpha_num', 'min:3', 'max:50', 'unique:tenants,id'],
        ];
    }
    
    protected function prepareForValidation()
    {
        if ($this->has('id')) {
            $this->merge([
                'domain' => strtolower($this->id) . '.localhost'
            ]);
        }
    }
}