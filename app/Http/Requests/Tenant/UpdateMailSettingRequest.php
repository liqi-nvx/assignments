<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMailSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sender_name'   => 'required|string|max:255',
            'mail_username' => 'required|email|max:255',
            'mail_password' => 'nullable|string',
        ];
    }
}