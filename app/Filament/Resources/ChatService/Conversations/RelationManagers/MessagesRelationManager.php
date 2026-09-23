<?php

namespace App\Filament\Resources\ChatService\Conversations\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{

    protected static string $relationship = 'messages';

    protected static ?string $title = 'Messages';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('createdAt', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('sender.nickname')
                    ->label('Sender')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge(),

                Tables\Columns\TextColumn::make('content')
                    ->label('Content')
                    ->limit(100)
                    ->wrap()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('createdAt')
                    ->label('Sent At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\IconColumn::make('editedAt')
                    ->label('Edited')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->editedAt !== null),

                Tables\Columns\IconColumn::make('deletedAt')
                    ->label('Deleted')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->deletedAt !== null),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
