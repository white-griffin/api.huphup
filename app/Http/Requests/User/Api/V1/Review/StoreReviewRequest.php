<?php

namespace App\Http\Requests\User\Api\V1\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rating' => [
                'nullable',
                'integer',
                'between:1,5',
            ],

            'title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'body' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function authorize(): bool
    {
        return auth()->check();
    }
}
