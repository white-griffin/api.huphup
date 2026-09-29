<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\ActivityStatus;
use App\Enums\OrderStatuses;
use App\Enums\PaymentStatuses;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                    ->searchable(),
                TextColumn::make('subtotal_amount')
                    ->label('مبلغ کل')
                    ->searchable(),
                TextColumn::make('shipping_amount')
                    ->label('هزینه ارسال')
                    ->searchable(),
                TextColumn::make('discount_amount')
                    ->label('تخفیف')
                    ->searchable(),
                TextColumn::make('total_amount')
                    ->label('مبلغ پرداختی')
                    ->searchable(),
                TextColumn::make('order_status')
                    ->label('وضعیت سفارش')
                    ->formatStateUsing(fn ($state) => OrderStatuses::label((string) $state) ?? '—')
                    ->searchable(),
                TextColumn::make('payment_status')
                    ->label('وضعیت پرداخت')
                    ->formatStateUsing(fn ($state) => PaymentStatuses::label((string) $state) ?? '—')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->formatStateUsing(fn ($state) =>
                    $state ? Jalalian::fromDateTime($state)->format('Y/m/d') : null
                    ),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])->recordActionsColumnLabel('عملیات')
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ]);
    }
}
