<?php

namespace App\Http\Resources\V1\User\User;

use App\Models\UserNotice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserNotice */
class UserNoticeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'created_at' => $this->created_at,
        ];
    }
}
