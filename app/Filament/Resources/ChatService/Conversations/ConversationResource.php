<?php

namespace App\Filament\Resources\ChatService\Conversations;

use App\Enums\ChatTypes;
use App\Filament\Resources\ChatService\Conversations\Pages\ListConversations;
use App\Filament\Resources\ChatService\Conversations\Pages\ViewConversation;
use App\Filament\Resources\ChatService\Conversations\RelationManagers\MembersRelationManager;
use App\Filament\Resources\ChatService\Conversations\Schemas\ConversationForm;
use App\Filament\Resources\ChatService\Conversations\Tables\ConversationsTable;
use App\Models\MongoDB\Conversation;
use App\Models\User;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'مکالمات';

    protected static ?string $modelLabel = 'مکالمه';

    protected static ?string $pluralModelLabel = 'مکالمه';

    protected static string|null|\UnitEnum $navigationGroup = 'چت';

    protected static ?string $recordTitleAttribute = 'چت';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'creator',
                'members.user',
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return ConversationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConversationsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('title')
                ->label('عنوان')
                ->placeholder('مکالمه خصوصی'),

            TextEntry::make('type')
                ->label('نوع چت')
                ->formatStateUsing(fn ($state) => ChatTypes::label($state))
                ->badge(),

            TextEntry::make('creator_name')
                ->label(' سازنده چت ')
                ->state(function ($record) {
                    $creator = $record->creator;

                    if (! $creator || $creator->externalType !== 'USER') {
                        return $creator?->nickname ?? '-';
                    }

                    $user = User::query()
                        ->find((int) $creator->externalId);

                    return $user
                        ? trim($user->first_name . ' ' . $user->last_name).' - '.$user->mobile
                        : ($creator->nickname ?? '-');
                }),

            TextEntry::make('createdAt')
                ->label('تاریخ ایجاد')
                ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y-m-d H:i') : null),

        ]);
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConversations::route('/'),
            'view' => ViewConversation::route('/{record}'),
        ];
    }
}
