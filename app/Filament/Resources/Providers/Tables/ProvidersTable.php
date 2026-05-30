<?php

namespace App\Filament\Resources\Providers\Tables;

use App\Enums\VerificationStatuses;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProvidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('نام و نام خانوادگی')
                    ->getStateUsing(fn ($record) => trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? '')))
                    ->searchable(query: function ($query, $search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('mobile')
                    ->label('موبایل')
                    ->searchable(),

                TextColumn::make('city.name')
                    ->label('شهر')
                    ->sortable(),

                IconColumn::make('shahkar_verified')
                    ->label('احراز شاهکار')
                    ->sortable()
                    ->boolean(),

                TextColumn::make('verification_status')
                    ->label('احراز سامانه')
                    ->formatStateUsing(fn ($state) => VerificationStatuses::label($state))
                    ->sortable(),

                TextColumn::make('documents_count')
                    ->label('تعداد مدارک')
                    ->counts('documents')
                    ->badge()
                    ->color('info'),

                TextColumn::make('verified_at')
                    ->label('تاریخ احراز')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->jalaliDateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('verify')
                    ->label('تأیید')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأیید تأمین‌کننده')
                    ->modalDescription('از تأیید این تأمین‌کننده مطمئنی؟')
                    ->visible(fn (Model $record) => $record->verification_status !== VerificationStatuses::ACTIVE->value)
                    ->action(fn (Model $record) => $record->update([
                        'verification_status' => VerificationStatuses::ACTIVE->value,
                    ])),
                Action::make('reject')
                    ->label('رد')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('رد تأمین‌کننده')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('دلیل رد')
                            ->required(),
                    ])
                    ->visible(fn (Model $record) => $record->verification_status !== VerificationStatuses::REJECTED->value)
                    ->action(function (Model $record, array $data) {
                        $record->update([
                            'verification_status' => VerificationStatuses::REJECTED->value,
                            'rejection_reason'    => $data['rejection_reason'],
                        ]);
                    }),
                EditAction::make(),
            ])->recordActionsColumnLabel('عملیات')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
