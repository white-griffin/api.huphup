<?php

namespace App\Services\Logistics;

use App\Enums\PaymentStatuses;
use App\Enums\ShipmentProvider;
use App\Enums\ShipmentStatuses;
use App\Models\Shipment;
use App\Services\Order\OrderDeliveryService;
use App\Services\Payment\SettlementService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ShippingWebhookService
{
    public function __construct(
        private readonly LogisticsManager $manager,
    ) {}

    public function handle(
        ShipmentProvider $provider,
        array $payload,
    ): void {


        DB::transaction(function () use ($provider, $payload) {

            $driver = $this->manager->driver($provider);

            $result = $driver->handleWebhook($payload);

            $shipment = Shipment::query()
                ->where('provider', $provider)
                ->where('provider_order_id', $result->providerOrderId)
                ->lockForUpdate()
                ->first();

            if (! $shipment) {
                throw new ModelNotFoundException(
                    "Shipment not found. Provider Order ID: {$result->providerOrderId}"
                );
            }

            if (in_array($shipment->status, [
                ShipmentStatuses::DELIVERED->value,
                ShipmentStatuses::CANCELLED->value,
            ])) {
                return;
            }

            $shipment->updateStatus(
                $result->status,
                $result->providerData,
            );

            if ($result->status == ShipmentStatuses::DELIVERED) {
                app(OrderDeliveryService::class)
                    ->delivered($shipment);
            }
        });
    }
}
