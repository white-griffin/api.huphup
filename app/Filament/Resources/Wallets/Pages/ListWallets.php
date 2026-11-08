<?php

namespace App\Filament\Resources\Wallets\Pages;

use App\Filament\Resources\Wallets\WalletResource;
use App\Models\Business;
use App\Models\User;
use App\Models\Wallet;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListWallets extends ListRecords
{
    protected static string $resource = WalletResource::class;

    public function getTabs(): array
    {
        $userType = (new User())->getMorphClass();
        $businessType = (new Business())->getMorphClass();

        return [
            'users' => Tab::make('کیف پول کاربران')
                ->icon('heroicon-o-user')
                ->badge(
                    Wallet::where('owner_type', $userType)->count()
                )
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where(
                        'owner_type',
                        $userType
                    )
                ),

            'businesses' => Tab::make('کیف پول کسب‌وکارها')
                ->icon('heroicon-o-building-storefront')
                ->badge(
                    Wallet::where('owner_type', $businessType)->count()
                )
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where(
                        'owner_type',
                        $businessType
                    )
                ),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'users';
    }
}
