<?php

namespace App\Filament\Resources\Breeds\Schemas;

use App\Enums\ActivityStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class BreedForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(4)
                    ->schema([
                        Select::make('species_id')
                            ->label('نوع حیوان')
                            ->relationship('species', 'name_fa')
                            ->placeholder('نام حیوان را وارد کنید')
                            ->searchable()
                            ->required(),

                        TextInput::make('name_en')
                            ->label('نام انگلیسی')
                            ->required(),

                        TextInput::make('name_fa')
                            ->label('نام فارسی')
                            ->required(),

                        TextInput::make('slug')
                            ->label('اسلاگ')
                            ->required(),

                    ])->columnSpanFull(),

                Grid::make(3)
                    ->schema([
                        Textarea::make('description')
                            ->label('توضیحات')
                            ->default(null),

                        FileUpload::make('image')
                            ->label('تصویر')
                            ->image(),

                        Radio::make('activity_status')
                            ->label('وضعیت')
                            ->options(ActivityStatus::labels())
                            ->default(ActivityStatus::ACTIVE->value)
                            ->inline(),
                    ])->columnSpanFull(),


                Repeater::make('characteristics')
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('value')
                            ->label('مقدار')
                            ->required(),

                    ])
                    ->columns(2)
                    ->collapsible()
                    ->defaultItems(1)
                    ->addActionLabel('افزودن خصوصیت جدید')
                    ->label('خصوصیات')
                    ->columnSpanFull(),
            ]);
    }
}
