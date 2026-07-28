<?php

namespace App\Services\Logistics\DTO;

use App\Enums\ShipmentStatuses;

readonly class CreateShipmentResult
{
    public function __construct(
        public string $providerOrderId,
        public ?string $trackingCode,
        public ShipmentStatuses $status,
        public array $providerData = [],
    ) {}
}
