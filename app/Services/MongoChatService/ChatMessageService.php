<?php

namespace App\Services\MongoChatService;

use App\Models\Admin;
use App\Models\MongoDB\ChatUser;
use App\Models\MongoDB\Conversation;
use App\Models\MongoDB\Message;

class ChatMessageService
{
    public function send(
        Conversation $conversation,
        Admin $admin,
        string $content,
    ): Message {
        $content = trim($content);

        if ($content === '') {
            throw new \InvalidArgumentException(
                'Message content cannot be empty.'
            );
        }

        if ($conversation->context !== 'SUPPORT') {
            abort(403);
        }

        if ($conversation->status !== 'OPEN') {
            abort(422, 'This support conversation is closed.');
        }

        $chatUser = ChatUser::query()->firstOrCreate(
            [
                'externalType' => 'ADMIN',
                'externalId' => (string) $admin->id,
            ],
            [
                'nickname' => 'ADMIN=' . $admin->id,
            ]
        );

        if (
            (string) $conversation->assignedTo !==
            (string) $chatUser->id
        ) {
            abort(403);
        }

        return Message::query()->create([
            'conversationId' => $conversation->id,
            'senderId' => $chatUser->id,
            'type' => 'TEXT',
            'content' => $content,
            'replyTo' => null,
            'readBy' => [],
            'editedAt' => null,
            'deletedAt' => null,
        ]);
    }
}
