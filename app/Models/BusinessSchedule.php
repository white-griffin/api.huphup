<?php

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSchedule extends Model
{
    use BelongsToBusiness;
    protected $guarded = ['id'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function breaks()
    {
        return $this->hasMany(ScheduleBreak::class,'schedule_id');
    }
}
