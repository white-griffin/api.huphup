<?php

namespace App\Filament\Resources\ChatService\Conversations\Pages;

use App\Filament\Resources\ChatService\Conversations\ConversationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditConversation extends EditRecord
{
    protected static string $resource = ConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
