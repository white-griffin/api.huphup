<?php

namespace App\Http\Requests\User\Api\V1\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'body' => [
                'required',
                'string',
                'max:5000',
            ],

            'parent_id' => [
                'nullable',
                'exists:review_messages,id',
            ],
        ];
    }
}
