<?php

namespace App\Filament\Resources\WalletWithdrawals;

use App\Filament\Resources\WalletWithdrawals\Pages\CreateWalletWithdrawal;
use App\Filament\Resources\WalletWithdrawals\Pages\EditWalletWithdrawal;
use App\Filament\Resources\WalletWithdrawals\Pages\ListWalletWithdrawals;
use App\Filament\Resources\WalletWithdrawals\Schemas\WalletWithdrawalForm;
use App\Filament\Resources\WalletWithdrawals\Tables\WalletWithdrawalsTable;
use App\Models\WalletWithdrawal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WalletWithdrawalResource extends Resource
{
    protected static ?string $model = WalletWithdrawal::class;

    protected static string|null|\UnitEnum $navigationGroup = 'تنظیمات مالی';

    protected static ?string $navigationLabel = 'درخواست‌های برداشت';

    protected static ?string $modelLabel = 'درخواست برداشت';

    protected static ?string $pluralModelLabel = 'درخواست‌های برداشت';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Banknotes;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('business');
    }

    public static function form(Schema $schema): Schema
    {
        return WalletWithdrawalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WalletWithdrawalsTable::configure($table);
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
            'index' => ListWalletWithdrawals::route('/'),
            'create' => CreateWalletWithdrawal::route('/create'),
            'edit' => EditWalletWithdrawal::route('/{record}/edit'),
        ];
    }
}
