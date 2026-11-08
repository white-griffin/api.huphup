<?php

namespace App\Filament\Resources\Wallets\RelationManagers;

use App\Enums\WalletChargeStatus;
use App\Models\User;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ChargesRelationManager extends RelationManager
{
    protected static string $relationship = 'charges';

    protected static ?string $title = 'شارژهای کیف پول';

    public static function canViewForRecord(
        Model  $ownerRecord,
        string $pageClass
    ): bool {
        return $ownerRecord->owner instanceof User;
    }


    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('شناسه')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('مبلغ شارژ')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' تومان'),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn ($state) => WalletChargeStatus::label((string)$state) ?? '_'),

                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
