<?php

namespace App\Filament\Resources\LogisticsPayments;

use App\Filament\Resources\LogisticsPayments\Pages\CreateLogisticsPayment;
use App\Filament\Resources\LogisticsPayments\Pages\EditLogisticsPayment;
use App\Filament\Resources\LogisticsPayments\Pages\ListLogisticsPayments;
use App\Filament\Resources\LogisticsPayments\Schemas\LogisticsPaymentForm;
use App\Filament\Resources\LogisticsPayments\Tables\LogisticsPaymentsTable;
use App\Models\LogisticsPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LogisticsPaymentResource extends Resource
{
    protected static ?string $model = LogisticsPayment::class;

    protected static ?string $navigationLabel = 'مرسولات';

    protected static ?string $pluralLabel = 'مرسولات';

    protected static ?string $modelLabel = 'مرسوله';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LogisticsPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LogisticsPaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLogisticsPayments::route('/'),
            'create' => CreateLogisticsPayment::route('/create'),
            'edit' => EditLogisticsPayment::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
