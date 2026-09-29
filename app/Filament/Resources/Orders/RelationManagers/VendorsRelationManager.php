<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Filament\Resources\OrderVendors\OrderVendorResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;

class VendorsRelationManager extends RelationManager
{
    protected static string $relationship = 'vendors';

    protected static ?string $title = 'فروشنده‌ها';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('شناسه')
                    ->sortable(),

                TextColumn::make('business.name')
                    ->label('فروشگاه')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subtotal_amount')
                    ->label('جمع سفارش')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' تومان'),

                TextColumn::make('discount_amount')
                    ->label('تخفیف')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' تومان'),

                TextColumn::make('shipping_amount')
                    ->label('هزینه ارسال')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' تومان'),

                TextColumn::make('total_amount')
                    ->label('مبلغ پرداخت‌شده')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' تومان')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : null),
            ])
            ->recordUrl(
                fn ($record) => OrderVendorResource::getUrl('view', [
                    'record' => $record,
                ])
            )
            ->openRecordUrlInNewTab()
            ->defaultSort('id', 'desc');
    }
}
