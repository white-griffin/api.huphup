<?php

namespace App\Filament\Resources\Wallets\RelationManagers;

use App\Enums\WalletWithdrawalStatuses;
use App\Models\Business;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

class WithdrawalsRelationManager extends RelationManager
{
    protected static string $relationship = 'withdrawals';

    protected static ?string $title = 'درخواست‌های برداشت';

    public static function canViewForRecord(
        Model  $ownerRecord,
        string $pageClass
    ): bool {
        return $ownerRecord->owner instanceof Business;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('شناسه')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('مبلغ')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' تومان'),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn ($state) => WalletWithdrawalStatuses::label((string)$state->value) ?? '_'),

                TextColumn::make('rejection_reason')
                    ->label('دلیل رد')
                    ->limit(40),

                TextColumn::make('payout_reference')
                    ->label('شماره پیگیری'),

                TextColumn::make('created_at')
                    ->label('تاریخ درخواست')
                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : null)
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('تاریخ پرداخت')
                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : null),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
