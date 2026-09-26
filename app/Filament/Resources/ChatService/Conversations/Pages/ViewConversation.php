<?php

namespace App\Filament\Resources\ChatService\Conversations\Pages;


use App\Filament\Resources\ChatService\Conversations\ConversationResource;
use App\Models\MongoDB\ConversationMember;
use App\Models\MongoDB\Message;
use App\Models\User;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use MongoDB\BSON\ObjectId;
use App\Services\MongoChatService\ChatUserResolver;
class ViewConversation extends ViewRecord
{
    protected static string $resource = ConversationResource::class;

    protected string $view =
        'filament.resources.chat-service.conversations.pages.view-conversation';

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

        $members = ConversationMember::query()
            ->where('conversationId', $conversationId)
            ->with('user')
            ->orderBy('joinedAt')
            ->get();

        $chatUsers = $messages
            ->pluck('sender')
            ->merge($replies->pluck('sender'))
            ->merge($members->pluck('user'))
            ->filter()
            ->unique(
                fn ($chatUser) =>
                    $chatUser->externalType . ':' . $chatUser->externalId
            )
            ->values();

        $users = $chatUserResolver->resolveMany($chatUsers);

        return [
            'messages' => $messages,
            'replies' => $replies,
            'members' => $members,
            'users' => $users,
        ];
    }

    protected function getHeaderWidgetsData(): array
    {
        return [];
    }

    protected function getInfolistData(): array
    {
        $creator = $this->record->creator;

        $creatorUser = null;

        if ($creator?->externalType === 'USER') {
            $creatorUser = \App\Models\User::query()
                ->find((int) $creator->externalId);
        }

        return [
            'creator_name' => $creatorUser
                ? trim($creatorUser->first_name . ' ' . $creatorUser->last_name)
                : ($creator?->nickname ?? '-'),

            'creator_mobile' => $creatorUser?->mobile,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

}
