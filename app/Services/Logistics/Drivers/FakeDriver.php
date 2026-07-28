<?php

namespace App\Services\Logistics\Drivers;

use App\Contracts\ShippingProvider;
use App\Enums\ShipmentStatuses;
use App\Models\Shipment;
use App\Services\Logistics\DTO\CreateShipmentData;
use App\Services\Logistics\DTO\CreateShipmentResult;
use App\Services\Logistics\DTO\ShipmentStatusResult;

class FakeDriver implements ShippingProvider
{

    public function createShipment(CreateShipmentData $data): CreateShipmentResult
    {
        return new CreateShipmentResult(
            providerOrderId: fake()->uuid(),
            trackingCode: fake()->numerify('TRK######'),
            status: ShipmentStatuses::PENDING,
        );
    }

    public function cancelShipment(Shipment $shipment): bool
    {
        return true;
    }

    public function track(Shipment $shipment): ShipmentStatusResult
    {
        return new ShipmentStatusResult(
            ShipmentStatuses::DELIVERING
        );
    }
}
