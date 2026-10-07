<?php

namespace App\Filament\Resources\FcmNotifications\Schemas;

use App\Enums\FcmNotificationAudience;
use App\Enums\PlatformTypes;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class FcmNotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('عنوان و دریافت کننده')
                ->columns()
                ->schema([
                    TextInput::make('title')
                        ->label('عنوان')
                        ->required()
                        ->maxLength(255),


                    Select::make('audience_type')
                        ->label('نوع دریافت کننده')
                        ->options([
                            FcmNotificationAudience::ALL->value => 'همه ی کاربران',
                            FcmNotificationAudience::USERS->value => 'کاربر خاص',
                            FcmNotificationAudience::PLATFORM->value => 'بر اساس پلتفرم',
                        ])
                        ->required()
                        ->live(),

                    Select::make('user_ids')
                        ->label('کاربران')
                        ->multiple()
                        ->visible(fn($get): bool => $get('audience_type') == FcmNotificationAudience::USERS->value)
                        ->required(fn($get): bool => $get('audience_type') == FcmNotificationAudience::USERS->value)
                        ->searchable()
                        ->searchPrompt('جستجوی نام، نام خانوادگی یا موبایل')
                        ->getSearchResultsUsing(function (string $search): array {
                            return User::query()
                                ->where(function ($query) use ($search) {
                                    $query
                                        ->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhere('mobile', 'like', "%{$search}%");
                                })
                                ->orderBy('id')
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn(User $user) => [
                                    $user->id => sprintf(
                                        '<div>
                        <div class="font-medium">%s %s</div>
                        <div class="text-xs text-gray-400">%s</div>
                    </div>',
                                        e($user->first_name),
                                        e($user->last_name),
                                        e($user->mobile),
                                    ),
                                ])
                                ->toArray();
                        })
                        ->getOptionLabelsUsing(function (array $values): array {
                            return User::query()
                                ->whereIn('id', $values)
                                ->get()
                                ->mapWithKeys(fn(User $user) => [
                                    $user->id => sprintf(
                                        '<div>
                        <div class="font-medium">%s %s</div>
                        <div class="text-xs text-gray-400">%s</div>
                    </div>',
                                        e($user->first_name),
                                        e($user->last_name),
                                        e($user->mobile),
                                    ),
                                ])
                                ->toArray();
                        })
                        ->allowHtml(),

                    Select::make('platform')
                        ->label('پلتفرم')
                        ->options([
                            PlatformTypes::ANDROID->value => 'Android',
                            PlatformTypes::IOS->value => 'iOS',
                        ])
                        ->visible(
                            fn(Get $get) => $get('audience_type') == FcmNotificationAudience::PLATFORM->value
                        )
                        ->required(
                            fn(Get $get) => $get('audience_type') == FcmNotificationAudience::PLATFORM->value
                        ),
                ])->columnSpanFull(),


                Section::make('محتوا')
                    ->columns()
                ->schema([
                    RichEditor::make('body')
                        ->label('محتوا')
                        ->required(),

                    KeyValue::make('data')
                        ->label('داده ها')
                        ->keyLabel('کلید')
                        ->valueLabel('مقدار')
                        ->helperText(
                            'اطلاعات تکمیلی مورد نیاز اپلیکیشن (تایپ سفارش ، شماره سفارش)'
                        ),
                ])->columnSpanFull()
            ]);
    }
}
