<?php

namespace App\Services\Review\Guards;

use App\Contracts\Reviewable;
use App\Exceptions\Review\AlreadyReviewedException;
use App\Exceptions\Review\RatingNotAllowedException;
use App\Exceptions\Review\ReviewNotAllowedException;
use App\Models\Review;
use App\Models\User;

class ReviewGuard
{
    public function ensureCanReview(
        User $user,
        Reviewable $reviewable,
    ): void {
        if (! $reviewable->canUserReview($user)) {
            throw new ReviewNotAllowedException();
        }
    }

    public function ensureNotReviewed(
        User $user,
        Reviewable $reviewable,
    ): void {
        $alreadyReviewed = Review::query()
            ->where('user_id', $user->id)
            ->whereMorphedTo('reviewable', $reviewable)
            ->exists();

        if ($alreadyReviewed) {
            throw new AlreadyReviewedException();
        }
    }

    public function ensureRatingAllowed(
        Reviewable $reviewable,
        ?int $rating,
    ): void {
        if ($rating !== null && ! $reviewable->canBeRated()) {
            throw new RatingNotAllowedException();
        }
    }

    public function isVerifiedPurchase(
        User $user,
        Reviewable $reviewable,
    ): bool {
        return $reviewable->isVerifiedPurchase($user);
    }
}
