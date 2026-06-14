<?php

namespace App\Filament\Resources\Groups\Tables;

use App\Enums\ActivityStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class GroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('تصویر')
                    ->circular(),

                TextColumn::make('name')
                    ->label('عنوان گروه')
                    ->searchable(),

                TextColumn::make('creator.name')
                    ->label('ایجادکننده')
                    ->searchable(['first_name', 'last_name'])
                    ->default('—'),

                TextColumn::make('participants_count')
                    ->label('تعداد اعضا')
                    ->counts('participants'),

                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->jalaliDate(),

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
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
            ])->recordActionsColumnLabel('عملیات')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn($query) => $query->with('creator'));
    }
}
