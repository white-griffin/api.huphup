<?php

use App\Http\Controllers\Simulator\ShippingSimulatorController;
use App\Http\Controllers\Simulator\ShippingSimulatorStatusController;
use Illuminate\Http\Request;
use App\Services\Payment\Gateways\TestGateway;
use Illuminate\Support\Facades\Route;

Route::match(['GET', 'POST'], '/payments/test', function (Request $request) {
    return app(TestGateway::class)->simulate($request);
})->name('payments.test');

Route::prefix('simulator/shipping')
    ->name('simulator.shipping.')
    ->group(function () {

        Route::get(
            '/',
            [ShippingSimulatorController::class,'index']
        )->name('index');

        Route::post(
            '/{shipment}/status',
            [ShippingSimulatorStatusController::class,'updateStatus']
        )->name('status');

    });
