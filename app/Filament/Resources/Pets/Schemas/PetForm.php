<?php

namespace App\Filament\Resources\Pets\Schemas;

use App\Enums\GenderType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Select::make('user_id')
                            ->label('کاربر')
                            ->relationship('user', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('species_id')
                            ->label('نوع')
                            ->relationship('species', 'name_fa')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('breed_id')
                            ->label('نژاد')
                            ->relationship('breed', 'name_fa')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columnSpanFull(),

                Grid::make(3)
                    ->schema([
                        TextInput::make('name')
                            ->label('نام')
                            ->required(),
                        Select::make('gender')
                            ->label('جنسیت')
                            ->options(GenderType::labels()),
                        DatePicker::make('birth_date')
                            ->label('تاریخ تولد')
                            ->jalali(),
                    ])->columnSpanFull(),

                Grid::make(3)
                    ->schema([
                        TextInput::make('weight')
                            ->label('وزن')
                            ->numeric()
                            ->default(null),
                        TextInput::make('color')
                            ->label('رنگ')
                            ->default(null),
                        FileUpload::make('avatar')
                            ->label('آواتار')
                            ->directory('pets/images')
                            ->image(),
                    ])->columnSpanFull(),


                Repeater::make('medical_records')
                    ->label('رکوردهای پزشکی')
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان')
                            ->required(),

                        TextInput::make('value')
                            ->label('مقدار')
                            ->required(),
                    ])
                    ->collapsible() // اختیاری
                    ->createItemButtonLabel('افزودن آیتم')
                    ->columns()
                    ->columnSpanFull(),

                Repeater::make('settings')
                    ->label('تنظیمات')
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان')
                            ->required(),

                        TextInput::make('value')
                            ->label('مقدار')
                            ->required(),
                    ])
                    ->collapsible() // اختیاری
                    ->createItemButtonLabel('افزودن آیتم')
                    ->columns()
                    ->columnSpanFull(),

                Textarea::make('bio')
                    ->label('بیوگرافی')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
