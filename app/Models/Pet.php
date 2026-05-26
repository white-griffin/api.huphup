<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Pet extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'medical_records' => 'array',
        'settings' => 'array',
    ];
    protected $appends = ['avatar_url'];

    public function getAvatarUrlAttribute()
    {
        return $this->avatar
            ? Storage::disk('public')->url($this->avatar)
            : null;
    }
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breed::class);
    }
}
