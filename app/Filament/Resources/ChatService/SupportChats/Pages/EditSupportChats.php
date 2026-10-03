<?php

namespace App\Filament\Resources\ChatService\SupportChats\Pages;

use App\Filament\Resources\ChatService\SupportChats\SupportChatsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSupportChats extends EditRecord
{
    protected static string $resource = SupportChatsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
