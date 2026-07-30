<?php

namespace App\Services\Logistics;

use App\Enums\ShipmentProvider;
use App\Enums\ShipmentStatuses;
use App\Jobs\TrackShipmentJob;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Logistics\DTO\AddressData;
use App\Services\Logistics\DTO\CreateShipmentData;
use App\Services\Logistics\DTO\CustomerData;

class ShippingService
{

    public function __construct(
        private readonly LogisticsManager $manager,
    ) {}


    public function create(Order $order): Shipment
    {
        $provider = ShipmentProvider::SANDBOX;

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'provider' => $provider,
            'status' => ShipmentStatuses::PENDING,
        ]);

        $driver = $this->manager->driver($provider);

        $data = new CreateShipmentData(
            reference: (string) $order->id,
            origin: new AddressData(
                address: '',
                latitude: 0,
                longitude: 0,
            ),
            destination: new AddressData(
                address: '',
                latitude: 0,
                longitude: 0,
            ),
            customer: new CustomerData(
                name: '',
                phone: '',
            ),
            price: 0,
        );

        $result = $driver->createShipment($data);

        $shipment->update([
            'provider_order_id' => $result->providerOrderId,
            'tracking_code' => $result->trackingCode,
            'status' => $result->status,
            'provider_data' => $result->providerData,
        ]);

        $shipment->events()->create([
            'status' => $result->status,
            'payload' => $result->providerData,
        ]);

        TrackShipmentJob::dispatch($shipment)
            ->delay(now()->addMinute());

        return $shipment->fresh();
    }

    public function cancel(Shipment $shipment): void
    {
        if (in_array($shipment->status, [
            ShipmentStatuses::DELIVERED,
            ShipmentStatuses::CANCELLED,
        ])) {
            return;
        }

        $driver = $this->manager->driver($shipment->provider);

        $driver->cancelShipment($shipment);

        $shipment->updateStatus(
            ShipmentStatuses::CANCELLED
        );
    }
}
