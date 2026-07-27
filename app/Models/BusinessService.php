<?php

namespace App\Models;

use App\Contracts\Reviewable;
use App\Enums\AppointmentStatuses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
        return Appointment::query()
            ->where('business_id', $this->business_id)
            ->where('business_service_id', $this->id)
            ->where('user_id', $user->id)
            ->where('status', AppointmentStatuses::COMPLETED->value)
            ->exists();
    }

    public function canBeRated(): bool
    {
        return true;
    }

    public function isVerifiedPurchase(User $user): bool
    {
        return false;
    }

    public function reviewSummary(): MorphOne
    {
        return $this->morphOne(
            ReviewSummary::class,
            'reviewable'
        );
    }
}
