<?php

namespace App\Filament\Resources\BusinessProfiles\Schemas;

use App\Enums\MemberActivityStatuses;
use App\Enums\ProviderTypes;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BusinessProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('کاربر')
                    ->relationship('user', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('provider_type')
                    ->label('نوع خدمت')
                    ->options(ProviderTypes::labels())
                    ->default(ProviderTypes::SHOPPING->value),
                TextInput::make('business_name')
                    ->label('نام مرکز')
                    ->required(),
                TextInput::make('License_code')
                    ->label('شناسه ملی')
                    ->required(),
                Textarea::make('description')
                    ->label('توضیحات')
                    ->default(null)
                    ->columnSpanFull(),
                FileUpload::make('logo')
                    ->label('لوگو')
                    ->directory('businesses/logos')
                    ->image(),
                FileUpload::make('cover_image')
                    ->label('عکس کاور')
                    ->directory('businesses/covers')
                    ->image(),
                TextInput::make('phone')
                    ->label('تلفن')
                    ->tel()
                    ->required(),
                TextInput::make('email')
                    ->label('ایمیل')
                    ->email()
                    ->default(null),
                TextInput::make('website')
                    ->label('وبسایت')
                    ->url()
                    ->default(null),
                Select::make('city_id')
                    ->label('شهر')
                    ->relationship('city', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                    ->searchable()
                    ->preload()
                    ->required(),
                Textarea::make('address')
                    ->label('آدرس')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('postal_code')
                    ->label('کد پستی')
                    ->default(null),
                TextInput::make('latitude')
                    ->numeric()
                    ->default(null),
                TextInput::make('longitude')
                    ->numeric()
                    ->default(null),

                Radio::make('activity_status')
                    ->label('وضعیت')
                    ->options(MemberActivityStatuses::labels())
                    ->default(MemberActivityStatuses::PENDING->value)
                    ->inline()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state === MemberActivityStatuses::ACTIVE->value) {
                            $set('rejection_reason', null);
                            $set('verified_at', now());
                        }
                    }),

                DateTimePicker::make('verified_at')
                    ->label('تاریخ تأیید')
                    ->disabled(fn (Get $get) => $get('activity_status') === MemberActivityStatuses::ACTIVE->value)
                    ->dehydrated()
                    ->hidden(fn (Get $get) => $get('activity_status') !== MemberActivityStatuses::ACTIVE->value)
                    ->default(fn (Get $get) => $get('activity_status') === MemberActivityStatuses::ACTIVE->value ? now() : null),

                Textarea::make('rejection_reason')
                    ->label('دلیل رد')
                    ->default(null)
                    ->columnSpanFull()
                    ->hidden(fn (Get $get) => !in_array($get('activity_status'), [
                        MemberActivityStatuses::REJECTED->value,
                        MemberActivityStatuses::SUSPENDED->value,
                    ]))
                    ->required(fn (Get $get) => in_array($get('activity_status'), [
                        MemberActivityStatuses::REJECTED->value,
                        MemberActivityStatuses::SUSPENDED->value,
                    ]))
                    ,

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
            ]);
    }
}
