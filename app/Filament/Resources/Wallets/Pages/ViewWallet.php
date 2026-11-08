<?php

namespace App\Filament\Resources\Wallets\Pages;

use App\Filament\Resources\Wallets\WalletResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewWallet extends ViewRecord
{
    protected static string $resource = WalletResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id')
                ->label('شناسه کیف پول'),

            TextEntry::make('owner.fullName')
                ->label('مالک'),

            TextEntry::make('available_balance')
                ->label('موجودی قابل برداشت')
                ->formatStateUsing(fn ($state) => number_format($state) . ' تومان'),

            TextEntry::make('pending_balance')
                ->label('موجودی در انتظار')
                ->formatStateUsing(fn ($state) => number_format($state) . ' تومان'),

            TextEntry::make('created_at')
                ->label('تاریخ ایجاد')
                ->dateTime('Y/m/d H:i'),
        ]);
    }


}
