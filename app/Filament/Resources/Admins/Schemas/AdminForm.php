<?php

namespace App\Filament\Resources\Admins\Schemas;

use App\Enums\ActivityStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class AdminForm
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

                        TextInput::make('username')
                            ->label('نام کاربری')
                            ->required(),
                    ])->columnSpanFull(),

                Grid::make()
                    ->columns(3)
                    ->schema([

                        TextInput::make('mobile')
                            ->label('شماره موبایل')
                            ->unique(ignoreRecord: true)
                            ->required(),

                        TextInput::make('email')
                            ->label('آدرس ایمیل')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required(),

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


                Grid::make()
                    ->columns(3)
                    ->schema([


                        // انتخاب نقش (اختیاری)
                        Select::make('roles')
                            ->label('نقش')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload(),

                        FileUpload::make('avatar')
                            ->image()
                            ->label('عکس پروفایل')
                            ->directory('admin/avatars')
                            ->visibility('public')
                            ->maxSize(2048) // 2MB
                            ->imageEditor()
                        ,

                        Radio::make('activity_status')
                            ->label('وضعیت')
                            ->options(ActivityStatus::labels())
                            ->default(ActivityStatus::ACTIVE->value)
                            ->inline(),
                    ])->columnSpanFull(),
            ]);
    }
}
