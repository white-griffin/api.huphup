<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatuses;
use App\Enums\PaymentStatuses;
use App\Filament\Resources\Orders\OrdersResource;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.fullName')
                    ->label('کاربر')
                    ->sortable(),
                TextColumn::make('order_number')
                    ->label('شناسه سفارش')
                    ->url(
                        fn($record) => OrdersResource::getUrl('view', [
                            'record' => $record,
                        ])
                    )
                    ->searchable(),
                TextColumn::make('subtotal_amount')
                    ->label('مبلغ کل')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' تومان')
                    ->searchable(),
                TextColumn::make('discount_amount')
                    ->label('تخفیف')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' تومان')
                    ->searchable(),
                TextColumn::make('shipping_amount')
                    ->label('هزینه ارسال')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' تومان')
                    ->searchable(),
                TextColumn::make('total_amount')
                    ->label('مبلغ پرداختی')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' تومان')
                    ->searchable(),
                TextColumn::make('order_status')
                    ->label('وضعیت سفارش')
                    ->formatStateUsing(fn($state) => OrderStatuses::label((string)$state) ?? '—')
                    ->searchable(),
                TextColumn::make('payment_status')
                    ->label('وضعیت پرداخت')
                    ->formatStateUsing(fn($state) => PaymentStatuses::label((string)$state) ?? '—')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d') : null),
            ])
            ->filters([
                //
            ])
            ->recordActions([

            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ]);
    }
}
