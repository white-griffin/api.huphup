<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\ActivityStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('تصویر')
                    ->square() // اگر مربعی می‌خوای
                    ->height(50) // کنترل سایز
                    ->rounded(), // گوشه‌های گرد

                TextColumn::make('full_name')
                    ->label('نام و نام خانوادگی')
                    ->getStateUsing(fn ($record) => trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? '')))
                    ->searchable(query: function ($query, $search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('email')
                    ->label('ایمیل')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('mobile')
                    ->label('موبایل')
                    ->searchable(),

                TextColumn::make('activity_status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn ($state) => ActivityStatus::label((string) $state) ?? '—')
                    ->badge()
                    ->colors([
                        'success'   => fn ($record) => (string) $record->activity_status === ActivityStatus::ACTIVE->value,
                        'danger' => fn ($record) => (string) $record->activity_status === ActivityStatus::INACTIVE->value,
                    ]),

                TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->jalaliDate('Y/m/d'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])->recordActionsColumnLabel('عملیات')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
