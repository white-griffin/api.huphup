<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Breed extends Model
{
    protected $guarded = ['id'];

    protected $casts =[
        'characteristics' => 'array',
    ];

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
}
