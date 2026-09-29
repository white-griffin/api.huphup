<?php

namespace App\Filament\Resources\OrderVendors\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'آیتم‌ها';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('شناسه')
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('محصول')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('variation.name')
                    ->label('تنوع')
                    ->placeholder('-'),

                TextColumn::make('quantity')
                    ->label('تعداد')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('قیمت')
                    ->numeric(),

                TextColumn::make('total_amount')
                    ->label('مبلغ')
                    ->numeric(),
            ])
            ->defaultSort('id', 'desc');
    }
}
