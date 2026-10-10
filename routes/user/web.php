<?php

use App\Http\Controllers\Simulator\ShippingSimulatorController;
use App\Http\Controllers\Simulator\ShippingSimulatorStatusController;
use App\Models\MongoDB\ChatUser;
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

Route::get('/test-chat-db', function () {
    return ChatUser::query()->limit(5)->get();
});

Route::get('/debug-admin-auth', function () {
    return response()->json([
        'admin_authenticated' => auth('admin')->check(),
        'admin_id' => auth('admin')->id(),
        'default_guard' => config('auth.defaults.guard'),
        'session_id' => session()->getId(),
        'session_admin_login' => session()->get('login_admin_' . sha1('admin')),
    ]);
});
