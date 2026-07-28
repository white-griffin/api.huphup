<?php

namespace App\Models;

use App\Enums\ShipmentProvider;
use App\Enums\ShipmentStatuses;
use Illuminate\Database\Eloquent\Model;

class ShipmentEvent extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => ShipmentStatuses::class,
        ];
    }
}
