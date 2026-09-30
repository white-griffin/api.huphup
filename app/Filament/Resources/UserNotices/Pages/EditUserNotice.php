<?php

namespace App\Filament\Resources\UserNotices\Pages;

use App\Filament\Resources\UserNotices\UserNoticeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditUserNotice extends EditRecord
{
    protected static string $resource = UserNoticeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
