<?php

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessOffDay extends Model
{
    use BelongsToBusiness;
    protected $guarded = ['id'];

    protected $casts = ['date' => 'date'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
