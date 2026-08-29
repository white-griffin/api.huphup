<?php

namespace App\Http\Requests\Provider\Api\V1\Business;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'description' => ['nullable'],
            'logo' => ['nullable'],
            'cover_image' => ['nullable'],
            'email' => ['nullable', 'email', 'max:254'],
            'latitude' => ['nullable', 'decimal:2'],
            'longitude' => ['nullable', 'decimal:2'],
            'bank_name' => ['nullable'],
            'bank_account_holder' => ['nullable'],
            'bank_card' => ['nullable'],
            'bank_iban' => ['nullable'],
            'settings' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
