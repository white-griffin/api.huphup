<?php

namespace App\Filament\Resources\CommissionRules\Tables;

use App\Enums\ActivityStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommissionRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business_type')
                    ->label('نوع کسب و کار'),

                TextColumn::make('min_rating')
                    ->label('از'),

                TextColumn::make('max_rating')
                    ->label('تا'),

                TextColumn::make('commission_rate')
                    ->label('کمیسیون')
                    ->suffix('%'),

                TextColumn::make('priority')
                    ->label('اولویت'),

                TextColumn::make('activity_status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn ($state) => ActivityStatus::label((string) $state) ?? '—')
                    ->badge()
                    ->colors([
                        'success'   => fn ($record) => (string) $record->activity_status === ActivityStatus::ACTIVE->value,
                        'danger' => fn ($record) => (string) $record->activity_status === ActivityStatus::INACTIVE->value,
                    ]),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
