<?php

namespace App\Filament\Resources\Species\Schemas;

use App\Enums\ActivityStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class SpeciesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
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
                        TextInput::make('icon')
                            ->label('ایکن')
                            ->default(null),

                        FileUpload::make('image')
                            ->label('تصویر')
                            ->directory('species/images')
                            ->visibility('public')
                            ->maxSize(2048) // 2MB
                            ->imageEditor(),

                        Radio::make('activity_status')
                            ->label('وضعیت')
                            ->options(ActivityStatus::labels())
                            ->default(ActivityStatus::ACTIVE->value)
                            ->inline(),
                    ])->columnSpanFull(),

            ]);
    }
}
