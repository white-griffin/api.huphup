<?php

use App\Http\Controllers\User\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

Route::controller(ProfileController::class)->group(function () {
    Route::get('profile', 'getProfile');
    Route::put('profile', 'updateProfile');
    Route::post('address', 'addAddress');
    Route::put('address', 'updateAddress');
    Route::delete('address', 'deleteAddress');
});
