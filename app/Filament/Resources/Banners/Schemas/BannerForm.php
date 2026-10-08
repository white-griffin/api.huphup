<?php

namespace App\Filament\Resources\Banners\Schemas;

use App\Enums\ActivityStatus;
use App\Enums\BannerPlacement;
use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات اصلی')
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان')
                            ->required()
                            ->maxLength(255),

                        Select::make('placement')
                            ->label('محل نمایش')
                            ->options(BannerPlacement::labels())
                            ->required()
                            ->native(false),

                        Select::make('activity_status')
                            ->label('وضعیت')
                            ->options([
                                ActivityStatus::ACTIVE->value => 'فعال',
                                ActivityStatus::INACTIVE->value => 'غیرفعال',
                            ])
                            ->default(ActivityStatus::ACTIVE->value)
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('تصاویر')
                    ->description(
                        'در صورت نبودن تصویر مخصوص، اپلیکیشن می‌تواند از تصویر اصلی استفاده کند.'
                    )
                    ->schema([
                        FileUpload::make('images.default')
                            ->label('تصویر اصلی')
                            ->image()
                            ->disk('public')
                            ->directory('banners')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(5120)
                            ->required(),

                        FileUpload::make('images.mobile')
                            ->label('تصویر موبایل')
                            ->image()
                            ->disk('public')
                            ->directory('banners')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(5120),

                        FileUpload::make('images.tablet')
                            ->label('تصویر تبلت')
                            ->image()
                            ->disk('public')
                            ->directory('banners')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(5120),
                    ])
                    ->columns(3),

                Section::make('مقصد تبلیغ')
                    ->description(
                        'مقصد اختیاری است. در صورت خالی بودن، بنر صرفاً نمایش داده می‌شود.'
                    )
                    ->schema([
                        MorphToSelect::make('target')
                            ->label('مقصد')
                            ->types([
                                MorphToSelect\Type::make('product')
                                    ->label('محصول')
                                    ->model(Product::class)
                                    ->titleAttribute('name'),

                                MorphToSelect\Type::make('business')
                                    ->label('بیزنس')
                                    ->model(Business::class)
                                    ->titleAttribute('name'),

                                MorphToSelect\Type::make('category')
                                    ->label('دسته‌بندی')
                                    ->model(Category::class)
                                    ->titleAttribute('name'),
                            ])
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(
                                fn(Set $set) => $set('target_url', null)
                            ),

                        TextInput::make('target_url')
                            ->label('آدرس مقصد')
                            ->url()
                            ->maxLength(2048)
                            ->placeholder('https://...')
                            ->visible(
                                fn(Get $get): bool => blank($get('target'))
                            )
                            ->live()
                            ->afterStateUpdated(
                                fn(Set $set) => $set('target', null)
                            ),
                    ])
                    ->columns(1),

                Section::make('زمان‌بندی')
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->jalali()
                            ->label('شروع نمایش')
                            ->native(false)
                            ->seconds(false),

                        DateTimePicker::make('ends_at')
                            ->jalali()
                            ->label('پایان نمایش')
                            ->native(false)
                            ->seconds(false)
                            ->afterOrEqual('starts_at'),
                    ])
                    ->columns(2),
            ]);
    }
}
