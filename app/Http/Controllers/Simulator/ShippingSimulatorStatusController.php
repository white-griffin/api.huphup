<?php

namespace App\Http\Controllers\Simulator;

use App\Enums\ShipmentProvider;
use App\Enums\ShipmentStatuses;
use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\Logistics\ShippingWebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ShippingSimulatorStatusController extends Controller
{
    public function updateStatus(
        Request $request,
        Shipment $shipment,
        ShippingWebhookService $webhookService,
    ) {
        $data = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $status = ShipmentStatuses::tryFrom($data['status']);

        abort_if(! $status, 422, 'وضعیت نامعتبر است.');

        $payload = [
            'provider_order_id' => $shipment->provider_order_id,
            'status' => $status->value,
            'sandbox' => true,
        ];

        $webhookService->handle(
            provider: $shipment->provider,
            payload: $payload,
        );

        return response()->json([
            'message' => 'وضعیت با موفقیت تغییر کرد.',
            'shipment' => $shipment->fresh(),
        ]);
    }
}
