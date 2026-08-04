<?php

namespace App\Services\Product;

use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductFilterService
{
    public function apply(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('brand'), function (Builder $query) use ($request) {
                $query->whereHas('brand', function (Builder $query) use ($request) {
                    $query->where('slug', $request->brand);
                });
            })
            ->when($request->filled('category'), function (Builder $query) use ($request) {
                $query->whereHas('categories', function (Builder $query) use ($request) {
                    $query->where('slug', $request->category);
                });
            })
            ->when(
                $request->filled('attribute_options'),
                function (Builder $query) use ($request) {

                    foreach ($request->attribute_options as $attributeId => $optionIds) {

                        $query->whereHas('activeVariations.variationAttributes', function (Builder $query) use ($attributeId, $optionIds) {

                            $query->where('attribute_id', $attributeId)
                                ->whereIn('attribute_option_id', $optionIds);

                        });

                    }

                }
            )->when(
                $request->filled('min_price') || $request->filled('max_price'),
                function (Builder $query) use ($request) {

                    $query->whereHas('activeVariations', function (Builder $query) use ($request) {

                        $query
                            ->when($request->filled('min_price'), fn ($q) =>
                            $q->where('price', '>=', $request->min_price)
                            )
                            ->when($request->filled('max_price'), fn ($q) =>
                            $q->where('price', '<=', $request->max_price)
                            );

                    });

                }
            )->when(
                $request->filled('sort'),
                function (Builder $query) use ($request) {

                    match ($request->sort) {

                        'newest' => $query->latest(),

                        'oldest' => $query->oldest(),

                        'price_asc' => $query->orderBy(
                            ProductVariation::select('price')
                                ->whereColumn('product_id', 'products.id')
                                ->orderBy('price')
                                ->limit(1)
                        ),

                        'price_desc' => $query->orderByDesc(
                            ProductVariation::select('price')
                                ->whereColumn('product_id', 'products.id')
                                ->orderByDesc('price')
                                ->limit(1)
                        ),

                        'name_asc' => $query->orderBy('name'),

                        'name_desc' => $query->orderByDesc('name'),

                        default => null,
                    };

                }
            );
    }
}
