<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Breed extends Model
{
    protected $guarded = ['id'];

    protected $casts =[
        'characteristics' => 'array',
    ];
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }
}
