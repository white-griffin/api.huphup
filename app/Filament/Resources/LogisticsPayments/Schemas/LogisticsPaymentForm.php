<?php

namespace App\Filament\Resources\LogisticsPayments\Schemas;

use App\Enums\LogisticsPaymentStatuses;
use App\Enums\ShipmentProvider;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class LogisticsPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات سفارش')
                    ->columns(4)
                    ->schema([
                        Select::make('order_vendor_id')
                            ->relationship('orderVendor', 'id')
                            ->label('شماره سفارش پذیرنده')
                            ->disabled(),
                        Select::make('shipment_id')
                            ->relationship('shipment', 'id')
                            ->label('شماره مرسوله')
                            ->disabled(),
                        Select::make('provider')
                            ->label('ارسال کننده')
                            ->disabled()
                            ->options(ShipmentProvider::labels()),
                        TextInput::make('amount')
                            ->label('مبلغ')
                            ->disabled()
                            ->numeric(),
                    ])->columnSpanFull(),

                Section::make('فرم پرداخت')
                ->schema([

                    Grid::make()
                        ->columns(3)
                        ->schema([
                            TextInput::make('invoice_number')
                                ->label('شماره فاکتور پرداختی')
                                ->default(null),
                            TextInput::make('payment_reference')
                                ->label('کد رهگیری پرداخت')
                                ->default(null),
                            TextInput::make('payment_method')
                                ->label('نوع پرداخت')
                                ->default(null),
                        ]),
                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->default(null)
                        ->columnSpanFull(),
                    Radio::make('status')
                        ->label('وضعیت')
                        ->options(LogisticsPaymentStatuses::labels())
                        ->default(LogisticsPaymentStatuses::PENDING->value)
                        ->inline()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if ($state == LogisticsPaymentStatuses::PAID->value) {
                                $set('paid_at', now());
                                $set('paid_by', auth('admin')->id());
                            } else {
                                $set('paid_at', null);
                                $set('paid_by', null);
                            }
                        }),

                    Hidden::make('paid_at'),

                    Hidden::make('paid_by'),
                ])
                ->columnSpanFull()
            ]);
    }
}
