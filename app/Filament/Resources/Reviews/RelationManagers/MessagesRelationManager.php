<?php

namespace App\Filament\Resources\Reviews\RelationManagers;

use App\Enums\ReviewStatus;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $relatedResource = ReviewResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('author.name')
                    ->label('فرستنده'),

                TextColumn::make('author_type')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->label('نوع'),

                TextColumn::make('body')
                    ->limit(80),

                IconColumn::make('parent_id')
                    ->boolean()
                    ->label('Reply'),

                TextColumn::make('created_at')
                    ->since(),
            ])
            ->headerActions([
                ViewAction::make(),

                Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(
                        fn(Review $record) =>
                        $record->approve()
                    ),

                Action::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(
                        fn(Review $record) =>
                        $record->reject()
                    ),
            ]);
    }
}
