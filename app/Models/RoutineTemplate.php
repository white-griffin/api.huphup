<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class RoutineTemplate extends Model
{
    protected $guarded =['id'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image
            ? Storage::disk('public')->url($this->image)
            : null;
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    // --------------------------------------------------
    // اکشن‌های پیشنهادی این روتین
    // --------------------------------------------------
    public function actions()
    {
        return $this->hasMany(RoutineAction::class);
    }

    // --------------------------------------------------
    // روتین‌های فعال روی پت‌ها
    // --------------------------------------------------
    public function petRoutines()
    {
        return $this->hasMany(PetRoutine::class);
    }
}
