<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\ReviewMessage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewMessagePolicy
{
    public function update(
        User $user,
        ReviewMessage $message,
    ): bool {
        return $message->author_type === User::class
            && $message->author_id === $user->id;
    }

    public function delete(
        User $user,
        ReviewMessage $message,
    ): bool {
        return $message->author_type === User::class
            && $message->author_id === $user->id;
    }
}
