<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Enums\ShipmentProvider;
use App\Enums\ShipmentStatuses;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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

                TextColumn::make('shipments')
                    ->label('ارسال‌ها')
                    ->state(fn ($record) => $record->shipments->isNotEmpty()
                        ? 'مشاهده ارسال‌ها'
                        : 'ارسال نشده'
                    )
                    ->color(fn ($record) => $record->shipments->isNotEmpty()
                        ? 'primary'
                        : null
                    )
                    ->icon(fn ($record) => $record->shipments->isNotEmpty()
                        ? Heroicon::Truck
                        : null
                    )
                    ->action(
                        Action::make('shipments')
                            ->label('ارسال‌ها')
                            ->modalHeading('اطلاعات ارسال')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('بستن')
                            ->infolist(function (Schema $schema, $record) {
                                $record->loadMissing('shipments');

                                return $schema
                                    ->record($record)
                                    ->components([
                                        RepeatableEntry::make('shipments')
                                            ->label('ارسال‌ها')
                                            ->schema([
                                                TextEntry::make('id')
                                                    ->label('شناسه ارسال'),

                                                TextEntry::make('provider')
                                                    ->label('ارائه‌دهنده ارسال')
                                                   ->formatStateUsing(fn ($state) => ShipmentProvider::label($state?->value) ?? '-'),

                                                TextEntry::make('status')
                                                    ->label('وضعیت')
                                                   ->formatStateUsing(fn ($state) => ShipmentStatuses::label($state?->value) ?? '-')
                                                    ->badge(),

                                                TextEntry::make('tracking_code')
                                                    ->label('کد رهگیری')
                                                    ->placeholder('-'),

                                                TextEntry::make('created_at')
                                                    ->label('تاریخ ایجاد')
                                                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : null),

                                                TextEntry::make('updated_at')
                                                    ->label('آخرین بروزرسانی')
                                                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : null),
                                            ])
                                            ->columns(2),
                                    ]);
                            })
                            ->visible(
                                fn ($record) => $record->shipments->isNotEmpty()
                            )
                    ),
            ])
            ->defaultSort('id', 'desc');
    }
}
