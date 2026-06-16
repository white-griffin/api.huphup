<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use App\Models\Scopes\BusinessScope;
use App\Models\Traits\BelongsToBusiness;
use App\Support\SlugService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use BelongsToBusiness;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:0',
            'discount_price' => 'decimal:0',
        ];
    }


    public function getRouteKeyName(): string
    {
        return 'slug';
    }
    protected static function booted(): void
    {
        static::saving(function ($product) {

            if (!$product->slug && $product->name) {
                $product->slug = app(SlugService::class)
                    ->generate($product);
            }

        });
    }
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_products')->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function activeVariations(): HasMany
    {
        return $this->hasMany(ProductVariation::class)->where('is_default', true);
    }

    // در Product.php
    public function getEffectivePrice(): string
    {
        return $this->variations()->where('activity_status', ActivityStatus::ACTIVE->value)->exists()
            ? $this->variations()->where('activity_status', ActivityStatus::ACTIVE->value)->min('price')
            : $this->price;
    }

    public function getTotalStock(): int
    {
        return $this->variations()->where('activity_status', ActivityStatus::ACTIVE->value)->exists()
            ? $this->variations()->where('activity_status', ActivityStatus::ACTIVE->value)->sum('stock')
            : $this->stock;
    }

}
