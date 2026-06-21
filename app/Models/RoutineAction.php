<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineAction extends Model
{
    protected $guarded = ['id'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(RoutineTemplate::class,'routine_template_id');
    }
}
