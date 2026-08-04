<?php

namespace App\Http\Requests;

use App\Enums\ReactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleReactionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reactable_type' => ['required', 'string'],
            'reactable_id'   => ['required', 'integer'],
            'type'           => ['required', Rule::enum(ReactionType::class)],
        ];
    }
}
