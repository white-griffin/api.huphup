<?php

namespace App\Filament\Resources\FcmNotifications\Pages;

use App\Filament\Resources\FcmNotifications\FcmNotificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFcmNotifications extends ListRecords
{
    protected static string $resource = FcmNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
