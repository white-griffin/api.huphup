<?php

namespace App\Models;

use App\Contracts\Reviewable;
use Highlight\Mode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BusinessService extends Model implements Reviewable
{
    protected $guarded =['id'];

    protected $casts = [
        'settings' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
    public function getBusiness(): Business
    {
        return $this->business;
    }

    public function canUserReview(User $user): bool
    {
        return true;
    }

    public function canBeRated(): bool
    {
        return true;
    }

    public function isVerifiedPurchase(User $user): bool
    {
        return false;
    }
}
