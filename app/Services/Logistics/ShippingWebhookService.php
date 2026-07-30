<?php

namespace App\Services\Logistics;

use App\Enums\ShipmentProvider;
use App\Models\Shipment;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShippingWebhookService
{
    public function __construct(
        private readonly LogisticsManager $manager,
    ) {
    }

    public function handle(
        ShipmentProvider $provider,
        array $payload,
    ): void {
        $driver = $this->manager->driver($provider);

        $result = $driver->handleWebhook($payload);

        $shipment = Shipment::query()
            ->where('provider', $provider)
            ->where('provider_order_id', $result->providerOrderId)
            ->first();

        if (! $shipment) {
            throw new ModelNotFoundException(
                "Shipment not found. Provider Order ID: {$result->providerOrderId}"
            );
        }

        $shipment->updateStatus(
            $result->status,
            $result->providerData,
        );
    }
}
