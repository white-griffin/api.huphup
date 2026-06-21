<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetRoutine extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'last_done_at' => 'datetime',
        'next_due_at' => 'datetime',
        'notification_enabled' => 'boolean',
        'settings' => 'array',
    ];

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(RoutineTemplate::class,'routine_template_id');
    }
}
