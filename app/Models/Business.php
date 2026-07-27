<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use App\Models\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Business extends Model
{
    use SoftDeletes,HasWallet;

    protected $guarded = ['id'];

    protected $casts =[
        'settings' => 'array',
    ];

    protected $appends = [
        'logo_url','cover_url'
    ];

    public function getLogoUrlAttribute()
    {
        return $this->logo
            ? Storage::disk('public')->url($this->logo)
            : null;
    }

    public function getCoverUrlAttribute()
    {
        return $this->cover_image
            ? Storage::disk('public')->url($this->cover_image)
            : null;
    }
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(BusinessService::class)
            ->where('activity_status', ActivityStatus::ACTIVE->value);
    }


    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function reviewMessages(): MorphMany
    {
        return $this->morphMany(
            ReviewMessage::class,
            'author'
        );
    }

    public function reputation(): HasOne
    {
        return $this->hasOne(BusinessReputation::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }
}
