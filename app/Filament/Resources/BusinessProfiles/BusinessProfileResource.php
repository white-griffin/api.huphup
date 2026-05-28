<?php

namespace App\Filament\Resources\BusinessProfiles;

use App\Filament\Resources\BusinessProfiles\Pages\CreateBusinessProfile;
use App\Filament\Resources\BusinessProfiles\Pages\EditBusinessProfile;
use App\Filament\Resources\BusinessProfiles\Pages\ListBusinessProfiles;
use App\Filament\Resources\BusinessProfiles\Schemas\BusinessProfileForm;
use App\Filament\Resources\BusinessProfiles\Tables\BusinessProfilesTable;
use App\Models\BusinessProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BusinessProfileResource extends Resource
{
    protected static ?string $model = BusinessProfile::class;

    protected static ?string $navigationLabel = 'خدمت دهندگان';

    protected static ?string $pluralLabel = 'خدمت دهندگان';

    protected static ?string $modelLabel = 'خدمت دهنده';

    protected static ?int $navigationSort = 6;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    public static function form(Schema $schema): Schema
    {
        return BusinessProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusinessProfilesTable::configure($table);
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
            'index' => ListBusinessProfiles::route('/'),
            'create' => CreateBusinessProfile::route('/create'),
            'edit' => EditBusinessProfile::route('/{record}/edit'),
        ];
    }
}
