<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Business extends Model
{
    use SoftDeletes;
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

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'business_services')
            ->using(BusinessService::class)
            ->withPivot(['price', 'duration', 'settings', 'activity_status'])
            ->withTimestamps();
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

}
