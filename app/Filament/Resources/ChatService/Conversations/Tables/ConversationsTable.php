<?php

namespace App\Filament\Resources\ChatService\Conversations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('createdAt', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('id')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'primary' => 'DIRECT',
                        'success' => 'GROUP',
                    ]),

                TextColumn::make('creator.nickname')
                    ->label('Created By')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('createdAt')
                    ->label('Created At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('updatedAt')
                    ->label('Updated At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'DIRECT' => 'Direct',
                        'GROUP' => 'Group',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
