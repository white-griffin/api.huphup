<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\ShipmentProvider;
use App\Http\Controllers\Controller;
use App\Services\Logistics\ShippingWebhookService;
use Illuminate\Http\Request;

class ShippingWebhookController extends Controller
{
    public function __invoke(
        ShipmentProvider $provider,
        Request $request,
        ShippingWebhookService $service,
    ) {
        $service->handle(
            $provider,
            $request->all(),
        );

        return response()->json();
    }
}
