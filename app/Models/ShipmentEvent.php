<?php

namespace App\Models;

use App\Enums\ShipmentProvider;
use App\Enums\ShipmentStatuses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentEvent extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => ShipmentStatuses::class,
            'payload' => 'array',
        ];
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
