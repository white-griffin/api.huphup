<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Provider extends Model
{
    protected $guarded = ['id'];

    public function documents()
    {
        return $this->hasMany(ProviderDocument::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function getNameAttribute(): string
    {
        $full = trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
        return $full !== ''
            ? $full
            : ($this->mobile ?? $this->email ?? 'تامین کننده');
    }
}
