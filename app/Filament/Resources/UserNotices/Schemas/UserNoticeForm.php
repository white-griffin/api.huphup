<?php

namespace App\Filament\Resources\UserNotices\Schemas;

use App\Enums\UserNoticeTypes;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserNoticeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('target')
                    ->label('ارسال به ...')
                    ->options([
                        'all' => 'همه کاربران',
                        'selected' => 'کاربران انتخابی',
                        'one' => 'یک کاربر',
                    ])
                    ->default('all')
                    ->live()
                    ->required(),

                Select::make('user_id')
                    ->label('کاربر')
                    ->relationship('user', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->fullName)
                    ->placeholder('نام کاربر را وارد کنید')
                    ->searchable()
                    ->preload()
                    ->visible(fn($get): bool => $get('target') === 'one')
                    ->required(fn($get): bool => $get('target') === 'one'),

                Select::make('user_ids')
                    ->label('کاربران')
                    ->multiple()
                    ->relationship('user', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->fullName)
                    ->placeholder('نام کاربران را وارد کنید')
                    ->searchable()
                    ->preload()
                    ->visible(fn($get): bool => $get('target') === 'selected')
                    ->required(fn($get): bool => $get('target') === 'selected'),

                Select::make('type')
                    ->label('موضوع')
                    ->options(
                        UserNoticeTypes::labels()
                    )
                    ->default(UserNoticeTypes::GENERAL->value)
                    ->required(),

                TextInput::make('title')
                    ->label('عنوان')
                    ->required()
                    ->maxLength(255),

                RichEditor::make('body')
                    ->label('متن اعلان')
                    ->required()
                    ->maxLength(5000)
                    ->columnSpanFull(),

                KeyValue::make('data')
                    ->label('اطلاعات ')
                    ->nullable()
                    ->keyLabel('عنوان')
                    ->valueLabel('مقدار')
                    ->columnSpanFull(),


            ]);
    }
}
