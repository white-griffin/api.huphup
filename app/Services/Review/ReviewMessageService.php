<?php

namespace App\Services\Review;

use App\Enums\ReviewStatus;
use App\Models\Business;
use App\Models\Review;
use App\Models\ReviewMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReviewMessageService
{
    public function create(
        Review $review,
        Model $author,
        string $body,
        ?Business $business = null,
        ?ReviewMessage $parent = null,
    ): ReviewMessage {
        return $this->store(
            review: $review,
            author: $author,
            body: $body,
            business: $business,
            parent: $parent,
        );
    }

    private function store(
        Review $review,
        Model $author,
        string $body,
        ?Business $business,
        ?ReviewMessage $parent,
    ): ReviewMessage {
        return DB::transaction(function () use (
            $review,
            $author,
            $body,
            $business,
            $parent,
        ) {
            $message = new ReviewMessage([
                'body' => $body,
                'status' => ReviewStatus::PENDING->value,
            ]);

            $message->review()->associate($review);
            $message->author()->associate($author);

            if ($parent) {
                $message->parent()->associate($parent);
            }

            if ($business) {
                $message->business()->associate($business);
            }

            $message->save();

            return $message;
        });
    }

//    public function update(): ReviewMessage
//    {
//    }
//
//    public function delete(): void
//    {
//    }
}
