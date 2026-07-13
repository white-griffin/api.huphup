<?php

namespace App\Http\Resources\V1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->data['type'],
            'title' => $this->data['title'],
            'body' => $this->data['body'],
            'action' => $this->data['action'],
            'is_read' => ! is_null($this->read_at),
            'created_at' => $this->created_at,
        ];
    }
}
