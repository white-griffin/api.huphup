<?php

namespace App\Filament\Resources\ChatService\Conversations;

use App\Filament\Resources\ChatService\Conversations\Pages\ListConversations;
use App\Filament\Resources\ChatService\Conversations\Pages\ViewConversation;
use App\Filament\Resources\ChatService\Conversations\RelationManagers\MembersRelationManager;
use App\Filament\Resources\ChatService\Conversations\Schemas\ConversationForm;
use App\Filament\Resources\ChatService\Conversations\Tables\ConversationsTable;
use App\Models\MongoDB\Conversation;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Conversations';

    protected static ?string $modelLabel = 'Conversation';

    protected static ?string $pluralModelLabel = 'Conversations';

    protected static string|null|\UnitEnum $navigationGroup = 'Chat';

    protected static ?string $recordTitleAttribute = 'Chat';

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
                ->label('Title')
                ->placeholder('-'),

            TextEntry::make('type')
                ->label('Type')
                ->badge(),

            TextEntry::make('creator.nickname')
                ->label('Created By')
                ->placeholder('-'),

            TextEntry::make('createdAt')
                ->label('Created At')
                ->dateTime('Y-m-d H:i'),

            TextEntry::make('updatedAt')
                ->label('Updated At')
                ->dateTime('Y-m-d H:i'),
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
