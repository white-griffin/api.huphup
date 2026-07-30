<?php

namespace App\Services\Logistics\DTO;

use App\Enums\ShipmentStatuses;

readonly class ShipmentUpdateData
{
    public function __construct(
        public string $providerOrderId,
        public ShipmentStatuses $status,
        public array $providerData = [],
    ) {
    }
}
