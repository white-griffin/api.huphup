<?php

namespace App\Filament\Resources\Groups\Schemas;

use App\Enums\ActivityStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                ->label('عنوان گروه')
                ->required()
                ->maxLength(100),

                FileUpload::make('image')
                    ->label('تصویر')
                    ->directory('groups/images')
                    ->image(),

                Radio::make('activity_status')
                    ->label('وضعیت')
                    ->options(ActivityStatus::labels())
                    ->default(ActivityStatus::ACTIVE->value)
                    ->inline(),

            ]);
    }
}
