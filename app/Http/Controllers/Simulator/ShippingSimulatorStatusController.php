<?php

namespace App\Http\Controllers\Simulator;

use App\Enums\ShipmentProvider;
use App\Enums\ShipmentStatuses;
use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;

class ShippingSimulatorStatusController extends Controller
{
    public function __invoke(
        Shipment $shipment,
        ShipmentStatuses $status,
    ): RedirectResponse {
        abort_unless(
            $shipment->provider === ShipmentProvider::SANDBOX,
            404
        );

        Http::post(
            route('shipping.webhook', ShipmentProvider::SANDBOX),
            [
                'provider_order_id' => $shipment->provider_order_id,
                'status' => $status->value,
            ]
        );

        return back();
    }
}
