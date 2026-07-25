<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\BusinessService;
use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewPolicy
{
    public function update(
        User $user,
        Review $review,
    ): bool {
        return $review->user_id === $user->id
            && $review->status->isPending();
    }

    public function delete(
        User $user,
        Review $review,
    ): bool {
        return $review->user_id === $user->id;
    }

    public function reply(
        User $user,
        Review $review
    ): bool {
        $business = $user->business;

        if (!$business) {
            return false;
        }

        return $review->reviewable_type === BusinessService::class
            && $review->reviewable->business_id === $business->id;
    }
}
