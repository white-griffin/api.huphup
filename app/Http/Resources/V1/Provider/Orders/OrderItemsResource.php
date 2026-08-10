<?php

namespace App\Http\Resources\V1\Provider\Orders;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrderItem */
class OrderItemsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product_slug' => $this->product?->slug,
            'product_name' => $this->product?->name,
            'price' => $this->variation->price,
            'discount_price' => $this->variation->discount_price,
            'quantity' => $this->quantity,
        ];
    }
}
