<?php

use App\Http\Controllers\Provider\Api\V1\BrandController;
use App\Http\Controllers\Provider\Api\V1\CategoryController;
use App\Http\Controllers\Provider\Api\V1\ProductController;

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


Route::controller(BrandController::class)->prefix('brands')->group(function () {
    Route::get('/', 'index');
});
