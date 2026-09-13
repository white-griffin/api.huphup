<?php

namespace App\Filament\Resources\LogisticsPayments\Pages;

use App\Filament\Resources\LogisticsPayments\LogisticsPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditLogisticsPayment extends EditRecord
{
    protected static string $resource = LogisticsPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
