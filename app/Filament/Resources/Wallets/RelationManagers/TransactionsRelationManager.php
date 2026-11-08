<?php

namespace App\Filament\Resources\Wallets\RelationManagers;

use App\Enums\WalletTransactionType;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'تراکنش‌ها';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('شناسه')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('نوع تراکنش')
                    ->badge()
                    ->formatStateUsing(fn ($state) => WalletTransactionType::label($state->value) ?? '_'),

                TextColumn::make('amount')
                    ->label('مبلغ')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' تومان'),

                TextColumn::make('available_before')
                    ->label('موجودی قبل')
                    ->numeric(decimalPlaces: 0),

                TextColumn::make('available_after')
                    ->label('موجودی بعد')
                    ->numeric(decimalPlaces: 0),

                TextColumn::make('description')
                    ->label('توضیحات')
                    ->limit(40),

                TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : null)
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
