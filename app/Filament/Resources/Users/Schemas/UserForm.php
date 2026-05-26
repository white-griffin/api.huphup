<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\GenderType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columns(3)
                    ->schema([
                        TextInput::make('first_name')
                            ->label('نام')
                            ->required(),

                        TextInput::make('last_name')
                            ->label('نام خانوادگی')
                            ->required(),

                        TextInput::make('mobile')
                            ->label('شماره موبایل')
                            ->unique(ignoreRecord: true)
                            ->required(),

                    ])->columnSpanFull(),

                Grid::make()
                    ->columns(2)
                    ->schema([

                        TextInput::make('email')
                            ->label('آدرس ایمیل')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->default(null),

                        TextInput::make('password')
                            ->label('رمز عبور')
                            ->password()
                            ->revealable()
                            ->minLength(6)
                            ->required(fn (string $context) => $context === 'create') // فقط موقع ساخت
                            ->dehydrated(fn ($state) => filled($state)), // فقط وقتی چیزی وارد شده ذخیره کنه

                    ])->columnSpanFull(),

                Grid::make()
                    ->columns(3)
                    ->schema([
                        Select::make('gender')
                            ->label('جنسیت')
                            ->options(GenderType::labels()),

                        TextInput::make('national_code')
                            ->label('کد ملی'),

                        DatePicker::make('birth_date')
                            ->label('تاریخ تولد')
                            ->jalali()

                    ]),

                Grid::make()
                    ->columns(2)
                    ->schema([
                        FileUpload::make('avatar')
                            ->image()
                            ->directory('users/avatar')
                            ->label('عکس پروفایل')
                            ->imageEditor(),

                        Textarea::make('bio')
                            ->label('بیوگرافی')
                            ->default(null),

                    ]),

            ]);
    }
}
