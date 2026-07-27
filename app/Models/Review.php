<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use App\Services\Review\ReviewSummaryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => ReviewStatus::class,
            'is_verified_purchase' => 'boolean',
            'approved_at' => 'datetime',
            'edited_at' => 'datetime',
        ];
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


    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ReviewMessage::class);
    }

    public function summary(): MorphOne
    {
        return $this->morphOne(
            ReviewSummary::class,
            'reviewable'
        );
    }

    public function refreshSummary(): void
    {
        app(ReviewSummaryService::class)->refresh($this);
    }

    public function approve(): void
    {
        $this->update([
            'status' => ReviewStatus::APPROVED->value,
        ]);

        $this->refreshSummary();

    }

    public function reject(): void
    {
        $this->update([
            'status' => ReviewStatus::REJECTED->value,
        ]);

        $this->refreshSummary();
    }
}
