<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Enums\ActivityStatus;
use App\Enums\CouponTypes;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('کد')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('نوع')
                    ->formatStateUsing(fn ($state) => CouponTypes::label($state))
                    ->sortable(),

                TextColumn::make('value')
                    ->label('مقدار')
                    ->sortable(),

                TextColumn::make('used_count')
                    ->label('تعداد استفاده')
                    ->sortable(),

                TextColumn::make('usage_limit')
                    ->label('سقف استفاده'),

                TextColumn::make('activity_status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn ($state) => ActivityStatus::label((string) $state) ?? '—')
                    ->badge()
                    ->colors([
                        'success'   => fn ($record) => (string) $record->activity_status === ActivityStatus::ACTIVE->value,
                        'danger' => fn ($record) => (string) $record->activity_status === ActivityStatus::INACTIVE->value,
                    ]),

                TextColumn::make('starts_at')
                    ->label('شروع')
                    ->dateTime('Y/m/d H:i'),

                TextColumn::make('ends_at')
                    ->label('پایان')
                    ->dateTime('Y/m/d H:i'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])->recordActionsColumnLabel('عملیات')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
