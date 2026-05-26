<?php

use App\Http\Controllers\User\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

Route::controller(ProfileController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('profile', 'getProfile');
    Route::put('profile', 'updateProfile');
    Route::post('address', 'addAddress');
    Route::put('address/{address}', 'updateAddress');
    Route::delete('address/{address}', 'deleteAddress');
});
