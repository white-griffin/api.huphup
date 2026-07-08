<?php

namespace App\Services\Product;

use App\Models\ProductVariationAttribute;
use Illuminate\Database\Eloquent\Builder;

class ProductFacetService
{
    public function build(Builder $query): array
    {
        $productIds = (clone $query)
            ->pluck('products.id');

        if ($productIds->isEmpty()) {
            return [];
        }

        $attributes = ProductVariationAttribute::query()
            ->whereHas('variation', function ($query) use ($productIds) {
                $query->whereIn('product_id', $productIds);
            })
            ->with([
                'attribute',
                'option',
                'variation',
            ])
            ->get()
            ->filter(fn ($item) => $item->attribute?->is_filterable);

        if ($attributes->isEmpty()) {
            return [];
        }

        return $attributes
            ->groupBy('attribute_id')
            ->map(function ($attributeGroup) {

                $attribute = $attributeGroup->first()->attribute;

                $options = $attributeGroup
                    ->groupBy('attribute_option_id')
                    ->map(function ($optionGroup) {

                        $option = $optionGroup->first()->option;

                        return [
                            'id' => $option->id,
                            'label' => $option->label,
                            'value' => $option->value,
                            'count' => $optionGroup
                                ->pluck('variation.product_id')
                                ->unique()
                                ->count(),
                        ];
                    })
                    ->reject(fn ($option) => $option['count'] === 0)
                    ->values();

                if ($options->isEmpty()) {
                    return null;
                }

                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'slug' => $attribute->slug,
                    'options' => $options,
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }
}
