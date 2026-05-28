<?php

namespace App\Filament\Resources\Pets\Tables;

use App\Enums\GenderType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('تصویر')
                    ->square(),
                TextColumn::make('user.fullName')
                    ->label('کاربر')
                    ->sortable(),
                TextColumn::make('species.name_fa')
                    ->label('نوع')
                    ->searchable(),
                TextColumn::make('breed.name_fa')
                    ->label('نژاد')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('نام')
                    ->searchable(),
                TextColumn::make('gender_type')
                    ->label('جنسیت')
                    ->formatStateUsing(fn ($state) => GenderType::label($state))
                    ->sortable(),
                TextColumn::make('birth_date')
                    ->label('تاریخ تولد')
                    ->jalaliDate('Y/m/d')
                    ->sortable(),
                TextColumn::make('weight')
                    ->label('وزن')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('color')
                    ->label('رنگ')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاریخ ویرایش')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])->recordActionsColumnLabel('عملیات')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
