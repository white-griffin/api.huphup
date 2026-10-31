<?php

namespace App\Http\Resources\V1\User\Wallet;

use App\Models\WalletCharge;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WalletCharge */
class WalletChargeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'status' => $this->status,

            'payment' => [
                'id' => $this->payments->first()?->id,
                'gateway' => $this->payments->first()?->gateway,
                'payment_status' => $this->payments->first()?->payment_status,
            ],

            'created_at' => $this->created_at,
        ];
    }
}
