<?php

namespace App\Models\Traits;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasWallet
{
    public function wallet(): MorphOne
    {
        return $this->morphOne(Wallet::class, 'owner');
    }

    public function getWallet(): \Illuminate\Database\Eloquent\Model
    {
        return $this->wallet()->firstOrCreate();
    }
}
