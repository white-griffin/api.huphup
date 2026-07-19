<?php

namespace App\Models\Traits;

use App\Enums\ReactionType;
use App\Models\Reaction;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasReactions
{
    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    public function reactionsOfType(ReactionType $type): MorphMany
    {
        return $this->reactions()->where('type', $type);
    }
}
