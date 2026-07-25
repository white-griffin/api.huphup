<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessReputation extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'rating_avg' => 'float',
            'current_commission_rate' => 'float',
            'last_calculated_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
