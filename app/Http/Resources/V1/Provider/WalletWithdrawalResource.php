<?php

namespace App\Http\Resources\V1\Provider;

use App\Models\WalletWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WalletWithdrawal */
class WalletWithdrawalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'payout_reference' => $this->payout_reference,
            'approved_at' => $this->approved_at,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
        ];
    }
}
