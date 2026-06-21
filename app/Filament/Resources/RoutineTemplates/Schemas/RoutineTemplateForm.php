<?php

namespace App\Filament\Resources\RoutineTemplates\Schemas;

use App\Enums\ActivityStatus;
use App\Enums\RoutineCategoryTypes;
use App\Models\Category;
use App\Models\Product;
use App\Models\Service;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoutineTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات روتین')
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان')
                            ->required()
                            ->maxLength(255),

                        Select::make('species_id')
                            ->label('نوع حیوان')
                            ->relationship('species', 'name_fa')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('routine_category')
                            ->label('دسته‌بندی مفهومی')
                            ->options(RoutineCategoryTypes::labels())
                            ->required(),

                        TextInput::make('default_interval_days')
                            ->label('بازه پیش‌فرض - روز')
                            ->numeric()
                            ->required(),

                        TextInput::make('reminder_days_before')
                            ->label('یادآوری چند روز قبل')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        FileUpload::make('image')
                            ->label('تصویر')
                            ->directory('routine_templates/images')
                            ->nullable(),

                        Textarea::make('description')
                            ->label('توضیحات')
                            ->columnSpanFull(),

                        Radio::make('activity_status')
                            ->label('وضعیت')
                            ->options(ActivityStatus::labels())
                            ->default(ActivityStatus::ACTIVE->value)
                            ->inline(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('پیشنهادهای مرتبط')
                    ->schema([
                        Repeater::make('actions')
                            ->label('اکشن‌ها')
                            ->relationship()
                            ->schema([
                                Select::make('target_type')
                                    ->label('نوع مقصد')
                                    ->options([
                                        'service' => 'سرویس',
                                        'product' => 'محصول',
                                        'category' => 'دسته‌بندی محصول',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn($set) => $set('target_id', null)),

                                Select::make('target_id')
                                    ->label('مقصد')
                                    ->required()
                                    ->searchable()
                                    ->options(function ($get) {
                                        return match ($get('target_type')) {
                                            'service' => Service::query()
                                                ->pluck('name', 'id')
                                                ->toArray(),

                                            'product' => Product::query()
                                                ->pluck('name', 'id')
                                                ->toArray(),

                                            'category' => Category::query()
                                                ->where('type', 1)
                                                ->pluck('name', 'id')
                                                ->toArray(),

                                            default => [],
                                        };
                                    }),

                                TextInput::make('priority')
                                    ->label('اولویت')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('افزودن پیشنهاد'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
