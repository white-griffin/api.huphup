<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatuses;
use App\Filament\Resources\Orders\OrdersResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Morilog\Jalali\Jalalian;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrdersResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات سفارش')
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('شناسه سفارش'),

                        TextEntry::make('user.fullName')
                            ->label('مشتری')
                            ->placeholder('-'),

                        TextEntry::make('order_status')
                            ->label('وضعیت سفارش')
                            ->formatStateUsing(fn ($state) => OrderStatuses::label((string) $state) ?? '—'),

                        TextEntry::make('total_amount')
                            ->label('مبلغ کل')
                            ->numeric(decimalPlaces: 0)
                            ->suffix(' تومان'),

                        TextEntry::make('discount_amount')
                            ->label('مبلغ تخفیف')
                            ->numeric(decimalPlaces: 0)
                            ->suffix(' تومان'),

                        TextEntry::make('created_at')
                            ->label('تاریخ ثبت')
                            ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : null),
                    ])
                    ->columns(3),

                Section::make('آدرس ارسال')
                    ->schema([
                        TextEntry::make('shipping_address')
                            ->label('آدرس')
                            ->columnSpanFull(),

                        TextEntry::make('shipping_latitude')
                            ->label('عرض جغرافیایی'),

                        TextEntry::make('shipping_longitude')
                            ->label('طول جغرافیایی'),
                    ])
                    ->columns(2),
            ]);
    }
}
