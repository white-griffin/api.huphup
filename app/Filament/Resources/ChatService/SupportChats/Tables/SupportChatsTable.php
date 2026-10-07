<?php

namespace App\Filament\Resources\ChatService\SupportChats\Tables;

use App\Models\Admin;
use App\Models\MongoDB\ChatUser;
use App\Models\User;
use App\Services\MongoChatService\SupportChatService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;

class SupportChatsTable
{
    public static function configure(Table $table): Table
    {
        $admin = auth('admin')->user();

        $isSuperAdmin = $admin && (
                $admin->hasRole('super-admin') ||
                $admin->hasRole('super_admin')
            );

        return $table
            ->columns([
                TextColumn::make('customer')
                    ->label('کاربر')
                    ->state(function ($record) {
                        $creator = $record->creator;

                        if (
                            ! $creator ||
                            $creator->externalType !== 'USER'
                        ) {
                            return $creator?->nickname ?? '-';
                        }

                        $user = User::query()
                            ->find((int) $creator->externalId);

                        return $user
                            ? trim(
                                $user->first_name . ' ' .
                                $user->last_name
                            )
                            : ($creator->nickname ?? '-');
                    })
                    ->searchable(),

                TextColumn::make('assigned_admin')
                    ->label('Assigned Admin')
                    ->state(function ($record) {
                        $assignedTo = $record->assignedTo;

                        if (! $assignedTo) {
                            return '-';
                        }

                        $chatUser = ChatUser::query()->find(
                            $assignedTo
                        );

                        if (! $chatUser) {
                            return '-';
                        }

                        $admin = Admin::query()
                            ->find((int) $chatUser->externalId);

                        if (! $admin) {
                            return $chatUser->nickname ?? '-';
                        }

                        return $admin->name
                            ?? $admin->username
                            ?? $chatUser->nickname
                            ?? '-';
                    }),

                TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'OPEN' => 'success',
                        'CLOSED' => 'gray',
                        default => 'warning',
                    }),

                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->formatStateUsing(fn($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i') : null)
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                Action::make('assignAdmin')
                    ->label('Assign Admin')
                    ->icon('heroicon-m-user')
                    ->visible(fn () => $isSuperAdmin)
                    ->form([
                        Select::make('admin_id')
                            ->label('Admin')
                            ->options(function () {
                                return Admin::query()
                                    ->whereHas('roles', function ($query) {
                                        $query->whereIn('name', [
                                            'support',
                                            'super-admin',
                                            'super_admin',
                                        ]);
                                    })
                                    ->get()
                                    ->mapWithKeys(
                                        fn (Admin $admin) => [
                                            $admin->id =>
                                                $admin->name
                                                ?? $admin->username
                                                    ?? "Admin #{$admin->id}",
                                        ]
                                    )
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->fillForm(function ($record): array {
                        $assignedTo = $record->assignedTo;

                        if (! $assignedTo) {
                            return [
                                'admin_id' => null,
                            ];
                        }

                        $chatUser = ChatUser::query()->find(
                            $assignedTo
                        );

                        return [
                            'admin_id' => $chatUser
                                ? (int) $chatUser->externalId
                                : null,
                        ];
                    })
                    ->action(function (
                        $record,
                        array $data
                    ) {
                        app(SupportChatService::class)
                            ->assignAdmin(
                                $record,
                                (int) $data['admin_id']
                            );
                    })
                    ->successNotificationTitle(
                        'Support chat reassigned successfully.'
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
//                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
