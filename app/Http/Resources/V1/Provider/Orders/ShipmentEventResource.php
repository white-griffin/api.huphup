<?php

namespace App\Http\Resources\V1\Provider\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->status,
            'created_at' => $this->created_at
        ];
    }
}
