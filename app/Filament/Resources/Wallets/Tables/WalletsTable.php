<?php

namespace App\Filament\Resources\Wallets\Tables;

use App\Models\User;
use App\Models\Wallet;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WalletsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('owner'))
            ->columns([
                TextColumn::make('id')
                    ->label('شناسه')
                    ->sortable(),

                TextColumn::make('owner_id')
                    ->label('مالک')
                    ->formatStateUsing(function ($state, Wallet $record) {
                        $owner = $record->owner;

                        return $owner?->name
                            ?? $owner?->fullName
                            ?? $owner?->phone
                            ?? "شناسه {$state}";
                    }),

                TextColumn::make('owner_type')
                    ->label('نوع مالک')
                    ->formatStateUsing(fn ($state) =>
                    $state === (new User())->getMorphClass()
                        ? 'کاربر'
                        : 'کسب‌وکار'
                    ),

                TextColumn::make('available_balance')
                    ->label('موجودی قابل برداشت')
                    ->formatStateUsing(fn ($state) => number_format($state) . ' تومان')
                    ->sortable(),

                TextColumn::make('pending_balance')
                    ->label('موجودی در انتظار')
                    ->formatStateUsing(fn ($state) => number_format($state) . ' تومان')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
