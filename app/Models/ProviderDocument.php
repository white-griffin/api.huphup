<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProviderDocument extends Model
{
    protected $guarded =['id'];

    protected $appends = [
        'image_url'
    ];

    public function getImageUrlAttribute()
    {
        return $this->name
            ? Storage::disk('public')->url($this->name)
            : null;
    }
}
