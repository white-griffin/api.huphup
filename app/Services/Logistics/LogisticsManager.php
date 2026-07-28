<?php

namespace App\Services\Logistics;

use App\Contracts\ShippingProvider;
use App\Enums\ShipmentProvider;
use App\Services\Logistics\Drivers\FakeDriver;

class LogisticsManager
{
    protected array $drivers = [
        ShipmentProvider::FAKE->value => FakeDriver::class,
//        ShipmentProvider::ALOPEYK->value => AlopeykDriver::class,
    ];

    public function driver(ShipmentProvider $provider): ShippingProvider
    {
        return app($this->drivers[$provider->value]);
    }
}
