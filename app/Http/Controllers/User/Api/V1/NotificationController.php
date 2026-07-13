<?php

namespace App\Http\Controllers\User\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return NotificationResource::collection(
            $request->user()
                ->notifications()
                ->latest()
                ->paginate()
        );
    }

    public function markAsRead(string $id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->noContent();
    }
}
