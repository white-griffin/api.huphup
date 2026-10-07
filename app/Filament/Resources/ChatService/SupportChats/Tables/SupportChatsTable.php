<?php

namespace App\Filament\Resources\ChatService\SupportChats\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SupportChatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer')
                    ->label('کاربر')
                    ->state(function ($record) {
                        $creator = $record->creator;

                        if (
                            ! $creator ||
                            $creator->externalType !== 'USER'
                        ) {
                            return $creator?->nickname ?? '-';
                        }

                        $user = User::query()
                            ->find((int) $creator->externalId);

                        return $user
                            ? trim(
                                $user->first_name . ' ' .
                                $user->last_name
                            )
                            : ($creator->nickname ?? '-');
                    })
                    ->searchable(),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'OPEN' => 'success',
                        'CLOSED' => 'gray',
                        default => 'warning',
                    }),

                TextColumn::make('createdAt')
                    ->label('تاریخ ایجاد')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->defaultSort('updatedAt', 'desc')
            ->recordActions([
                EditAction::make(),
                ViewAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
