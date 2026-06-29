<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\PublicationStatus;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                        ->label('نام'),
                    TextEntry::make('slug')
                        ->label('اسلاگ'),
                    TextEntry::make('description')
                        ->label('توضیحات')
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('price')
                        ->label('قیمت اصلی'),
                    TextEntry::make('discount_price')
                        ->label('قیمت تخفیف خورده')
                        ->placeholder('-'),
                    TextEntry::make('stock')
                        ->label('موجودی')
                        ->numeric(),
                    TextEntry::make('sku')
                        ->label('SKU')
                        ->placeholder('-'),
                    TextEntry::make('publication_status')
                        ->label('وضعیت انتشار')
                        ->formatStateUsing(fn($state) => PublicationStatus::label((string)$state) ?? '—'),
                    TextEntry::make('created_at')
                        ->label('تاریخ ایجاد')
                        ->jalaliDate('Y/m/d')
                        ->placeholder('-'),
                    TextEntry::make('updated_at')
                        ->label('تاریخ ویرایش')
                        ->jalaliDate('Y/m/d')
                        ->placeholder('-'),
                ])->columnSpanFull(),

                Section::make('تصاویر محصول')
                    ->schema([

                        ImageEntry::make('primaryImage.name')
                            ->label('تصویر شاخص')
                            ->disk('public')
                            ->height(320)
                            ->url(fn ($state) => asset('storage/' . $state))
                            ->openUrlInNewTab()
                            ->extraImgAttributes([
                                'class' => 'rounded-lg object-cover'
                            ]),

                        RepeatableEntry::make('images')
                            ->label('گالری تصاویر')
                            ->grid(6)
                            ->schema([
                                ImageEntry::make('name')
                                    ->label('عکس')
                                    ->disk('public')
                                    ->height(110)
                                    ->url(fn ($state) => asset('storage/' . $state))
                                    ->openUrlInNewTab()
                                    ->extraImgAttributes([
                                        'class' => 'rounded-md object-cover cursor-zoom-in'
                                    ])
                            ])
                    ])->columnSpanFull()

            ]);
    }
}
