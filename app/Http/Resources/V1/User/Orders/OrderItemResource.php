<?php

namespace App\Http\Resources\V1\User\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product' => [
                'slug' => $this->product->slug,
                'name' => $this->product->name,
                'image' => $this->product->images()->where('is_primary', 1)->first()->image_url
            ],
            'price' => $this->variation->price,
            'discount_price' => $this->variation->discount_price,
            'quantity' => $this->quantity,
        ];
    }
}
