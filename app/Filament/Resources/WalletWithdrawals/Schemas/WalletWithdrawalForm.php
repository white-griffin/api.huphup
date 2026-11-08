<?php

namespace App\Filament\Resources\WalletWithdrawals\Schemas;

use App\Enums\WalletWithdrawalStatuses;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class WalletWithdrawalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('business_id')
                    ->relationship('business', 'name')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options(WalletWithdrawalStatuses::class)
                    ->default(1)
                    ->required(),
                Textarea::make('rejection_reason')
                    ->default(null)
                    ->columnSpanFull(),
                DateTimePicker::make('approved_at'),
                DateTimePicker::make('paid_at'),
                TextInput::make('payout_reference')
                    ->default(null),
                Textarea::make('payout_metadata')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
