<?php

namespace App\Http\Controllers\User\Api\V1\ChatService;

use App\Helpers\Api\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\MongoChatService\SupportChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function start(
        Request            $request,
        SupportChatService $supportChatService,
    ): JsonResponse
    {
        $conversation = $supportChatService->start(
            $request->user()
        );

        return ApiResponse::Success('عملیات موفق', [
            'conversation_id' => (string)$conversation->id,
            'type' => $conversation->type,
            'context' => $conversation->context,
            'status' => $conversation->status,
            'assigned_to' => (string)$conversation->assignedTo,
        ]);
    }
}
