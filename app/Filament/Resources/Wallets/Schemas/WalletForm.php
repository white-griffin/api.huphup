<?php

namespace App\Filament\Resources\Wallets\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WalletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('owner_type')
                    ->required(),
                TextInput::make('owner_id')
                    ->required()
                    ->numeric(),
                TextInput::make('available_balance')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('pending_balance')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
