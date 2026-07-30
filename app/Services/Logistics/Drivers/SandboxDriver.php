<?php

namespace App\Services\Logistics\Drivers;

use App\Contracts\ShippingProvider;
use App\Enums\ShipmentStatuses;
use App\Models\Shipment;
use App\Services\Logistics\DTO\CreateShipmentData;
use App\Services\Logistics\DTO\CreateShipmentResult;
use App\Services\Logistics\DTO\ShipmentUpdateData;
use Illuminate\Support\Str;

class SandboxDriver implements ShippingProvider
{

    private const STATUS_FLOW = [
        ShipmentStatuses::PENDING,
        ShipmentStatuses::ACCEPTED,
        ShipmentStatuses::PICKED,
        ShipmentStatuses::DELIVERING,
        ShipmentStatuses::DELIVERED,
    ];

    public function createShipment(CreateShipmentData $data): CreateShipmentResult
    {
        return new CreateShipmentResult(
            providerOrderId: (string) Str::uuid(),
            trackingCode: sprintf(
                'SBX-%s-%06d',
                now()->format('Ymd'),
                random_int(1, 999999)
            ),
            status: ShipmentStatuses::PENDING,
            providerData: [
                'driver' => null,
                'price' => $data->price,
                'created_at' => now()->toIso8601String(),
            ],
        );
    }

    public function cancelShipment(Shipment $shipment): bool
    {
        return true;
    }

    public function track(Shipment $shipment): ShipmentUpdateData
    {
        $currentIndex = array_search(
            $shipment->status,
            self::STATUS_FLOW,
            true
        );

        $nextStatus = self::STATUS_FLOW[
        min($currentIndex + 1, count(self::STATUS_FLOW) - 1)
        ];

        return new ShipmentUpdateData(
            providerOrderId: $shipment->provider_order_id,
            status: $nextStatus,
            providerData: $shipment->provider_data ?? [],
        );
    }
    public function handleWebhook(array $payload): ShipmentUpdateData
    {
        return new ShipmentUpdateData(
            providerOrderId: $payload['provider_order_id'],
            status: $this->mapStatus($payload['status']),
            providerData: $payload,
        );
    }

    private function mapStatus(string $status): ShipmentStatuses
    {
        return match ($status) {
            'accepted' => ShipmentStatuses::ACCEPTED,
            'picked_up' => ShipmentStatuses::PICKED,
            'delivering' => ShipmentStatuses::DELIVERING,
            'delivered' => ShipmentStatuses::DELIVERED,
            'cancelled' => ShipmentStatuses::CANCELLED,
            default => ShipmentStatuses::PENDING,
        };
    }

}
