<?php

namespace App\Filament\Resources\Commissions\Tables;

use App\Filament\Resources\Orders\OrdersResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('شناسه')
                    ->sortable(),


                TextColumn::make('business.name')
                    ->label('کسب‌وکار')
                    ->searchable()
                    ->sortable(),


                TextColumn::make('payment.id')
                    ->label('پرداخت')
                    ->url(fn ($record) => OrdersResource::getUrl('index', [
                        'record' => $record->payment?->payable->id,
                    ]))
                    ->openUrlInNewTab()
                    ->sortable(),


                TextColumn::make('rate')
                    ->label('درصد کمیسیون')
                    ->suffix('%')
                    ->sortable(),


                TextColumn::make('amount')
                    ->label('مبلغ کمیسیون')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' تومان')
                    ->sortable(),


                TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->dateTime()
                    ->jalaliDate('Y/m/d')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
//            ->recordActions([
//                ViewAction::make(),
//            ])
            ->toolbarActions([

            ]);
    }
}
