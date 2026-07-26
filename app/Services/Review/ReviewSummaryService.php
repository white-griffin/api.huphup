<?php

namespace App\Services\Review;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\ReviewSummary;

class ReviewSummaryService
{

    public function refresh(Review $review): void
    {
        $reviewable = $review->reviewable;

        $approvedReviews = $reviewable->reviews()
            ->where('status', ReviewStatus::APPROVED->value)
            ->get();

        $summary = ReviewSummary::query()->firstOrCreate([
            'reviewable_type' => $reviewable->getMorphClass(),
            'reviewable_id'   => $reviewable->getKey(),
        ]);

        $summary->update([
            'reviews_count'  => $approvedReviews->count(),

            'ratings_count'  => $approvedReviews
                ->whereNotNull('rating')
                ->count(),

            'average_rating' => round(
                $approvedReviews->whereNotNull('rating')->avg('rating') ?? 0,
                2
            ),

            'one_star' => $approvedReviews->where('rating', 1)->count(),

            'two_star' => $approvedReviews->where('rating', 2)->count(),

            'three_star' => $approvedReviews->where('rating', 3)->count(),

            'four_star' => $approvedReviews->where('rating', 4)->count(),

            'five_star' => $approvedReviews->where('rating', 5)->count(),

            'last_review_at' => $approvedReviews->max('created_at'),
        ]);
    }

}
