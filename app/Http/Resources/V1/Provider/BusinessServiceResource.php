<?php

namespace App\Http\Resources\V1\Provider;

use App\Http\Resources\V1\User\Business\BusinessResource;
use App\Http\Resources\V1\User\ReviewResource;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Service */
class BusinessServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->service->name,
            'description' => $this->service->description,
            'icon' => $this->service->icon,
            'image' => $this->service->image_url,
        ];
    }
}
