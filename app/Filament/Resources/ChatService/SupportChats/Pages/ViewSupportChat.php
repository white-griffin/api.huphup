<?php

namespace App\Filament\Resources\ChatService\SupportChats\Pages;

use App\Filament\Resources\ChatService\SupportChats\SupportChatsResource;
use App\Services\MongoChatService\ChatMessageService;
use App\Services\MongoChatService\ChatUserResolver;
use Filament\Resources\Pages\ViewRecord;
use App\Models\MongoDB\ChatUser;
use App\Models\MongoDB\Message;
use MongoDB\BSON\ObjectId;
use Morilog\Jalali\Jalalian;

class ViewSupportChat extends ViewRecord
{
    protected static string $resource = SupportChatsResource::class;

    protected string $view =
        'filament.resources.chat-service.support-chats.pages.view-support-chat';

    public string $content = '';

    protected function getViewData(): array
    {
        $chatUserResolver = app(ChatUserResolver::class);

        $conversationId = new ObjectId(
            (string) $this->record->id
        );

        $messages = Message::query()
            ->where('conversationId', $conversationId)
            ->orderBy('createdAt')
            ->get();

        /*
         * Reply messages
         */
        $replyIds = $messages
            ->map(fn (Message $message) =>
            $message->getAttribute('replyTo')
            )
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        $replies = collect();

        if ($replyIds->isNotEmpty()) {
            $replies = Message::query()
                ->whereIn(
                    '_id',
                    $replyIds
                        ->map(fn (string $id) => new ObjectId($id))
                        ->all()
                )
                ->get()
                ->keyBy(
                    fn (Message $message) =>
                    (string) $message->id
                );
        }

        /*
         * همه sender ها را مستقیم از Mongo می‌گیریم
         */
        $senderIds = $messages
            ->pluck('senderId')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        $chatUsers = collect();

        if ($senderIds->isNotEmpty()) {
            $chatUsers = ChatUser::query()
                ->whereIn(
                    '_id',
                    $senderIds
                        ->map(fn (string $id) => new ObjectId($id))
                        ->all()
                )
                ->get();
        }

        /*
         * USER های واقعی MySQL
         */
        $users = $chatUserResolver->resolveMany($chatUsers);

        /*
         * Admin فعلی
         */
        $admin = auth('admin')->user();

        $currentAdminChatUserId = null;

        if ($admin) {
            $currentAdminChatUserId = ChatUser::query()
                ->where('externalType', 'ADMIN')
                ->where('externalId', (string) $admin->id)
                ->value('_id');

            $currentAdminChatUserId = $currentAdminChatUserId
                ? (string) $currentAdminChatUserId
                : null;
        }

        /*
         * آماده‌سازی پیام‌ها برای Alpine
         */
        $chatMessages = $messages
            ->map(function (Message $message) use (
                $chatUsers,
                $users
            ) {
                $senderId = (string) $message->senderId;

                $sender = $chatUsers->first(
                    fn (ChatUser $chatUser) =>
                        (string) $chatUser->id === $senderId
                );

                $mysqlUser = null;

                if (
                    $sender &&
                    $sender->externalType === 'USER'
                ) {
                    $mysqlUser = $users[
                    (int) $sender->externalId
                    ] ?? null;
                }

                if ($mysqlUser) {
                    $senderName = trim(
                        $mysqlUser->first_name . ' ' .
                        $mysqlUser->last_name
                    );

                    if ($senderName === '') {
                        $senderName = 'User';
                    }

                    $avatarUrl = $mysqlUser->avatar_url;

                    $firstName = trim(
                        (string) $mysqlUser->first_name
                    );

                    $lastName = trim(
                        (string) $mysqlUser->last_name
                    );

                    $initials = mb_strtoupper(
                        mb_substr($firstName, 0, 1) .
                        mb_substr($lastName, 0, 1)
                    );

                    if ($initials === '') {
                        $initials = 'U';
                    }
                } else {
                    $senderName = $sender?->nickname ?? 'Unknown';
                    $avatarUrl = null;
                    $initials = mb_strtoupper(
                        mb_substr($senderName, 0, 1)
                    ) ?: 'U';
                }


                return [
                    'id' => $senderId === ''
                        ? (string) $message->id
                        : (string) $message->id,

                    'conversationId' => (string) $message->conversationId,

                    'senderId' => $senderId,

                    'senderName' => $senderName,

                    'senderAvatar' => $avatarUrl,

                    'senderInitials' => $initials,

                    'type' => $message->type,

                    'content' => $message->content,

                    'createdAt' => Jalalian::fromDateTime($message->createdAt?->toISOString())->format('yyyy/MM/dd HH:mm')
                ];
            })
            ->values()
            ->all();

        $senderMeta = collect($chatMessages)
            ->mapWithKeys(function (array $message) {
                return [
                    $message['senderId'] => [
                        'name' => $message['senderName'],
                        'avatar' => $message['senderAvatar'],
                        'initials' => $message['senderInitials'],
                    ],
                ];
            })
            ->all();

        return [
            'messages' => $messages,
            'replies' => $replies,
            'users' => $users,
            'currentAdminChatUserId' => $currentAdminChatUserId,
            'chatMessages' => $chatMessages,
            'senderMeta' => $senderMeta,
        ];
    }

    public function sendMessage(): void
    {
        $admin = auth('admin')->user();

        if (! $admin) {
            abort(403);
        }

        app(ChatMessageService::class)->send(
            conversation: $this->record,
            admin: $admin,
            content: $this->content,
        );

        $this->content = '';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
