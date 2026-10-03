<?php

namespace App\Services\MongoChatService;

use App\Models\Admin;
use App\Models\User;
use App\Models\MongoDB\ChatUser;
use App\Models\MongoDB\Conversation;
use App\Models\MongoDB\ConversationMember;
use Illuminate\Support\Facades\Cache;
use MongoDB\BSON\ObjectId;

class SupportChatService
{
    private const CONTEXT = 'SUPPORT';

    private const STATUS_OPEN = 'OPEN';

    public function start(User $user): Conversation
    {
        return Cache::lock(
            "support-chat:user:{$user->id}",
            10
        )->block(5, function () use ($user) {

            $userChatUser = $this->resolveUserChatUser($user);

            $existing = Conversation::query()
                ->where('context', self::CONTEXT)
                ->where('status', self::STATUS_OPEN)
                ->where(
                    'createdBy',
                    new ObjectId((string) $userChatUser->id)
                )
                ->first();

            if ($existing) {
                $this->ensureMembers($existing);

                return $existing->fresh();
            }

            $admin = $this->findLeastLoadedAdmin();

            $adminChatUser = $this->resolveAdminChatUser($admin);

            $conversation = Conversation::query()->create([
                'type' => 'DIRECT',
                'title' => 'Support',

                'createdBy' => new ObjectId(
                    (string) $userChatUser->id
                ),

                'context' => self::CONTEXT,
                'status' => self::STATUS_OPEN,

                'assignedTo' => new ObjectId(
                    (string) $adminChatUser->id
                ),

                'closedAt' => null,
            ]);

            $this->ensureMembers($conversation);

            return $conversation->fresh();
        });
    }

    private function ensureMembers(
        Conversation $conversation
    ): void {
        ConversationMember::query()->firstOrCreate(
            [
                'conversationId' => new ObjectId(
                    (string) $conversation->id
                ),

                'userId' => new ObjectId(
                    (string) $conversation->createdBy
                ),
            ],
            [
                'role' => 'MEMBER',
                'joinedAt' => now(),
                'leftAt' => null,
            ]
        );

        if ($conversation->assignedTo) {
            ConversationMember::query()->firstOrCreate(
                [
                    'conversationId' => new ObjectId(
                        (string) $conversation->id
                    ),

                    'userId' => new ObjectId(
                        (string) $conversation->assignedTo
                    ),
                ],
                [
                    'role' => 'ADMIN',
                    'joinedAt' => now(),
                    'leftAt' => null,
                ]
            );
        }
    }

    private function resolveUserChatUser(User $user): ChatUser
    {
        return ChatUser::query()->firstOrCreate(
            [
                'externalType' => 'USER',
                'externalId' => (string) $user->id,
            ],
            [
                'nickname' => 'USER=' . $user->id,
            ]
        );
    }

    private function resolveAdminChatUser(Admin $admin): ChatUser
    {
        return ChatUser::query()->firstOrCreate(
            [
                'externalType' => 'ADMIN',
                'externalId' => (string) $admin->id,
            ],
            [
                'nickname' => 'ADMIN=' . $admin->id,
            ]
        );
    }

    private function findLeastLoadedAdmin(): Admin
    {
        $admins = Admin::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', [
                    'support',
                    'super-admin',
                    'super_admin',
                ]);
            })
            ->get();

        if ($admins->isEmpty()) {
            abort(
                503,
                'No support agents are currently available.'
            );
        }

        $adminChatUsers = ChatUser::query()
            ->where('externalType', 'ADMIN')
            ->whereIn(
                'externalId',
                $admins
                    ->pluck('id')
                    ->map(fn ($id) => (string) $id)
                    ->all()
            )
            ->get();

        if ($adminChatUsers->isEmpty()) {
            return $admins->first();
        }

        $adminChatUserIds = $adminChatUsers
            ->map(
                fn (ChatUser $user) => new ObjectId(
                    (string) $user->id
                )
            )
            ->values()
            ->all();

        $loads = Conversation::query()
            ->where('context', self::CONTEXT)
            ->where('status', self::STATUS_OPEN)
            ->whereIn('assignedTo', $adminChatUserIds)
            ->get()
            ->groupBy(
                fn (Conversation $conversation) =>
                (string) $conversation->assignedTo
            )
            ->map->count();

        return $admins
            ->sortBy(function (Admin $admin) use (
                $adminChatUsers,
                $loads
            ) {
                $chatUser = $adminChatUsers->first(
                    fn (ChatUser $chatUser) =>
                        (string) $chatUser->externalId ===
                        (string) $admin->id
                );

                return $chatUser
                    ? ($loads[(string) $chatUser->id] ?? 0)
                    : 0;
            })
            ->first();
    }
}
