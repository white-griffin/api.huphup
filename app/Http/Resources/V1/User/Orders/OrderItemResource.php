<?php

namespace App\Http\Resources\V1\User\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product_slug' => $this->product->slug,
            'product_name' => $this->product->name,
            'price' => $this->variation->price,
            'discount_price' => $this->variation->discount_price,
            'quantity' => $this->quantity,
        ];
    }
}
