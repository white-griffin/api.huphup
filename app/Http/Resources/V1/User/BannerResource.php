<?php

namespace App\Http\Resources\V1\User;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
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
            $type = match ($this->target_type) {
                Product::class => 'product',
                Business::class => 'business',
                Category::class => 'category',
                default => null,
            };

            if ($type === null) {
                return null;
            }

            return [
                'type' => $type,
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
