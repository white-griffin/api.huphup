<?php

namespace App\Filament\Resources\OrderVendors;

use App\Filament\Resources\OrderVendors\Pages\CreateOrderVendor;
use App\Filament\Resources\OrderVendors\Pages\EditOrderVendor;
use App\Filament\Resources\OrderVendors\Pages\ListOrderVendors;
use App\Filament\Resources\OrderVendors\Pages\ViewOrderVendor;
use App\Filament\Resources\OrderVendors\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\OrderVendors\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\OrderVendors\RelationManagers\ShipmentsRelationManager;
use App\Filament\Resources\OrderVendors\Schemas\OrderVendorForm;
use App\Filament\Resources\OrderVendors\Schemas\OrderVendorInfolist;
use App\Filament\Resources\OrderVendors\Tables\OrderVendorsTable;
use App\Models\OrderVendor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderVendorResource extends Resource
{
    protected static ?string $model = OrderVendor::class;

    protected static ?string $navigationLabel = 'فروشنده‌های سفارش';

    protected static ?string $pluralLabel = 'فروشنده‌های سفارش';

    protected static ?string $modelLabel = 'فروشنده سفارش';

    public static function form(Schema $schema): Schema
    {
        return OrderVendorForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderVendorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderVendorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            ShipmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderVendors::route('/'),
            'create' => CreateOrderVendor::route('/create'),
            'view' => ViewOrderVendor::route('/{record}'),
            'edit' => EditOrderVendor::route('/{record}/edit'),
        ];
    }
}
