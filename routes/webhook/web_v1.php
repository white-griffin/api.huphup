<?php

use App\Http\Controllers\Webhook\ShippingWebhookController;

Route::prefix('shipping')
    ->controller(ShippingWebhookController::class)
    ->group(function () {
    Route::post(
        '/{provider}',''
    );
});
