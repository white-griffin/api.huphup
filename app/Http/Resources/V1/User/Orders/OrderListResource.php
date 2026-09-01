<?php

namespace App\Http\Resources\V1\User\Orders;

use App\Http\Resources\V1\Provider\Orders\OrderItemsResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Order */
class OrderListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'total_amount' => $this->total_amount,
            'discount_amount' => $this->discount_amount,
            'order_status' => $this->order_status,
            'created_at' => $this->created_at,
            'items' => [
                'count' => count($this->items),
                'images' => $this->getItemImages($this->whenLoaded('items'))
            ],
        ];
    }


    private function getItemImages($orderItems): array
    {
        $images = [];
        foreach ($orderItems as $item) {
            $images[] = $item->product->images()->where('is_primary', 1)->first()->image_url;
        }

        return $images;
    }
}
