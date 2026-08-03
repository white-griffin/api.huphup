<?php

namespace App\Jobs;

use App\Enums\ShipmentStatuses;
use App\Models\Shipment;
use App\Services\Logistics\LogisticsManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TrackShipmentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Shipment $shipment,
    ) {}

    public function handle(LogisticsManager $manager): void
    {
        $this->shipment->refresh();

        if (in_array($this->shipment->status, [
            ShipmentStatuses::DELIVERED,
            ShipmentStatuses::CANCELLED,
        ], true)) {
            return;
        }

        $driver = $manager->driver($this->shipment->provider);

        $result = $driver->track($this->shipment);

        $this->shipment->updateStatus(
            $result->status,
            $result->providerData,
        );

        if (! in_array($result->status, [
            ShipmentStatuses::DELIVERED,
            ShipmentStatuses::CANCELLED,
        ], true)) {
            TrackShipmentJob::dispatch($this->shipment)
                ->delay(now()->addMinute());
        }
    }
}
