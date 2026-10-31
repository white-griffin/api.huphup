<?php

namespace App\Http\Resources\V1\User\Wallet;

use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WalletTransaction */
class WalletTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,

            'available_before' => $this->available_before,
            'available_after' => $this->available_after,

            'pending_before' => $this->pending_before,
            'pending_after' => $this->pending_after,

            'description' => $this->description,
            'created_at' => $this->created_at,
        ];
    }
}
