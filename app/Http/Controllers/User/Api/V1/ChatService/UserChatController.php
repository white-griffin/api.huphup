<?php

namespace App\Http\Controllers\User\Api\V1\ChatService;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;

class UserChatController extends Controller
{
    public function resolve(Request $request)
    {
        if (
            !$request->header('X-Chat-Service-Secret') ||
            !hash_equals(
                (string) config('services.chat.secret'),
                (string) $request->header('X-Chat-Service-Secret')
            )
        ) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $data = $request->validate([
            'externalType' => 'required|in:USER,ADMIN,BUSINESS',
            'externalId' => 'required|string',
        ]);

        $model = match ($data['externalType']) {
            'USER' => User::class,
            'ADMIN' => Admin::class,
            'BUSINESS' => Business::class,
        };

        $user = $model::find($data['externalId']);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $nickname = $user->getAttribute('name')
            ?? $user->getAttribute('fullName')
            ?? $data['externalType'] . '=' . $user->getKey();

        return response()->json([
            'externalType' => $data['externalType'],
            'externalId' => (string) $user->getKey(),
            'nickname' => (string) $nickname,
        ]);
    }
}
