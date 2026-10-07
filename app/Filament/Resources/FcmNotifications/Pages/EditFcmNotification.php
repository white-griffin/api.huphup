<?php

namespace App\Filament\Resources\FcmNotifications\Pages;

use App\Filament\Resources\FcmNotifications\FcmNotificationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFcmNotification extends EditRecord
{
    protected static string $resource = FcmNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
