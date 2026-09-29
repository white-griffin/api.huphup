<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Enums\PaymentGateways;
use App\Enums\PaymentStatuses;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'پرداخت‌ها';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('شناسه پرداخت')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('مبلغ')
                    ->numeric()
                    ->suffix(' تومان')
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label('وضعیت پرداخت')
                    ->formatStateUsing(fn ($state) => PaymentStatuses::label((string) $state) ?? '—')
                    ->searchable(),

                TextColumn::make('transaction_id')
                    ->label('شناسه تراکنش')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('settled_at')
                    ->label('تاریخ تسویه')
                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : null)
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : null)
                    ->sortable(),

                TextColumn::make('details')
                    ->label('جزئیات')
                    ->state(fn ($record) => 'مشاهده جزئیات')
                    ->color('primary')
                    ->icon(Heroicon::Eye)
                    ->action(
                        Action::make('details')
                            ->label('جزئیات پرداخت')
                            ->modalHeading('جزئیات پرداخت')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('بستن')
                            ->infolist(function (Schema $schema, $record) {
                                return $schema
                                    ->record($record)
                                    ->components([

                                        TextEntry::make('user.fullName')
                                            ->label('کاربر')
                                            ->placeholder('-'),

                                        TextEntry::make('gateway')
                                            ->label('درگاه پرداخت')
                                            ->formatStateUsing(fn ($state) => PaymentGateways::label((string) $state) ?? '—'),

                                        TextEntry::make('original_amount')
                                            ->label('مبلغ اصلی')
                                            ->numeric()
                                            ->suffix(' تومان'),

                                        TextEntry::make('coupon_discount_amount')
                                            ->label('مبلغ تخفیف')
                                            ->numeric()
                                            ->suffix(' تومان'),

                                        TextEntry::make('transaction_id')
                                            ->label('شناسه تراکنش')
                                            ->placeholder('-'),

                                        TextEntry::make('coupon.code')
                                            ->label('کد تخفیف')
                                            ->placeholder('-'),
                                    ])
                                    ->columns(2);
                            }),
                    ),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
