<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Wallet extends Model
{
    protected $guarded = ['id'];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(
            WalletCharge::class,
            'user_id',
            'owner_id'
        )->whereHas('user.wallet', function (Builder $query) {
            $query->whereKey($this->getKey());
        });
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(
            WalletWithdrawal::class,
            'business_id',
            'owner_id'
        )->whereHas('business.wallet', function (Builder $query) {
            $query->whereKey($this->getKey());
        });
    }
}
