<?php

namespace App\Filament\Resources\ChatService\Conversations\Tables;

use App\Enums\ChatTypes;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;

class ConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('createdAt', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('شناسه')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->placeholder('خصوصی'),

                TextColumn::make('type')
                    ->label('نوع مکالمه')
                    ->badge()
                    ->formatStateUsing(fn($state) => ChatTypes::label($state))
                    ->colors([
                        'primary' => 'DIRECT',
                        'success' => 'GROUP',
                    ]),

                TextColumn::make('creator.nickname')
                    ->label('سازنده ')
                    ->searchable()
                    ->state(function ($record) {
                        $creator = $record->creator;

                        if (!$creator || $creator->externalType !== 'USER') {
                            return $creator?->nickname ?? '-';
                        }

                        $user = User::query()
                            ->find((int)$creator->externalId);

                        return $user
                            ? trim($user->first_name . ' ' . $user->last_name)
                            : ($creator->nickname ?? '-');
                    })
                    ->placeholder('-'),

                TextColumn::make('createdAt')
                    ->label('تاریخ ایجاد')
                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y-m-d H:i') : null)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('نوع')
                    ->options([
                        'DIRECT' => 'Direct',
                        'GROUP' => 'Group',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])->recordActionsColumnLabel('عملیات')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
