<?php

namespace App\Filament\Resources\BusinessProfiles\Tables;

use App\Enums\MemberActivityStatuses;
use App\Enums\ProviderTypes;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BusinessProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('لوگو')
                    ->square(),
                TextColumn::make('user.fullName')
                    ->label('کاربر')
                    ->sortable(),
                TextColumn::make('provider_type')
                    ->label('نوع خدمت')
                    ->formatStateUsing(fn ($state) => ProviderTypes::label($state))
                    ->sortable(),
                TextColumn::make('business_name')
                    ->label('نام')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('تلفن')
                    ->searchable(),
                TextColumn::make('city.name')
                    ->label('شهر')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('activity_status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn ($state) => MemberActivityStatuses::label((string) $state) ?? '—')
                    ->badge(),
                TextColumn::make('website')
                    ->label('وبسایت')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
