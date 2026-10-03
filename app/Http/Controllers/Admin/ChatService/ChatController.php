<?php

namespace App\Http\Controllers\Admin\ChatService;

use App\Http\Controllers\Controller;
use App\Services\Chat\ChatTokenService;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function __invoke(
        ChatTokenService $chatTokenService,
    ): JsonResponse {
        $admin = auth('admin')->user();

        abort_unless($admin, 403);

        return response()->json([
            'token' => $chatTokenService->getAdminToken($admin),
        ]);
    }
}
