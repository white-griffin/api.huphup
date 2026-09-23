<?php

namespace App\Filament\Resources\ChatService\Conversations\Pages;


use App\Filament\Resources\ChatService\Conversations\ConversationResource;
use App\Models\MongoDB\Message;
use Filament\Resources\Pages\ViewRecord;

class ViewConversation extends ViewRecord
{
    protected static string $resource = ConversationResource::class;

    protected string $view =
        'filament.resources.chat-service.conversations.pages.view-conversation';

    protected function getViewData(): array
    {
        $conversationId = new \MongoDB\BSON\ObjectId(
            (string) $this->record->id
        );

        $messages = Message::query()
            ->where('conversationId', $conversationId)
            ->with('sender')
            ->orderBy('createdAt')
            ->get();

        $replyIds = $messages
            ->map(fn ($message) => $message->getAttribute('replyTo'))
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        $replies = Message::query()
            ->whereIn(
                '_id',
                $replyIds->map(
                    fn ($id) => new \MongoDB\BSON\ObjectId($id)
                )->all()
            )
            ->with('sender')
            ->get()
            ->keyBy(fn ($message) => (string) $message->id);

        return [
            'messages' => $messages,
            'replies' => $replies,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

}
