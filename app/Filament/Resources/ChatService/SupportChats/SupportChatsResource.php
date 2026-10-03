<?php

namespace App\Filament\Resources\ChatService\SupportChats;

use App\Filament\Resources\ChatService\SupportChats\Pages\CreateSupportChats;
use App\Filament\Resources\ChatService\SupportChats\Pages\EditSupportChats;
use App\Filament\Resources\ChatService\SupportChats\Pages\ListSupportChats;
use App\Filament\Resources\ChatService\SupportChats\Pages\ViewSupportChat;
use App\Filament\Resources\ChatService\SupportChats\Schemas\SupportChatsForm;
use App\Filament\Resources\ChatService\SupportChats\Tables\SupportChatsTable;
use App\Models\MongoDB\Conversation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupportChatsResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Lifebuoy;

    protected static ?string $navigationLabel = 'چت های پشتیبانی';

    protected static ?string $modelLabel = 'چت پشتیبانی';

    protected static ?string $pluralModelLabel = 'چت های پشتیبانی';

    protected static string|null|\UnitEnum $navigationGroup = 'سرویس چت';

    protected static ?string $recordTitleAttribute = 'چت پشتیبانی';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('context', 'SUPPORT')
            ->where('status', 'OPEN')
            ->with([
                'creator',
                'members.user',
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return SupportChatsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupportChatsTable::configure($table);
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
            'index' => ListSupportChats::route('/'),
            'view' => ViewSupportChat::route('/{record}'),
        ];
    }
}
