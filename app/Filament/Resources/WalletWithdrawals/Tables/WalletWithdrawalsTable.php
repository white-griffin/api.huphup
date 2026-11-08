<?php

namespace App\Filament\Resources\WalletWithdrawals\Tables;

use App\Enums\WalletWithdrawalStatuses;
use App\Models\WalletWithdrawal;
use App\Services\Wallet\WalletService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WalletWithdrawalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('شناسه')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('business_id')
                    ->label('کسب‌وکار')
                    ->formatStateUsing(
                        fn ($state, WalletWithdrawal $record) =>
                            $record->business?->name ?? "Business #{$state}"
                    )
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('مبلغ درخواست')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' تومان')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(
                        fn (WalletWithdrawalStatuses $state) => $state->label((string) $state->value ?? '')
                    )
                    ->color(fn (WalletWithdrawalStatuses $state) => match ($state) {
                        WalletWithdrawalStatuses::PENDING->value => 'warning',
                        WalletWithdrawalStatuses::APPROVED->value => 'info',
                        WalletWithdrawalStatuses::REJECTED->value => 'danger',
                        WalletWithdrawalStatuses::PAID->value => 'success',
                    }),

                TextColumn::make('payout_reference')
                    ->label('شماره پیگیری')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('تاریخ درخواست')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),

                TextColumn::make('approved_at')
                    ->label('تاریخ تأیید')
                    ->dateTime('Y/m/d H:i')
                    ->placeholder('-'),

                TextColumn::make('paid_at')
                    ->label('تاریخ پرداخت')
                    ->dateTime('Y/m/d H:i')
                    ->placeholder('-'),
            ])
            ->recordActions([
                // تأیید درخواست
                Action::make('approve')
                    ->label('تأیید درخواست')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأیید درخواست برداشت')
                    ->modalDescription('آیا از تأیید این درخواست اطمینان دارید؟')
                    ->modalSubmitActionLabel('تأیید')
                    ->visible(fn (WalletWithdrawal $record) =>
                        $record->status == WalletWithdrawalStatuses::PENDING->value
                    )
                    ->action(function (WalletWithdrawal $record) {
                        app(WalletService::class)->approveWithdrawal(
                            withdrawalId: $record->id,
                        );

                        Notification::make()
                            ->title('درخواست برداشت تأیید شد.')
                            ->success()
                            ->send();
                    }),

                // رد درخواست
                Action::make('reject')
                    ->label('رد درخواست')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->schema([
                        Textarea::make('reason')
                            ->label('دلیل رد درخواست')
                            ->required()
                            ->minLength(5)
                            ->maxLength(2000),
                    ])
                    ->modalHeading('رد درخواست برداشت')
                    ->modalSubmitActionLabel('رد درخواست')
                    ->visible(fn (WalletWithdrawal $record) =>
                        $record->status == WalletWithdrawalStatuses::PENDING->value
                    )
                    ->action(function (
                        array $data,
                        WalletWithdrawal $record
                    ) {
                        app(WalletService::class)->rejectWithdrawal(
                            withdrawalId: $record->id,
                            reason: $data['reason'],
                        );

                        Notification::make()
                            ->title('درخواست برداشت رد شد.')
                            ->success()
                            ->send();
                    }),

                // ثبت پرداخت
                Action::make('markAsPaid')
                    ->label('ثبت پرداخت')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->schema([
                        TextInput::make('payout_reference')
                            ->label('شماره پیگیری واریز')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->modalHeading('ثبت پرداخت درخواست برداشت')
                    ->modalDescription(
                        'پس از واریز وجه به حساب کسب‌وکار، این عملیات را انجام دهید.'
                    )
                    ->modalSubmitActionLabel('ثبت پرداخت')
                    ->visible(fn (WalletWithdrawal $record) =>
                        $record->status == WalletWithdrawalStatuses::APPROVED->value
                    )
                    ->action(function (
                        array $data,
                        WalletWithdrawal $record
                    ) {
                        app(WalletService::class)->markWithdrawalAsPaid(
                            withdrawalId: $record->id,
                            payoutReference: $data['payout_reference'],
                        );

                        Notification::make()
                            ->title('پرداخت با موفقیت ثبت شد.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
