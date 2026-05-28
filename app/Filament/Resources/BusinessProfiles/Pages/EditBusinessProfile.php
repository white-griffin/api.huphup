<?php

namespace App\Filament\Resources\BusinessProfiles\Pages;

use App\Filament\Resources\BusinessProfiles\BusinessProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBusinessProfile extends EditRecord
{
    protected static string $resource = BusinessProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
