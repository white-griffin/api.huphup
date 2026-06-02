<?php

namespace App\Filament\Resources\Providers\Schemas;

use App\Enums\GenderType;
use App\Enums\VerificationDocumentType;
use App\Enums\VerificationStatuses;
use App\Models\City;
use Filament\Forms\Components\DatePicker;
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
use Illuminate\Support\Facades\Hash;

class ProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('اطلاعات هویتی')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('first_name')
                                    ->label('نام')
                                    ->required(),
                                TextInput::make('last_name')
                                    ->label('نام خانوادگی')
                                    ->required(),
                                TextInput::make('national_code')
                                    ->label('کدملی')
                                    ->required(),
                            ])->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('father_name')
                                    ->label('نام پدر')
                                    ->default(null),
                                DatePicker::make('birth_date')
                                    ->jalali()
                                    ->label('تاریخ تولد'),
                                Select::make('gender')
                                    ->label('جنسیت')
                                    ->options(GenderType::labels()),
                            ])->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('mobile')
                                    ->label('موبایل')
                                    ->required(),

                                TextInput::make('email')
                                    ->label('ایمیل')
                                    ->email()
                                    ->default(null),

                                TextInput::make('password')
                                    ->label('رمز عبور')
                                    ->password()
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->maxLength(255)
                                    ->afterStateHydrated(function ($component) {
                                        $component->state(null);
                                    })

                            ])->columnSpanFull(),


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

                                TextInput::make('postal_code')
                                    ->label('کد پستی')
                                    ->default(null),
                            ])->columnSpanFull(),

                        Textarea::make('address')
                            ->label('آدرس')
                            ->default(null)
                            ->columnSpanFull(),

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
                            ]))
                    ]),

                Section::make('مدارک')
                    ->schema([
                        Repeater::make('documents')
                            ->relationship()
                            ->label('مدارک هویتی')
                            ->schema([
                                Select::make('type')
                                    ->label('نوع مدرک')
                                    ->options(VerificationDocumentType::labels())
                                    ->required(),

                                Select::make('verification_status ')
                                    ->label('وضعیت')
                                    ->options(VerificationStatuses::labels())
                                    ->default(VerificationStatuses::PENDING->value),

                                FileUpload::make('image')
                                    ->label('تصویر')
                                    ->image()
                                    ->directory('provider/documents')
                                    ->required(),


                            ])
                            ->columns(3)
                            ->addActionLabel('افزودن مدرک')
                            ->collapsible(),
                    ]),
            ]);


    }
}
