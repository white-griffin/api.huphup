<?php

namespace App\Services\Logistics\DTO;

readonly class CustomerData
{
    public function __construct(
        public string $name,
        public string $phone,
    ) {}
}
