<?php

namespace App\Filament\Resources\ChatService\SupportChats;


use App\Filament\Resources\ChatService\SupportChats\Pages\ListSupportChats;
use App\Filament\Resources\ChatService\SupportChats\Pages\ViewSupportChat;
use App\Filament\Resources\ChatService\SupportChats\Schemas\SupportChatsForm;
use App\Filament\Resources\ChatService\SupportChats\Tables\SupportChatsTable;
use App\Models\MongoDB\ChatUser;
use App\Models\MongoDB\Conversation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use MongoDB\BSON\ObjectId;

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
        $query = parent::getEloquentQuery()
            ->where('context', 'SUPPORT')
            ->where('status', 'OPEN');

        $admin = auth('admin')->user();

        if (! $admin) {
            return $query->whereRaw([
                '_id' => null,
            ]);
        }

        /*
         * Super Admin:
         * تمام Support Chatها
         */
        if (
            $admin->hasRole('super-admin') ||
            $admin->hasRole('super_admin')
        ) {
            return $query;
        }

        /*
         * Admin معمولی:
         * فقط Chatهای assign شده به خودش
         */
        $chatUser = ChatUser::query()
            ->where('externalType', 'ADMIN')
            ->where('externalId', (string) $admin->id)
            ->first();

        if (! $chatUser) {
            return $query->whereRaw([
                '_id' => null,
            ]);
        }

        return $query->where(
            'assignedTo',
            new ObjectId((string) $chatUser->id)
        );
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
