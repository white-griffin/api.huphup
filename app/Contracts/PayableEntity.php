<?php

namespace App\Contracts;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface PayableEntity
{
    public function payments(): MorphMany;

    public function getPayableAmount(): int;

    public function getPayableUser(): User;

    public function getPayableUserId(): int;

}
