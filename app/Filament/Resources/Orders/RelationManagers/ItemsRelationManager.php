<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'آیتم‌های سفارش';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with([
                'product',
                'variation.variationAttributes.attribute',
                'variation.variationAttributes.option',
                'vendor.business',
            ]))
            ->columns([
                TextColumn::make('id')
                    ->label('شناسه')
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('محصول')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('variation_id')
                    ->label('تنوع انتخابی')
                    ->state(fn ($record) => $record->variation ? 'مشاهده ویژگی' : '-')
                    ->color(fn ($record) => $record->variation ? 'primary' : null)
                    ->icon(fn ($record) => $record->variation ? Heroicon::Eye : null)
                    ->action(
                        Action::make('variation')
                            ->label('مشخصات تنوع')
                            ->modalHeading(
                                fn ($record) => 'مشخصات تنوع'
                            )
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('بستن')
                            ->infolist(function (Schema $schema, $record) {
                                $variation = $record->variation;

                                return $schema
                                    ->record($variation)
                                    ->components([
                                        RepeatableEntry::make('variationAttributes')
                                            ->label('ویژگی‌ها')
                                            ->schema([
                                                TextEntry::make('attribute.name')
                                                    ->label('ویژگی'),

                                                TextEntry::make('option.value')
                                                    ->label('مقدار'),
                                            ])
                                            ->columns(2),

                                        TextEntry::make('price')
                                            ->label('قیمت')
                                            ->numeric()
                                            ->suffix(' تومان')
                                            ->columnSpanFull(),
                                    ]);
                            })
                            ->visible(
                                fn ($record) => $record->variation !== null
                            )
                    ),

                TextColumn::make('vendor.business.name')
                    ->label('فروشنده')
                    ->placeholder('-'),


                TextColumn::make('unit_price')
                    ->label('قیمت اصلی')
                    ->numeric()
                    ->suffix(' تومان')
                    ->sortable(),

                TextColumn::make('discount_price')
                    ->label('مبلغ تخفیف خورده')
                    ->numeric()
                    ->suffix(' تومان')
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('تعداد')
                    ->sortable(),

                TextColumn::make('total_price')
                    ->label('مبلغ کل')
                    ->numeric()
                    ->suffix(' تومان')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc');
    }
}
