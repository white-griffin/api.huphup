<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\PublicationStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business.name')
                    ->label('فروشگاه')
                    ->searchable(),
                TextColumn::make('brand.name')
                    ->label('برند')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('نام')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('قیمت اصلی')
                    ->sortable(),
                TextColumn::make('discount_price')
                    ->label('قیمت تخفیف خورده')
                    ->default('بدون تخفیف')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('موجودی')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label('کد انبار')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('publication_status')
                    ->label('وضعیت انتشار')
                    ->formatStateUsing(fn ($state) => PublicationStatus::label((string) $state) ?? '—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->jalaliDate('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاریخ ویرایش')
                    ->jalaliDate('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
//                EditAction::make(),
            ])->recordActionsColumnLabel('عملیات')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
