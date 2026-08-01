<?php

namespace App\Http\Controllers\Simulator;

use App\Enums\ShipmentProvider;
use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\View\View;

class ShippingSimulatorController extends Controller
{
    public function index(): View
    {
        $shipments = Shipment::query()
            ->where('provider', ShipmentProvider::SANDBOX)
            ->latest()
            ->get();

        return view('simulator.shipping.index', compact('shipments'));
    }
}
