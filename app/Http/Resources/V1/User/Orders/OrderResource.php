<?php

namespace App\Http\Resources\V1\User\Orders;

use App\Enums\PaymentStatuses;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Order */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->id,
            'order_number' => $this->order_number,
            'total_amount' => $this->total_amount,
            'discount_amount' => $this->discount_amount,
            'order_status' => $this->order_status,
            'shipping' => [
                'address'=> $this->shipping_address,
                'postal_code'=> $this->shipping_postal_code,
                'latitude'=> $this->shipping_latitude,
                'longitude'=> $this->shipping_longitude,
            ],
            'payment' => [
                'status' => $this->payment_status,
                'transaction_id' => $this->payments
                    ->where('payment_status', PaymentStatuses::PAID->value)
                    ->sortByDesc('id')
                    ->first()?->transaction_id,
            ],
            'notes'=> $this->notes,
            'created_at' => $this->created_at,
            'vendors' => OrderVendorResource::collection($this->whenLoaded('vendors')),

        ];
    }
}
