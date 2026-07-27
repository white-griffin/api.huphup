<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphOne;

interface ReviewSource
{
    public function getReviewable(): Reviewable;

    public function getReviewAuthor(): User;

    public function canCreateReview(): bool;
    public function review(): MorphOne;
}
