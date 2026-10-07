<?php

namespace App\Filament\Resources\FcmNotifications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FcmNotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable(),

                TextColumn::make('audience_type')
                    ->label('نوع دریافت کنندگان')
                    ->badge(),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge(),

                TextColumn::make('total_recipients')
                    ->label('تعداد دریافت شده ها'),

                TextColumn::make('processed_recipients')
                    ->label('تعداد درحال ارسال ها'),

                TextColumn::make('failed_recipients')
                    ->label('تعداد ارسال های ناموفق'),

                TextColumn::make('sent_at')
                    ->label('زمان ارسال')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('ارسال کننده'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
//                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
