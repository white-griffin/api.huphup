<?php

namespace App\Services\Product;

use App\Enums\PublicationStatus;
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
            ->where('publication_status', PublicationStatus::PUBLISHED->value);
    }
}
