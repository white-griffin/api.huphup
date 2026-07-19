<?php

namespace App\Services\Reaction;

use App\Enums\ReactionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ReactionService
{
    public function toggle(
        User $user,
        Model $model,
        ReactionType $type
    ): bool {
        $reaction = $model->reactions()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->first();

        if ($reaction) {
            $reaction->delete();

            return false;
        }

        $model->reactions()->create([
            'user_id' => $user->id,
            'type' => $type,
        ]);

        return true;
    }
}
