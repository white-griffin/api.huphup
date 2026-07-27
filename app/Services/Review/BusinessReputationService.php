<?php


namespace App\Services\Review;

use App\Models\Business;
use App\Models\BusinessReputation;
use App\Services\Commission\CommissionService;

class BusinessReputationService
{
    public function refresh(Business $business): void
    {
        $summaries = $business->services()
            ->with('reviewSummary')
            ->get()
            ->pluck('reviewSummary')
            ->filter();

        $ratingCount = $summaries->sum('rating_count');

        $reviewCount = $summaries->sum('review_count');

        $ratingSum = $summaries->sum(
            fn ($summary) => $summary->rating_avg * $summary->rating_count
        );

        $ratingAvg = $ratingCount > 0
            ? round($ratingSum / $ratingCount, 2)
            : 0;


        $reputationScore = $this->calculateScore(
            $ratingAvg,
            $ratingCount,
            $reviewCount,
        );

        $commissionRate = app(CommissionService::class)
            ->resolveRate(
                $business,
                $reputationScore
            );

        BusinessReputation::query()->updateOrCreate(
            [
                'business_id' => $business->id,
            ],
            [
                'rating_avg' => $ratingAvg,
                'rating_count' => $ratingCount,
                'review_count' => $reviewCount,
                'reputation_score' => $reputationScore,
                'current_commission_rate' => $commissionRate,
                'last_calculated_at' => now(),
            ]
        );
    }

    protected function calculateScore(
        float $ratingAvg,
        int $ratingCount,
        int $reviewCount,
    ): float {
        // فعلاً الگوریتم ساده
        return $ratingAvg;
    }
}
