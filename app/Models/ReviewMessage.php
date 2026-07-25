<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReviewMessage extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
            'approved_at' => 'datetime',
            'edited_at' => 'datetime',
        ];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ReviewStatus::APPROVED->value);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ReviewStatus::PENDING->value);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', ReviewStatus::REJECTED->value);
    }
}
