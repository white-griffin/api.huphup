<?php

namespace App\Filament\Resources\ChatService\SupportChats\Pages;

use App\Filament\Resources\ChatService\SupportChats\SupportChatsResource;
use App\Services\MongoChatService\ChatMessageService;
use App\Services\MongoChatService\ChatUserResolver;
use Filament\Resources\Pages\ViewRecord;
use App\Models\MongoDB\ChatUser;
use App\Models\MongoDB\Message;
use MongoDB\BSON\ObjectId;
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
            ->with('sender')
            ->orderBy('createdAt')
            ->get();

        $replyIds = $messages
            ->map(fn (Message $message) => $message->getAttribute('replyTo'))
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
                ->with('sender')
                ->get()
                ->keyBy(
                    fn (Message $message) => (string) $message->id
                );
        }

        $chatUsers = $messages
            ->pluck('sender')
            ->merge($replies->pluck('sender'))
            ->filter()
            ->unique(
                fn ($chatUser) =>
                    $chatUser->externalType . ':' . $chatUser->externalId
            )
            ->values();

        $users = $chatUserResolver->resolveMany($chatUsers);

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

        return [
            'messages' => $messages,
            'replies' => $replies,
            'users' => $users,
            'currentAdminChatUserId' => $currentAdminChatUserId,
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
