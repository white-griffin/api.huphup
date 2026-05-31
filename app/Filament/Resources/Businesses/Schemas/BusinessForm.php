<?php

namespace App\Filament\Resources\Businesses\Schemas;

use App\Enums\ActivityStatus;
use App\Enums\BusinessTypes;
use App\Enums\VerificationDocumentType;
use App\Enums\VerificationStatuses;
use App\Models\City;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BusinessForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('اطلاعات اصلی')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('provider_id')
                                    ->label('تامین کننده')
                                    ->relationship('provider', 'first_name')
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                                    ->placeholder('نام تامین کننده را وارد کنید')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('business_type')
                                    ->label('نوع کسب و کار')
                                    ->required()
                                    ->options(BusinessTypes::labels()),

                                TextInput::make('name')
                                    ->label('نام')
                                    ->required(),
                            ])->columnSpanFull(),

                        Grid::make()
                            ->schema([
                                FileUpload::make('logo')
                                    ->label('لوگو')
                                    ->image()
                                    ->directory('businesses/logos'),
                                FileUpload::make('cover_image')
                                    ->label('کاور')
                                    ->image()
                                    ->directory('businesses/covers'),
                            ])->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('phone')
                                    ->label('تلفن')
                                    ->tel()
                                    ->required(),
                                TextInput::make('email')
                                    ->label('ایمیل')
                                    ->email()
                                    ->default(null),
                                TextInput::make('website')
                                    ->label('وب سایت')
                                    ->url()
                                    ->default(null),
                            ])->columnSpanFull(),

                        Grid::make()
                            ->schema([
                                Textarea::make('description')
                                    ->label('توضیحات')
                                    ->default(null)
                                    ->columnSpanFull(),
                                Radio::make('activity_status')
                                    ->label('وضعیت')
                                    ->options(ActivityStatus::labels())
                                    ->default(ActivityStatus::ACTIVE->value)
                                    ->inline(),
                            ])
                    ])->columnSpanFull(),


                Section::make('اطلاعات جغرافیایی')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('province_id')
                                    ->label('استان')
                                    ->relationship('province', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn(Set $set) => $set('city_id', null)),

                                Select::make('city_id')
                                    ->label('شهر')
                                    ->options(fn(Get $get): array => City::query()
                                        ->where('province', $get('province_id'))
                                        ->pluck('name', 'id')
                                        ->all()
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabled(fn(Get $get): bool => blank($get('province_id'))),

                                TextInput::make('license_code')
                                    ->label('کد پستی')
                                    ->default(null),
                            ])->columnSpanFull(),

                        Textarea::make('address')
                            ->label('آدرس')
                            ->default(null)
                            ->columnSpanFull(),

                        Grid::make()
                            ->schema([
                                TextInput::make('latitude')
                                    ->numeric()
                                    ->default(null),

                                TextInput::make('longitude')
                                    ->numeric()
                                    ->default(null),
                            ])
                    ])->columnSpanFull(),

                Section::make('اطلاعات بانکی')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label('نام بانک')
                                    ->default(null),
                                TextInput::make('bank_account_holder')
                                    ->label('نام صاحب حساب')
                                    ->default(null),
                                TextInput::make('bank_card')
                                    ->label('شماره کارت')
                                    ->default(null),
                                TextInput::make('bank_iban')
                                    ->label('شماره شبا (بدون IR)')
                                    ->default(null),

                            ])
                            ->columnSpanFull(),

                    ])->columnSpanFull(),

                Section::make('اطلاعات احراز هویت')
                    ->schema([

                        Radio::make('verification_status')
                            ->label('احراز سامانه')
                            ->options(VerificationStatuses::labels())
                            ->default(VerificationStatuses::PENDING->value)
                            ->inline()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state === VerificationStatuses::ACTIVE->value) {
                                    $set('rejection_reason', null);
                                    $set('verified_at', now());
                                }
                            }),

                        DateTimePicker::make('verified_at')
                            ->label('تاریخ تأیید')
                            ->disabled(fn(Get $get) => $get('verification_status') === VerificationStatuses::ACTIVE->value)
                            ->dehydrated()
                            ->hidden(fn(Get $get) => $get('verification_status') !== VerificationStatuses::ACTIVE->value)
                            ->default(fn(Get $get) => $get('verification_status') === VerificationStatuses::ACTIVE->value ? now() : null),


                        Textarea::make('rejection_reason')
                            ->label('دلیل رد')
                            ->default(null)
                            ->columnSpanFull()
                            ->hidden(fn(Get $get) => !in_array($get('verification_status'), [
                                VerificationStatuses::REJECTED->value,
                                VerificationStatuses::SUSPENDED->value,
                            ]))
                            ->required(fn(Get $get) => in_array($get('verification_status'), [
                                VerificationStatuses::REJECTED->value,
                                VerificationStatuses::SUSPENDED->value,
                            ])),
                    ])->columnSpanFull(),

                Section::make('تنظیمات')
                    ->schema([
                        Repeater::make('settings')
                            ->label('تنظیمات کسب و کار')
                            ->schema([
                                TextInput::make('title')
                                    ->label('عنوان')
                                    ->required(),

                                TextInput::make('value')
                                    ->label('مقدار')
                                    ->required(),

                            ])
                            ->addActionLabel('افزودن آیتم')
                            ->columns(2)
                            ->collapsible(),
                    ])->columnSpanFull()
            ]);
    }
}
