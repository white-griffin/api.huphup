<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

// Private channel برای چت دو نفره و گروه (user-based auth)
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    return Conversation::query()
        ->where('id', $conversationId)
        ->whereHas('participants', fn($q) => $q->where('user_id', $user->id))
        ->exists();
});
