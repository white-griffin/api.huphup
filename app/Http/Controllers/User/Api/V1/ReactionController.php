<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Enums\ReactionType;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\ToggleReactionRequest;
use App\Models\Product;
use App\Services\Reaction\ReactionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ReactionController extends Controller
{
    public function toggle(ToggleReactionRequest $request)
    {
        $data = $request->validated();

        $model = $this->resolveReactable(
            $data['reactable_type'],
            $data['reactable_id']
        );

        app(ReactionService::class)->toggle(
            user: $request->user(),
            model: $model,
            type: ReactionType::from($data['type']),
        );

        return ApiResponse::Success('عملیات موفق');
    }

    private function resolveReactable(string $type, int $id): Model
    {
        return match ($type) {
            'product' => Product::query()->findOrFail($id),
            default => throw ValidationException::withMessages([
                'reactable_type' => 'نوع آیتم نامعتبر است.'
            ]),
        };
    }
}
