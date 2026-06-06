<?php

use App\Http\Controllers\Provider\Api\V1\CategoryController;

Route::controller(CategoryController::class)->prefix('categories')->group(function () {
    Route::get('/', 'index');
    Route::get('/{category}', 'show');
    Route::post('/', 'store');
    Route::post('/{category}', 'update');
});

