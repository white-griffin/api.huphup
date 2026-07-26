<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReviewSummary extends Model
{
    protected $guarded = [];

    protected $casts = [
        'average_rating' => 'float',
        'last_review_at' => 'datetime',
    ];

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }
}
