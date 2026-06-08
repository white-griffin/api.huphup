<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Enums\ActivityStatus;
use App\Enums\CategoryTypes;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('parent_id')
                    ->label('والد')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->default(null),
                TextInput::make('name')
                    ->label('عنوان')
                    ->required(),
                Select::make('type')
                    ->label('نوع دسته بندی')
                    ->required()
                    ->options(CategoryTypes::labels()),
                FileUpload::make('image')
                    ->label('تصویر')
                    ->directory('categories/images')
                    ->image(),
                Radio::make('activity_status')
                    ->label('وضعیت')
                    ->options(ActivityStatus::labels())
                    ->default(ActivityStatus::ACTIVE->value)
                    ->inline(),
            ]);
    }
}
