<?php

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use BelongsToBusiness;

    protected $guarded = ['id'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
        'date'       => 'date',
    ];


    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
