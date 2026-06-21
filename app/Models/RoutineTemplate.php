<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineTemplate extends Model
{
    protected $guarded =['id'];

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function actions()
    {
        return $this->hasMany(RoutineAction::class);
    }

    public function petRoutines()
    {
        return $this->hasMany(PetRoutine::class);
    }
}
