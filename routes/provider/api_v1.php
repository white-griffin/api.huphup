<?php

use App\Http\Controllers\Provider\Api\V1\Appointment\BusinessOffDayController;
use App\Http\Controllers\Provider\Api\V1\Appointment\ScheduleBreakController;
use App\Http\Controllers\Provider\Api\V1\Appointment\ScheduleController;
use App\Http\Controllers\Provider\Api\V1\BusinessController;
use App\Http\Controllers\Provider\Api\V1\Order\OrderController;
use App\Http\Controllers\Provider\Api\V1\Product\BrandController;
use App\Http\Controllers\Provider\Api\V1\Product\CategoryController;
use App\Http\Controllers\Provider\Api\V1\Product\ProductAttributeController;
use App\Http\Controllers\Provider\Api\V1\Product\ProductController;
use App\Http\Controllers\Provider\Api\V1\ProfileController;
use App\Http\Controllers\Provider\Api\V1\ReviewController;

Route::controller(ProfileController::class)->prefix('profile')
    ->withoutMiddleware('resolve.business')
    ->group(function () {
    Route::get('/', 'getProfile');
    Route::post('/', 'updateProfile');
});

Route::controller(BusinessController::class)->prefix('businesses')
    ->group(function () {
        Route::get('/', 'getBusinesses')->withoutMiddleware('resolve.business');
        Route::get('/{business}', 'showBusiness');
        Route::post('/{business}', 'update');
    });

Route::controller(CategoryController::class)->prefix('categories')->group(function () {
    Route::get('/', 'index');
    Route::get('/{category}', 'show');
});

Route::controller(ProductController::class)->prefix('products')->group(function () {
    Route::get('/', 'index');
    Route::get('/{product}', 'show');
    Route::post('/', 'store');
    Route::post('/{product}', 'update');

    Route::prefix('{product}/images')->group(function () {
        Route::post('/', 'uploadImages');
        Route::delete('/{image}', 'deleteImage');
        Route::post('/set-primary/{image}', 'setPrimary');
        Route::post('/re-order', 'reorder');
    });
});

Route::controller(ProductAttributeController::class)->prefix('attributes')->group(function () {
    Route::get('/', 'index');
});

Route::controller(BrandController::class)->prefix('brands')->group(function () {
    Route::get('/', 'index');
});


Route::controller(ScheduleController::class)->prefix('schedules')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'upsert');

    Route::controller(ScheduleBreakController::class)->prefix('{schedule}/breaks')->group(function () {
        Route::post('/', 'store');
        Route::post('/{break}', 'destroy');
    });
});

Route::controller(BusinessOffDayController::class)->prefix('off-days')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::delete('/{offDay}', 'destroy');
});

Route::controller(ReviewController::class)
    ->prefix('reviews')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/{review}/reply', 'reply');
    });

Route::controller(OrderController::class)
    ->prefix('orders')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/{orderVendorId}', 'show');
        Route::post('/{orderVendorId}/accept', 'accept');
        Route::post('/{orderVendorId}/reject', 'reject');
        Route::post('/items/{orderItemId}/cancel','cancelItem');
    });
