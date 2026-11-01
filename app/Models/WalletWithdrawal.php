<?php

namespace App\Models;

use App\Enums\WalletWithdrawalStatuses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class WalletWithdrawal extends Model
{
    protected $guarded = [ ];

    protected function casts(): array
    {
        return [
            'status' => WalletWithdrawalStatuses::class,
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function walletTransaction(): morphMany
    {
        return $this->morphMany(WalletTransaction::class, 'source');
    }
}
