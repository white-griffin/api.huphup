<?php

namespace App\Models;

use App\Enums\ProductAttributeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariation extends Model
{
    protected $guarded = ['id'];


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variationAttributes(): HasMany
    {
        return $this->hasMany(ProductVariationAttribute::class);
    }
}
