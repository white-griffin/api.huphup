<?php

namespace App\Http\Resources\V1\User\Orders;

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
            'payment_status' => $this->payment_status,
            'shipping_address'=> $this->shipping_address,
            'shipping_postal_code'=> $this->shipping_postal_code,
            'shipping_latitude'=> $this->shipping_latitude,
            'shipping_longitude'=> $this->shipping_longitude,
            'notes'=> $this->notes,
            'created_at' => $this->created_at,
            'vendors' => OrderVendorResource::collection($this->whenLoaded('vendors')),
            'transaction_id' => $this->whenLoaded('payments')->last()?->transaction_id,
        ];
    }
}
