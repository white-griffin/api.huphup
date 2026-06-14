<?php

use App\Http\Controllers\User\Api\V1\AppointmentController;
use App\Http\Controllers\User\Api\V1\BreedsController;
use App\Http\Controllers\User\Api\V1\Chat\ConversationController;
use App\Http\Controllers\User\Api\V1\Chat\MessageController;
use App\Http\Controllers\User\Api\V1\PetController;
use App\Http\Controllers\User\Api\V1\ProfileController;
use App\Http\Controllers\User\Api\V1\SpeciesController;
use Illuminate\Support\Facades\Route;

Route::controller(ProfileController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('profile', 'getProfile');
    Route::post('profile', 'updateProfile');
    Route::post('address', 'addAddress');
    Route::post('address/{address}', 'updateAddress');
    Route::delete('address/{address}', 'deleteAddress');
});

Route::controller(SpeciesController::class)->prefix('species')->group(function () {
    Route::get('/', 'getSpecies');
});

Route::controller(BreedsController::class)->prefix('breeds')->group(function () {
    Route::get('/', 'getBreeds');
});

Route::controller(PetController::class)->prefix('pets')->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/', 'getPets');
        Route::post('/', 'storePet');
        Route::get('/{pet}', 'getPet');
        Route::post('/{pet}', 'updatePet');
    });


Route::controller(AppointmentController::class)->prefix('appointments')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/{businessId}/available-slots', 'availableSlots');
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/cancel/{appointment}', 'cancel');
    });

Route::prefix('chat')->middleware('auth:sanctum')->group(function () {
    Route::controller(ConversationController::class)->group(function () {
       Route::prefix('conversations')->group(function () {
           Route::get('/', 'index');
           Route::post('/', 'store');
           Route::get('/{conversation}', 'show');
           Route::delete('/{conversation}', 'leave');
       });

       Route::prefix('groups')->group(function () {
           Route::get('/', 'groups');
           Route::post('/{conversation}/join', 'join');
       });
    });

    Route::controller(MessageController::class)->group(function () {
        Route::get('/{conversation}/messages', 'index');
        Route::post('/{conversation}/messages', 'store');
        Route::post('/{conversation}/read', 'markAsRead');
    });
});
