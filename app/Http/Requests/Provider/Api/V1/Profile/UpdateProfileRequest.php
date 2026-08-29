<?php

namespace App\Http\Requests\Provider\Api\V1\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'father_name' => ['nullable'],
            'birth_date' => ['nullable', 'date'],
            'gender_type' => ['required', 'integer'],
            'email' => ['nullable', 'email', 'max:254'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
