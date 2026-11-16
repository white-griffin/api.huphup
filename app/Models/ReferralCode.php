<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReferralCode extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'activity_status' => ActivityStatus::class,
        ];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
