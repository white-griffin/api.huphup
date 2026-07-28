<?php

namespace App\Services\Logistics\DTO;

use App\Enums\ShipmentStatuses;

readonly class ShipmentStatusResult
{
    public function __construct(
        public ShipmentStatuses $status,
        public array $providerData = [],
    ) {}
}
