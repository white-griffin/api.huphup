<?php

namespace App\Models;

use App\Enums\ProductAttributeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'attributes' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * دریافت مقدار یک ویژگی خاص
     */
    public function getAttribute(ProductAttributeType $key): mixed
    {
        return $this->attributes[$key->value] ?? null;
    }
}
