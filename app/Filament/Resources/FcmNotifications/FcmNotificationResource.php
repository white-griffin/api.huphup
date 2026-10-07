<?php

namespace App\Filament\Resources\FcmNotifications;

use App\Filament\Resources\FcmNotifications\Pages\CreateFcmNotification;
use App\Filament\Resources\FcmNotifications\Pages\EditFcmNotification;
use App\Filament\Resources\FcmNotifications\Pages\ListFcmNotifications;
use App\Filament\Resources\FcmNotifications\Schemas\FcmNotificationForm;
use App\Filament\Resources\FcmNotifications\Tables\FcmNotificationsTable;
use App\Models\FcmNotification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FcmNotificationResource extends Resource
{
    protected static ?string $model = FcmNotification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    protected static string|null|\UnitEnum $navigationGroup = 'مدیریت اعضا';

    protected static ?string $navigationLabel = 'اعلانات fcm';

    protected static ?string $modelLabel = 'اعلان fcm';

    protected static ?string $pluralModelLabel = 'اعلانات fcm';

    public static function form(Schema $schema): Schema
    {
        return FcmNotificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FcmNotificationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFcmNotifications::route('/'),
            'create' => CreateFcmNotification::route('/create'),
            'edit' => EditFcmNotification::route('/{record}/edit'),
        ];
    }
}
