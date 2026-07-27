<?php

namespace App\Services\Commission;

use App\Enums\ActivityStatus;
use App\Models\Business;
use App\Models\CommissionRule;

class CommissionService
{
    public function resolveRate(
        Business $business,
        float $rating,
    ): float {
        return (float) (
            CommissionRule::query()
                ->where('business_type', $business->business_type)
                ->where('activity_status', ActivityStatus::ACTIVE->value)
                ->where('min_rating', '<=', $rating)
                ->where('max_rating', '>=', $rating)
                ->orderBy('priority')
                ->value('commission_rate')
            ?? 0
        );
    }


    public function calculateAmount(
        int $amount,
        float $rate,
    ): int {
        return (int) round(
            $amount * ($rate / 100)
        );
    }
}
