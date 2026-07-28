<?php

namespace App\Services\Logistics\DTO;

use App\Models\Order;

readonly class CreateShipmentData
{
    public function __construct(
        public string $reference,
        public AddressData $origin,
        public AddressData $destination,
        public CustomerData $customer,
        public int $price,
    ) {}

}
