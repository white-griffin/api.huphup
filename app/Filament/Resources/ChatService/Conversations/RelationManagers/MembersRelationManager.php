<?php

namespace App\Filament\Resources\ChatService\Conversations\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{

    protected static string $relationship = 'members';

    protected static ?string $title = 'Members';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.nickname')
                    ->label('User')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.externalType')
                    ->label('Type')
                    ->badge(),

                Tables\Columns\TextColumn::make('user.externalId')
                    ->label('External ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge(),

                Tables\Columns\TextColumn::make('joinedAt')
                    ->label('Joined At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('leftAt')
                    ->label('Left At')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('-'),
            ])
            ->defaultSort('joinedAt', 'asc')
            ->actions([])
            ->bulkActions([]);
    }
}
