<?php

namespace App\Http\Resources\V1\Provider\Orders;

use App\Enums\ShipmentProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => ShipmentProvider::label((string)$this->provider),
            'provider_order_id' => $this->provider_order_id,
            'tracking_code' => $this->tracking_code,
            'status' => $this->status,
            'events' => ShipmentEventResource::collection($this->whenLoaded('events')),
        ];
    }
}
