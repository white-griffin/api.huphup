<?php

namespace App\Filament\Resources\RoutineTemplates\Tables;

use App\Enums\ActivityStatus;
use App\Enums\RoutineCategoryTypes;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RoutineTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable(),

                TextColumn::make('species.name')
                    ->label('نوع حیوان')
                    ->placeholder('همه'),

                TextColumn::make('routine_category')
                    ->label('دسته'),

                TextColumn::make('default_interval_days')
                    ->label('بازه روز'),

                TextColumn::make('activity_status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn ($state) => ActivityStatus::label((string) $state) ?? '—')
                    ->badge()
                    ->colors([
                        'success'   => fn ($record) => (string) $record->activity_status === ActivityStatus::ACTIVE->value,
                        'danger' => fn ($record) => (string) $record->activity_status === ActivityStatus::INACTIVE->value,
                    ]),

                TextColumn::make('actions_count')
                    ->label('تعداد پیشنهادها')
                    ->counts('actions'),
            ])
            ->filters([
                SelectFilter::make('routine_category')
                    ->label('دسته')
                    ->options(RoutineCategoryTypes::labels()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
