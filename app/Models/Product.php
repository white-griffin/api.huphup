<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasReactions;
use App\Models\Traits\SearchableByTNT;
use App\Support\SlugService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use BelongsToBusiness,SearchableByTNT,HasReactions;

    protected $guarded = ['id'];



    // تعریف فیلدهایی که می‌خواهیم ایندکس شوند
    public function toSearchableArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'         => $this->slug,
            'business'         => $this->business->name,
            'brand'       => $this->brand?->name ?? '',
            'category'    => $this->category?->name ?? '',
            'description' => strip_tags($this->description ?? ''),
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
        return $this->hasMany(ProductVariation::class)
            ->where('activity_status',ActivityStatus::ACTIVE->value)
            ->where('is_default', true);
    }


    public function getEffectivePrice(): string
    {
        return $this->variations()->where('activity_status', ActivityStatus::ACTIVE->value)->min('price');
    }

    public function getTotalStock(): int
    {
        return$this->variations()->where('activity_status', ActivityStatus::ACTIVE->value)->sum('stock');
    }

}
