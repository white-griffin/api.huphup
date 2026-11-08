<?php

namespace App\Filament\Resources\WalletWithdrawals\Pages;

use App\Enums\WalletWithdrawalStatuses;
use App\Filament\Resources\WalletWithdrawals\WalletWithdrawalResource;
use App\Models\WalletWithdrawal;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListWalletWithdrawals extends ListRecords
{
    protected static string $resource = WalletWithdrawalResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('همه')
                ->badge(WalletWithdrawal::count()),

            'pending' => Tab::make('در انتظار بررسی')
                ->badge(
                    WalletWithdrawal::where(
                        'status',
                        WalletWithdrawalStatuses::PENDING->value
                    )->count()
                )
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) =>
                $query->where(
                    'status',
                    WalletWithdrawalStatuses::PENDING->value
                )
                ),

            'approved' => Tab::make('تأیید شده')
                ->badge(
                    WalletWithdrawal::where(
                        'status',
                        WalletWithdrawalStatuses::APPROVED->value
                    )->count()
                )
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) =>
                $query->where(
                    'status',
                    WalletWithdrawalStatuses::APPROVED->value
                )
                ),

            'rejected' => Tab::make('رد شده')
                ->badge(
                    WalletWithdrawal::where(
                        'status',
                        WalletWithdrawalStatuses::REJECTED->value
                    )->count()
                )
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) =>
                $query->where(
                    'status',
                    WalletWithdrawalStatuses::REJECTED->value
                )
                ),

            'paid' => Tab::make('پرداخت شده')
                ->badge(
                    WalletWithdrawal::where(
                        'status',
                        WalletWithdrawalStatuses::PAID->value
                    )->count()
                )
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) =>
                $query->where(
                    'status',
                    WalletWithdrawalStatuses::PAID->value
                )
                ),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'pending';
    }
}
