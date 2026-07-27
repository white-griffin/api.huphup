<?php

namespace App\Filament\Resources\CommissionRules\Schemas;

use App\Enums\ActivityStatus;
use App\Enums\BusinessTypes;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CommissionRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('business_type')
                    ->label('نوع کسب و کار')
                    ->options(BusinessTypes::labels())
                    ->required(),

                TextInput::make('min_rating')
                    ->label('حداقل امتیاز')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(5)
                    ->required(),

                TextInput::make('max_rating')
                    ->label('حداکثر امتیاز')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(5)
                    ->required(),

                TextInput::make('commission_rate')
                    ->label('درصد کمیسیون')
                    ->numeric()
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),

                TextInput::make('priority')
                    ->label('اولویت')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Radio::make('activity_status')
                    ->label('وضعیت')
                    ->options(ActivityStatus::labels())
                    ->default(ActivityStatus::ACTIVE->value)
                    ->inline(),
            ]);
    }
}
