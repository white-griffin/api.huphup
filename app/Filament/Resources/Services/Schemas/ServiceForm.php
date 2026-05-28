<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\ActivityStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('عنوان')
                    ->required(),
                TextInput::make('name_en')
                    ->label('عنوان انگلیسی')
                    ->default(null),
                Textarea::make('description')
                    ->label('توضیحات')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('icon')
                    ->label('ایکن')
                    ->default(null),
                FileUpload::make('image')
                    ->directory('services/images')
                    ->image(),
                Radio::make('activity_status')
                    ->label('وضعیت')
                    ->options(ActivityStatus::labels())
                    ->default(ActivityStatus::ACTIVE->value)
                    ->inline(),

            ]);
    }
}
