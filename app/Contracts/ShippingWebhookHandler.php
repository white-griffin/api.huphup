<?php

namespace App\Contracts;

use App\Services\Logistics\DTO\ShipmentUpdateData;

interface ShippingWebhookHandler
{
    public function track(string $providerOrderId): ShipmentUpdateData;

    public function handleWebhook(array $payload): ShipmentUpdateData;
}
