<?php

namespace App\Models;

use App\Enums\Referral\ReferralRewardStatus;
use App\Enums\Referral\ReferralRewardType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralReward extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => ReferralRewardType::class,
            'status' => ReferralRewardStatus::class,
            'amount' => 'integer',
            'awarded_at' => 'datetime',
        ];
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
