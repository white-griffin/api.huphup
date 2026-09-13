<?php

namespace App\Filament\Resources\LogisticsPayments\Tables;

use App\Enums\LogisticsPaymentStatuses;
use App\Enums\ShipmentProvider;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LogisticsPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('orderVendor.id')
                    ->label('شماره سفارش پذیرنده')
                    ->searchable(),
                TextColumn::make('shipment.id')
                    ->label('شماره مرسوله')
                    ->searchable(),
                TextColumn::make('provider')
                    ->label('ارسال کننده')
                    ->formatStateUsing(fn ($state) => ShipmentProvider::label($state))
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('مبلغ')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn ($state) => LogisticsPaymentStatuses::label($state))
                    ->badge()
                    ->sortable(),
                TextColumn::make('invoice_number')
                    ->label('شماره فاکتور پرداختی')
                    ->searchable(),
                TextColumn::make('paid_at')
                    ->label('تاریخ پرداخت')
                    ->jalaliDate('Y/m/d')
                    ->sortable(),
                TextColumn::make('paidBy.name')
                    ->label('پرداخت کننده')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])->recordActionsColumnLabel('عملیات')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
