<?php

namespace App\Filament\Resources\OrderVendors\Pages;

use App\Filament\Resources\OrderVendors\OrderVendorResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewOrderVendor extends ViewRecord
{
    protected static string $resource = OrderVendorResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات فروشنده سفارش')
                    ->schema([
                        TextEntry::make('id')
                            ->label('شناسه'),

                        TextEntry::make('business.name')
                            ->label('فروشگاه'),

                        TextEntry::make('order.id')
                            ->label('شماره سفارش'),

                        TextEntry::make('subtotal_amount')
                            ->label('جمع')
                            ->numeric(),

                        TextEntry::make('discount_amount')
                            ->label('تخفیف')
                            ->numeric(),

                        TextEntry::make('total_amount')
                            ->label('مبلغ نهایی')
                            ->numeric(),

                        TextEntry::make('paid_amount')
                            ->label('پرداخت‌شده')
                            ->numeric(),
                    ])
                    ->columns(3),
            ]);
    }
}
