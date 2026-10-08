<?php

namespace App\Http\Resources\V1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'images' => $this->images,
            'action' => $this->resolveAction(),
        ];
    }

    private function resolveAction(): ?array
    {
        if ($this->target_type && $this->target_id) {
            return [
                'type' => $this->target_type,
                'id' => $this->target_id,
            ];
        }

        if ($this->target_url) {
            return [
                'type' => 'url',
                'url' => $this->target_url,
            ];
        }

        return null;
    }
}
