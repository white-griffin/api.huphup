<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use App\Enums\BannerPlacement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
class Banner extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }


    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activity_status', ActivityStatus::ACTIVE->value);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->active()
            ->where(function (Builder $query) {
                $query
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeForPlacement(
        Builder $query,
        BannerPlacement $placement,
    ): Builder {
        return $query->where('placement', $placement->value);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }
}
