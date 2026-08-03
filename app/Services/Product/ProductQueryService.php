<?php

namespace App\Services\Product;

use App\Enums\ActivityStatus;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductQueryService
{
    public function make(): Builder
    {
        return Product::query()
            ->with([
                'images',
                'brand',
                'categories',
                'activeVariations.variationAttributes.attribute',
                'activeVariations.variationAttributes.option',
            ])
            ->where('publication_status', 1);
    }

    public function applySort(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'oldest' => $query
                ->orderBy('created_at', 'asc'),

            'price_asc' => $query
                ->orderByRaw('
                    (
                        SELECT MIN(product_variations.price)
                        FROM product_variations
                        WHERE product_variations.product_id = products.id
                        AND product_variations.activity_status = ?
                    ) ASC
                ', [
                    ActivityStatus::ACTIVE->value,
                ]),

            'price_desc' => $query
                ->orderByRaw('
                    (
                        SELECT MIN(product_variations.price)
                        FROM product_variations
                        WHERE product_variations.product_id = products.id
                        AND product_variations.activity_status = ?
                    ) DESC
                ', [
                    ActivityStatus::ACTIVE->value,
                ]),

            default => $query
                ->orderBy('created_at', 'desc'),
        };
    }
}
