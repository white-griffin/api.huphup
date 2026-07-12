<?php

use Illuminate\Http\Request;
use App\Services\Payment\Gateways\TestGateway;
use Illuminate\Support\Facades\Route;

Route::match(['GET', 'POST'], '/payments/test', function (Request $request) {
    return app(TestGateway::class)->simulate($request);
})->name('payments.test');
