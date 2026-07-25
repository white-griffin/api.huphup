<?php

namespace App\Http\Resources\V1\User\Products;


use App\Enums\ReactionType;
use App\Http\Resources\V1\User\CategoryResource;
use App\Http\Resources\V1\User\ReviewResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $reactionTypes = $this->whenLoaded('reactions', function () {
            return $this->reactions
                ->pluck('type')
                ->map(fn (ReactionType $type) => $type->value)
                ->values();
        }, collect());

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,

            'effective_price' => (int) $this->activeVariations->min('price'),

            'discount_price' => (int) $this->activeVariations
                ->whereNotNull('discount_price')
                ->min('discount_price'),

            'total_stock' => (int) $this->activeVariations->sum('stock'),

            'variations' => VariationResource::collection($this->activeVariations),

            'images' => ProductImageResource::collection($this->images),

            'categories' => CategoryResource::collection($this->categories),

            'brand' => BrandResource::make($this->brand),

            'reactions' => [
                'count' => [
                    'likes' => $this->likes_count ?? 0,
                ],

                'user' => [
                    'types' => $reactionTypes,

                    'liked' => $reactionTypes->contains('like'),

                    'bookmarked' => $reactionTypes->contains('bookmark'),
                ],
            ],

            'reviews' => ReviewResource::collection(
                $this->whenLoaded('reviews')
            ),
        ];
    }
}
