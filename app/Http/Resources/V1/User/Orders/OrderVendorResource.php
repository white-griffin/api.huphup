<?php

namespace App\Http\Resources\V1\User\Orders;

use App\Models\OrderVendor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrderVendor */
class OrderVendorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'business_name' => $this->business->name,
            'subtotal_amount' => $this->subtotal_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
