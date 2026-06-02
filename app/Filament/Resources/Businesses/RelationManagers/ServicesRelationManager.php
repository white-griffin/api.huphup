<?php

namespace App\Filament\Resources\Businesses\RelationManagers;

use App\Enums\ActivityStatus;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';
    protected static ?string $title = 'سرویس‌ها';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('service_id')
                    ->relationship('service', 'name')  // ← pivot فرق داره، ببین پایین
                    ->required()
                    ->label('سرویس'),

                TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->label('قیمت'),

                TextInput::make('duration')
                    ->numeric()
                    ->label('مدت زمان (دقیقه)'),

                KeyValue::make('settings')
                    ->label('تنظیمات'),

                Radio::make('activity_status')
                    ->label('وضعیت')
                    ->options(ActivityStatus::labels())
                    ->default(ActivityStatus::ACTIVE->value)
                    ->inline(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('نام سرویس'),
                TextColumn::make('pivot.price')->label('قیمت'),
                TextColumn::make('pivot.duration')->label('مدت'),
                TextColumn::make('pivot.activity_status')
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
            ->headerActions([
                AttachAction::make()->preloadRecordSelect()
                    ->form(fn (AttachAction $action) => [
                        $action->getRecordSelect(),
                        TextInput::make('price')
                            ->label('قیمت')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('duration')
                            ->label('مدت زمان')
                            ->numeric()
                            ->minValue(0),
                        KeyValue::make('settings')
                        ->label('تنظیمات'),
                        Radio::make('activity_status')
                            ->label('وضعیت')
                            ->options(ActivityStatus::labels())
                            ->default(ActivityStatus::ACTIVE->value)
                            ->inline(),
                    ]),
                ])
            ->actions([
                EditAction::make(),
                DetachAction::make(),
            ]);
    }
}
