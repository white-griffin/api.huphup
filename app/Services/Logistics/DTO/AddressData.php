<?php

namespace App\Services\Logistics\DTO;

readonly class AddressData
{
    public function __construct(
        public string $address,
        public float $latitude,
        public float $longitude,
    ) {}
}
