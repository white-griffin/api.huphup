<?php

namespace App\Models;

use App\Enums\Referral\ReferralCampaignStatus;
use App\Enums\Referral\ReferralCampaignType;
use App\Enums\Referral\ReferralQualificationEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralCampaign extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => ReferralCampaignType::class,
            'status' => ReferralCampaignStatus::class,
            'qualification_event' => ReferralQualificationEvent::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'priority' => 'integer',
            'referrer_reward_xp' => 'integer',
            'referred_reward_xp' => 'integer',
        ];
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'campaign_id');
    }
}
