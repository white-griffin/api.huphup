<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Enums\ActivityStatus;
use App\Enums\CouponTypes;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('کد')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),

                Select::make('type')
                    ->label('نوع تخفیف')
                    ->options(CouponTypes::labels())
                    ->required()
                    ->native(false),

                TextInput::make('value')
                    ->label('مقدار تخفیف')
                    ->numeric()
                    ->required(),

                TextInput::make('max_discount')
                    ->label('سقف تخفیف')
                    ->numeric(),

                TextInput::make('min_amount')
                    ->label('حداقل مبلغ سفارش')
                    ->numeric()
                    ->default(0)
                    ->required(),

                TextInput::make('usage_limit')
                    ->label('حداکثر استفاده کل')
                    ->numeric(),

                TextInput::make('usage_limit_per_user')
                    ->label('حداکثر استفاده هر کاربر')
                    ->numeric(),

                DateTimePicker::make('starts_at')
                    ->jalali()
                    ->label('شروع اعتبار')
                    ->seconds(false),

                DateTimePicker::make('ends_at')
                    ->jalali()
                    ->label('پایان اعتبار')
                    ->seconds(false),

                Radio::make('activity_status')
                    ->label('وضعیت')
                    ->options(ActivityStatus::labels())
                    ->default(ActivityStatus::ACTIVE->value)
                    ->inline(),
            ]);
    }
}
