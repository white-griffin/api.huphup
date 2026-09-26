<?php

namespace App\Filament\Resources\ChatService\Conversations\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;

class MembersRelationManager extends RelationManager
{

    protected static string $relationship = 'members';

    protected static ?string $title = 'اعضا';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.nickname')
                    ->label('کاربر')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.externalType')
                    ->label('نقش')
                    ->badge(),

                Tables\Columns\TextColumn::make('user.externalId')
                    ->label('شناسه خارجی')
                    ->searchable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('نقش')
                    ->badge(),

                Tables\Columns\TextColumn::make('joinedAt')
                    ->label('تاریخ عضویت')
                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y-m-d H:i') : null)
                    ->sortable(),

                Tables\Columns\TextColumn::make('leftAt')
                    ->label('تاریخ خروج')
                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y-m-d H:i') : null)
                    ->placeholder('-'),
            ])
            ->defaultSort('joinedAt', 'asc')
            ->actions([])
            ->bulkActions([]);
    }
}
