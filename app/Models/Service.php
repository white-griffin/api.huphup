<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $guarded = ['id'];

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
}
