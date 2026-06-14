<?php

namespace App\Filament\Resources\Groups\Pages;

use App\Enums\AccessStatuses;
use App\Filament\Resources\Groups\GroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGroup extends CreateRecord
{
    protected static string $resource = GroupResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = AccessStatuses::PUBLIC->value;
        $data['created_by'] = auth()->id();
        return $data;
    }
}
