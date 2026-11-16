<?php

namespace App\Models;

use App\Enums\Referral\ReferralQualificationEvent;
use App\Enums\Referral\ReferralQualificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Referral extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'qualification_event' => ReferralQualificationEvent::class,
            'qualification_status' => ReferralQualificationStatus::class,
            'referrer_reward_xp' => 'integer',
            'referred_reward_xp' => 'integer',
            'qualified_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(ReferralCampaign::class);
    }

    public function referrer(): MorphTo
    {
        return $this->morphTo();
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class);
    }
}
