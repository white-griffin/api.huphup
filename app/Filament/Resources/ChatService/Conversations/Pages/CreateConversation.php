<?php

namespace App\Filament\Resources\ChatService\Conversations\Pages;

use App\Filament\Resources\ChatService\Conversations\ConversationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateConversation extends CreateRecord
{
    protected static string $resource = ConversationResource::class;
}
