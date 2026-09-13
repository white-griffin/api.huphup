<?php

namespace App\Filament\Resources\LogisticsPayments\Pages;

use App\Filament\Resources\LogisticsPayments\LogisticsPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLogisticsPayments extends ListRecords
{
    protected static string $resource = LogisticsPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
