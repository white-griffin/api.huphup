<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Enums\ReviewStatus;
use App\Models\Review;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('کاربر'),

                TextColumn::make('reviewable_type')
                    ->label('نوع'),

                TextColumn::make('rating')
                    ->label('امتیاز'),

                TextColumn::make('title')
                    ->label('عنوان'),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn($state) => ReviewStatus::label((string)$state) ?? '—')
                    ->badge()
                    ->colors([
                        'warning' => ReviewStatus::PENDING->value,
                        'success' => ReviewStatus::APPROVED->value,
                        'danger' => ReviewStatus::REJECTED->value,
                    ]),

                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ReviewStatus::labels()),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('تایید')
                    ->color('success')
                    ->action(fn(Review $record) =>
                        $record->approve()
                ),

                Action::make('reject')
                    ->label('رد')
                    ->color('danger')
                    ->action(fn(Review $record) =>
                        $record->reject()
                    ),

            ]);
    }
}
