<?php

namespace App\Filament\Resources\Businesses\Tables;

use App\Enums\ActivityStatus;
use App\Enums\BusinessTypes;
use App\Enums\VerificationStatuses;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BusinessesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider.fullName')
                    ->label('تامین کننده')
                    ->sortable(),
                TextColumn::make('business_type')
                    ->label('نوع کسب و کار')
                    ->formatStateUsing(fn ($state) => BusinessTypes::label($state))
                    ->sortable(),
                TextColumn::make('name')
                    ->label('نام')
                    ->searchable(),
                TextColumn::make('license_code')
                    ->label('کد ثبتی')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('logo')
                    ->label('لوگو')
                    ->circular(),
                TextColumn::make('phone')
                    ->label('تلفن')
                    ->searchable(),
                TextColumn::make('website')
                    ->label('وب سایت')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('city.name')
                    ->label('شهر')
                    ->searchable(),
                TextColumn::make('postal_code')
                    ->label('کد پستی')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('verification_status')
                    ->label('احراز سامانه')
                    ->formatStateUsing(fn ($state) => VerificationStatuses::label($state))
                    ->sortable(),
                TextColumn::make('activity_status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn ($state) => ActivityStatus::label((string) $state) ?? '—')
                    ->badge()
                    ->colors([
                        'success'   => fn ($record) => (string) $record->activity_status === ActivityStatus::ACTIVE->value,
                        'danger' => fn ($record) => (string) $record->activity_status === ActivityStatus::INACTIVE->value,
                    ]),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
