<?php

namespace App\Models;

use App\Enums\ShipmentProvider;
use App\Enums\ShipmentStatuses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'provider' => ShipmentProvider::class,
            'status' => ShipmentStatuses::class,
        ];
    }

    public function orderVendor(): BelongsTo
    {
        return $this->belongsTo(OrderVendor::class);
    }

    public function events()
    {
        return $this->hasMany(ShipmentEvent::class);
    }

    public function updateStatus(
        ShipmentStatuses $status,
        array $providerData = [],
    ): void
    {
        $this->update([
            'status' => $status,
            'provider_data' => $providerData,
        ]);

        $this->events()->create([
            'status' => $status,
            'payload' => $providerData,
        ]);
    }
}
