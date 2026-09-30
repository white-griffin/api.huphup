<?php

namespace App\Services\UserDomain;

use App\Enums\UserNoticeTypes;
use App\Models\User;
use App\Models\UserNotice;

class UserNoticeService
{

    public function sendToUser(
        User $user,
        string $title,
        string $body,
        UserNoticeTypes $type = UserNoticeTypes::GENERAL,
        array $data = [],
    ): UserNotice {
        return UserNotice::create([
            'user_id' => $user->id,
            'type' => $type->value,
            'title' => $title,
            'body' => $body,
            'data' => $data ?: null,
        ]);
    }

    public function sendToUsers(
        iterable $users,
        string $title,
        string $body,
        UserNoticeTypes $type = UserNoticeTypes::GENERAL,
        array $data = [],
    ): void {
        foreach ($users as $user) {
            $this->sendToUser(
                user: $user,
                title: $title,
                body: $body,
                type: $type,
                data: $data,
            );
        }
    }

    public function sendToAll(
        string $title,
        string $body,
        UserNoticeTypes $type = UserNoticeTypes::GENERAL,
        array $data = [],
    ): UserNotice {
        return UserNotice::create([
            'user_id' => null,
            'type' => $type->value,
            'title' => $title,
            'body' => $body,
            'data' => $data ?: null,
        ]);
    }

}
