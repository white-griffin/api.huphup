<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ActivityStatus;
use App\Enums\PublicationStatus;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('اطلاعات محصول')
                    ->columns(2)
                    ->schema([

                        TextEntry::make('business.name')
                            ->label('فروشگاه'),

                        TextEntry::make('brand.name')
                            ->label('برند'),

                        TextEntry::make('name')
                            ->label('نام محصول'),

                        TextEntry::make('slug')
                            ->label('اسلاگ'),

                        TextEntry::make('publication_status')
                            ->label('وضعیت انتشار')
                            ->formatStateUsing(fn ($state) => PublicationStatus::label((string) $state) ?? '-')
                            ->badge(),

                        TextEntry::make('categories.name')
                            ->label('دسته بندی ها')
                            ->badge(),

                        TextEntry::make('description')
                            ->label('توضیحات')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('created_at')
                            ->label('تاریخ ایجاد')
                            ->jalaliDate('Y/m/d H:i'),

                        TextEntry::make('updated_at')
                            ->label('آخرین بروزرسانی')
                            ->jalaliDate('Y/m/d H:i'),

                    ])
                    ->columnSpanFull(),

                Section::make('تنوع')
                    ->schema([

                        RepeatableEntry::make('variations')
                            ->label('انواع تنوع محصولات')
                            ->grid(2)
                            ->contained()
                            ->columns(2)
                            ->schema([

                                TextEntry::make('sku')
                                    ->label('SKU'),

                                TextEntry::make('price')
                                    ->label('قیمت')
                                    ->numeric(decimalPlaces: 0)
                                    ->suffix(' تومان'),

                                TextEntry::make('discount_price')
                                    ->label('قیمت با تخفیف')
                                    ->numeric(decimalPlaces: 0)
                                    ->suffix(' تومان')
                                    ->placeholder('-'),

                                TextEntry::make('stock')
                                    ->label('موجودی'),

                                IconEntry::make('is_default')
                                    ->label('پیشفرض')
                                    ->boolean(),

                                TextEntry::make('activity_status')
                                    ->label('وضعیت')
                                    ->formatStateUsing(fn ($state) => ActivityStatus::label((string) $state) ?? '-')
                                    ->badge(),

                                TextEntry::make('attributes')
                                    ->label('ویژگی‌ها')
                                    ->state(function ($record) {
                                        return $record->variationAttributes
                                            ->map(fn ($attribute) => "{$attribute->attribute->name}: {$attribute->option->label}")
                                            ->toArray();
                                    })
                                    ->badge()
                                    ->separator(',')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary')

                            ])

                    ])
                    ->columnSpanFull(),

                Section::make('تصاویر محصول')
                    ->schema([

                        ImageEntry::make('primaryImage.name')
                            ->label('تصویر شاخص')
                            ->disk('public')
                            ->height(320)
                            ->url(fn ($state) => asset('storage/' . $state))
                            ->openUrlInNewTab(),

                        RepeatableEntry::make('images')
                            ->label('گالری تصاویر')
                            ->grid(6)
                            ->schema([

                                ImageEntry::make('name')
                                    ->hiddenLabel()
                                    ->disk('public')
                                    ->height(110)
                                    ->url(fn ($state) => asset('storage/' . $state))
                                    ->openUrlInNewTab(),

                            ])

                    ])
                    ->columnSpanFull()

            ]);
    }
}
