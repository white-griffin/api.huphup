<?php

namespace App\Services\Review;

use App\Contracts\Reviewable;
use App\Enums\ReviewStatus;
use App\Exceptions\Review\InvalidReviewException;
use App\Models\Review;
use App\Models\User;
use App\Services\Review\Guards\ReviewGuard;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function __construct(
        private readonly ReviewGuard $guard,
    ) {
    }

    public function create(
        User $user,
        Reviewable $reviewable,
        array $attributes,
    ): Review {
        $rating = $attributes['rating'] ?? null;
        $title = $attributes['title'] ?? null;
        $body = $attributes['body'] ?? null;

        $this->validate($user, $reviewable, $rating, $body);

        return $this->store(
            user: $user,
            reviewable: $reviewable,
            rating: $rating,
            title: $title,
            body: $body,
        );
    }

    private function validate(
        User $user,
        Reviewable $reviewable,
        ?int $rating,
        ?string $body,
    ): void {
        if (blank($rating) && blank($body)) {
            throw new InvalidReviewException();
        }

        $this->guard->ensureCanReview($user, $reviewable);
        $this->guard->ensureNotReviewed($user, $reviewable);
        $this->guard->ensureRatingAllowed($reviewable, $rating);
    }

    private function store(
        User $user,
        Reviewable $reviewable,
        ?int $rating,
        ?string $title,
        ?string $body,
    ): Review {
        return DB::transaction(function () use (
            $user,
            $reviewable,
            $rating,
            $title,
            $body,
        ) {
            $review = new Review([
                'rating' => $rating,
                'title' => $title,
                'body' => $body,
                'status' => ReviewStatus::PENDING->value,
                'is_verified_purchase' => $this->guard->isVerifiedPurchase(
                    $user,
                    $reviewable,
                ),
            ]);

            $review->user()->associate($user);
            $review->business()->associate($reviewable->getBusiness());
            $review->reviewable()->associate($reviewable);

            $review->save();

            return $review;
        });
    }
}
