<?php

namespace App\Contracts;

use App\Models\Shipment;
use App\Services\Logistics\DTO\CreateShipmentData;
use App\Services\Logistics\DTO\CreateShipmentResult;
use App\Services\Logistics\DTO\ShipmentUpdateData;

interface ShippingProvider
{
    public function createShipment(CreateShipmentData $data): CreateShipmentResult;

    public function cancelShipment(Shipment $shipment): bool;

    public function track(Shipment $shipment): ShipmentUpdateData;

}
