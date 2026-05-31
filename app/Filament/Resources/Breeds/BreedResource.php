<?php

namespace App\Filament\Resources\Breeds;

use App\Filament\Resources\Breeds\Pages\CreateBreed;
use App\Filament\Resources\Breeds\Pages\EditBreed;
use App\Filament\Resources\Breeds\Pages\ListBreeds;
use App\Filament\Resources\Breeds\Schemas\BreedForm;
use App\Filament\Resources\Breeds\Tables\BreedsTable;
use App\Models\Breed;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BreedResource extends Resource
{
    protected static ?string $model = Breed::class;

    protected static ?string $navigationLabel = 'نژادهای حیوانات';

    protected static ?string $pluralLabel = 'نژادهای حیوانات';

    protected static ?string $modelLabel = 'نژاد حیوان';

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::RectangleGroup;

    public static function form(Schema $schema): Schema
    {
        return BreedForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BreedsTable::configure($table);
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
            'index' => ListBreeds::route('/'),
            'create' => CreateBreed::route('/create'),
            'edit' => EditBreed::route('/{record}/edit'),
        ];
    }
}
