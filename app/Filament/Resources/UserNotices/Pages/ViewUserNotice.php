<?php

namespace App\Filament\Resources\UserNotices\Pages;

use App\Enums\UserNoticeTypes;
use App\Filament\Resources\UserNotices\UserNoticeResource;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Morilog\Jalali\Jalalian;

class ViewUserNotice extends ViewRecord
{

    protected static string $resource = UserNoticeResource::class;

    public function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.fullName')
                    ->label('کاربر')
                    ->placeholder('همه ی کاربران'),

                TextEntry::make('type')
                    ->label('نوع')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) =>
                            UserNoticeTypes::label($state)
                    ),

                TextEntry::make('title')
                    ->label('عنوان'),

                TextEntry::make('created_at')
                    ->label('تاریخ ثبت')
                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : null),

                TextEntry::make('body')
                    ->label('متن اعلان')
                    ->html()
                    ->columnSpanFull(),


                KeyValueEntry::make('data')
                    ->label('اطلاعات')
                    ->columnSpanFull(),


            ]);
    }
}
