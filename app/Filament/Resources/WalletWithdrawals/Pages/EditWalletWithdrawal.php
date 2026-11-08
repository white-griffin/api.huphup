<?php

namespace App\Filament\Resources\WalletWithdrawals\Pages;

use App\Filament\Resources\WalletWithdrawals\WalletWithdrawalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWalletWithdrawal extends EditRecord
{
    protected static string $resource = WalletWithdrawalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
