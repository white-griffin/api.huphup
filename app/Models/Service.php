<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class Service extends Model
{
    protected $guarded = ['id'];

    protected $appends = [
        'image_url'
    ];

    public function getImageUrlAttribute()
    {
        return $this->image
            ? Storage::disk('public')->url($this->image)
            : null;
    }
    public function scopeForBusiness($query)
    {
        return $query->whereHas('businesses', function ($q) {
            $q->where('business_id', business()->id);
        });
    }

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_services')
            ->withPivot(['price', 'duration', 'settings', 'activity_status']);
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
}
