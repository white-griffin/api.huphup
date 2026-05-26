<?php

use App\Http\Controllers\User\Api\V1\BreedsController;
use App\Http\Controllers\User\Api\V1\PetController;
use App\Http\Controllers\User\Api\V1\ProfileController;
use App\Http\Controllers\User\Api\V1\SpeciesController;
use Illuminate\Support\Facades\Route;

Route::controller(ProfileController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('profile', 'getProfile');
    Route::put('profile', 'updateProfile');
    Route::post('address', 'addAddress');
    Route::put('address/{address}', 'updateAddress');
    Route::delete('address/{address}', 'deleteAddress');
});

Route::controller(SpeciesController::class)->prefix('species')->group(function () {
    Route::get('/', 'getSpecies');
});

Route::controller(BreedsController::class)->prefix('breeds')->group(function () {
    Route::get('/', 'getBreeds');
});

Route::controller(PetController::class)->prefix('pets')
    ->middleware('auth:sanctum')->group(function () {
        Route::get('/', 'getPets');
        Route::post('/', 'storePet');
        Route::get('/{pet}', 'getPet');
        Route::put('/{pet}', 'updatePet');
    });


