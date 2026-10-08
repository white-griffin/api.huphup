<?php

namespace App\Filament\Resources\Banners\Tables;

use App\Enums\ActivityStatus;
use App\Enums\BannerPlacement;
use App\Models\Banner;
use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images.default')
                    ->label('تصویر')
                    ->disk('public')
                    ->square(),

                TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable(),

                TextColumn::make('placement')
                    ->label('محل نمایش')
                    ->badge()
                    ->formatStateUsing(
                        fn($state) => BannerPlacement::label($state) ?? '—'
                    ),

                TextColumn::make('target_type')
                    ->label('مقصد')
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            Product::class => 'محصول',
                            Business::class => 'بیزنس',
                            Category::class => 'دسته‌بندی',
                            default => 'بدون مقصد',
                        }
                    ),


                ToggleColumn::make('activity_status')
                    ->label('فعال')
                    ->getStateUsing(
                        fn (Banner $record): bool =>
                            $record->activity_status == ActivityStatus::ACTIVE->value
                    )
                    ->updateStateUsing(
                        function (Banner $record, bool $state): void {
                            $record->update([
                                'activity_status' => $state
                                    ? ActivityStatus::ACTIVE->value
                                    : ActivityStatus::INACTIVE->value,
                            ]);
                        }
                    ),

                TextColumn::make('starts_at')
                    ->label('شروع')
                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : null)
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('پایان')
                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : null)
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
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
