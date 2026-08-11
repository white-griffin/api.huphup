<?php

namespace App\Http\Resources\V1\Provider\Orders;

use App\Models\OrderVendor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrderVendor */
class OrderVendorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'vendor_id' => $this->id,
            'order_number' => $this->order->order_number,
            'order_status' => $this->order->order_status,
            'payment_status' => $this->order->payment_status,
            'shipping_address'=> $this->order->shipping_address,
            'shipping_postal_code'=> $this->order->shipping_postal_code,
            'shipping_latitude'=> $this->order->shipping_latitude,
            'shipping_longitude'=> $this->order->shipping_longitude,
            'notes'=> $this->order->notes,
            'created_at' => $this->created_at,
            'subtotal_amount' => $this->subtotal_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'items' => OrderItemsResource::collection($this->whenLoaded('items')),
            'shipments' => ShipmentResource::collection($this->whenLoaded('shipments')),
        ];
    }
}
