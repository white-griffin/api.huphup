<?php

namespace App\Filament\Resources\ChatService\SupportChats\Pages;

use App\Filament\Resources\ChatService\SupportChats\SupportChatsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupportChats extends ListRecords
{
    protected static string $resource = SupportChatsResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            CreateAction::make(),
        ];
    }
}
