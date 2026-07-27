<?php

namespace App\Services\Review;

use App\Contracts\Reviewable;
use App\Contracts\ReviewSource;
use App\Enums\ReviewStatus;
use App\Exceptions\Review\AlreadyReviewedException;
use App\Exceptions\Review\InvalidReviewException;
use App\Exceptions\Review\RatingNotAllowedException;
use App\Exceptions\Review\ReviewNotAllowedException;
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

    /**
     * @throws InvalidReviewException
     */
    public function create(
        ReviewSource $source,
        array $attributes
    ): Review {
        $rating = $attributes['rating'] ?? null;
        $title = $attributes['title'] ?? null;
        $body = $attributes['body'] ?? null;

        $reviewable = $source->getReviewable();

        $user = $source->getReviewAuthor();

        $this->validate(
            $user,
            $reviewable,
            $source,
            $rating,
            $body,
        );


        return $this->store(
            user: $user,
            reviewable: $reviewable,
            source: $source,
            rating: $rating,
            title: $title,
            body: $body,
        );
    }

    /**
     * @throws ReviewNotAllowedException
     * @throws AlreadyReviewedException
     * @throws InvalidReviewException
     * @throws RatingNotAllowedException
     */
    private function validate(
        User $user,
        Reviewable $reviewable,
        ReviewSource $source,
        ?int $rating,
        ?string $body,
    ): void {
        if (blank($rating) && blank($body)) {
            throw new InvalidReviewException();
        }

        $this->guard->ensureCanReview($user, $reviewable);
        $this->guard->ensureCanCreate($source);
        $this->guard->ensureRatingAllowed($reviewable, $rating);
    }

    private function store(
        User $user,
        Reviewable $reviewable,
        ReviewSource $source,
        ?int $rating,
        ?string $title,
        ?string $body,
    ): Review {
        return DB::transaction(function () use (
            $user,
            $reviewable,
            $source,
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
            $review->reviewable()->associate($reviewable);
            $review->source()->associate($source);
            if ($business = $reviewable->getBusiness()) {
                $review->business()->associate($business);
            }
            $review->save();

            return $review;
        });
    }
}
