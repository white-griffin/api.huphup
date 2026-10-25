<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Enums\ReactionType;
use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ToggleReactionRequest;
use App\Http\Resources\V1\User\Business\BusinessResource;
use App\Http\Resources\V1\User\Products\ProductResource;
use App\Models\Business;
use App\Models\Product;
use App\Models\Reaction;
use App\Services\Reaction\ReactionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReactionController extends Controller
{

    public function index(Request $request)
    {
        $data = $request->validate([
            'type' => [
                'required',
                Rule::enum(ReactionType::class),
            ],
            'entity_type' => [
                'required',
                Rule::in(['product', 'business']),
            ],
        ]);

        $userId = $request->user()->id;

        $entityMap = [
            'product' => [
                'model' => Product::class,
                'resource' => ProductResource::class,
            ],
            'business' => [
                'model' => Business::class,
                'resource' => BusinessResource::class,
            ],
        ];

        $entityType = $data['entity_type'];
        $entityModel = $entityMap[$entityType]['model'];
        $resourceClass = $entityMap[$entityType]['resource'];

        $morphType = (new $entityModel())->getMorphClass();

        $reactions = Reaction::query()
            ->where('user_id', $userId)
            ->where('type', $data['type'])
            ->where('reactable_type', $morphType)
            ->with('reactable')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $reactions->getCollection()->transform(
            function ($reaction) use ($entityType, $resourceClass, $request) {
                $entity = $reaction->reactable;

                if (!$entity) {
                    return null;
                }

                $entityData = $resourceClass::make($entity)->resolve($request);

                return [
                    'reaction_type' => $reaction->type instanceof ReactionType
                        ? $reaction->type->value
                        : $reaction->type,

                    'entity_type' => $entityType,

                    ...$entityData,
                ];
            }
        );

        $reactions->setCollection(
            $reactions->getCollection()->filter()->values()
        );

        return ApiResponse::success(
            'عملیات موفق',
            $reactions
        );
    }
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
            'business' => Business::query()->findOrFail($id),
            default => throw ValidationException::withMessages([
                'reactable_type' => 'نوع آیتم نامعتبر است.'
            ]),
        };
    }
}
