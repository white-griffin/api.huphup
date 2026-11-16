<?php

namespace App\Filament\Resources\Businesses\Pages;

use App\Filament\Resources\Businesses\BusinessResource;
use App\Services\Referral\ReferralCodeService;
use Filament\Resources\Pages\CreateRecord;

class CreateBusiness extends CreateRecord
{
    protected static string $resource = BusinessResource::class;

    protected function afterCreate(): void
    {
        app(ReferralCodeService::class)->generate($this->record);
    }
}
