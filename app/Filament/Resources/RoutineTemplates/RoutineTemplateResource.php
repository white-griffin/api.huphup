<?php

namespace App\Filament\Resources\RoutineTemplates;

use App\Filament\Resources\RoutineTemplates\Pages\CreateRoutineTemplate;
use App\Filament\Resources\RoutineTemplates\Pages\EditRoutineTemplate;
use App\Filament\Resources\RoutineTemplates\Pages\ListRoutineTemplates;
use App\Filament\Resources\RoutineTemplates\Schemas\RoutineTemplateForm;
use App\Filament\Resources\RoutineTemplates\Tables\RoutineTemplatesTable;
use App\Models\RoutineTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RoutineTemplateResource extends Resource
{
    protected static ?string $model = RoutineTemplate::class;

    protected static ?string $navigationLabel = 'روتین های حیوانات';

    protected static ?string $pluralLabel = 'روتین های حیوانات';

    protected static ?string $modelLabel = 'روتین حیوان';

    protected static ?int $navigationSort = 12;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::CircleStack;

    public static function form(Schema $schema): Schema
    {
        return RoutineTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoutineTemplatesTable::configure($table);
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
            'index' => ListRoutineTemplates::route('/'),
            'create' => CreateRoutineTemplate::route('/create'),
            'edit' => EditRoutineTemplate::route('/{record}/edit'),
        ];
    }
}
