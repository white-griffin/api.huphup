<?php

use App\Http\Controllers\User\Api\V1\AppointmentController;
use App\Http\Controllers\User\Api\V1\BusinessController;
use App\Http\Controllers\User\Api\V1\Chat\ConversationController;
use App\Http\Controllers\User\Api\V1\Chat\MessageController;
use App\Http\Controllers\User\Api\V1\LocationController;
use App\Http\Controllers\User\Api\V1\NotificationController;
use App\Http\Controllers\User\Api\V1\Order\OrderController;
use App\Http\Controllers\User\Api\V1\Order\PaymentController;
use App\Http\Controllers\User\Api\V1\PetRoutine\PetRoutineController;
use App\Http\Controllers\User\Api\V1\PetRoutine\RoutineTemplateController;
use App\Http\Controllers\User\Api\V1\Pets\BreedsController;
use App\Http\Controllers\User\Api\V1\Pets\PetController;
use App\Http\Controllers\User\Api\V1\Pets\SpeciesController;
use App\Http\Controllers\User\Api\V1\Products\CategoryController;
use App\Http\Controllers\User\Api\V1\Products\ProductController;
use App\Http\Controllers\User\Api\V1\User\ProfileController;
use Illuminate\Support\Facades\Route;

Route::controller(LocationController::class)->prefix('location')->group(function () {
    Route::get('/provinces', 'provinces');
    Route::get('/cities', 'cities');
});

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
        Route::delete('/{pet}', 'deletePet');
    });


Route::controller(AppointmentController::class)->prefix('appointments')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/{businessId}/available-slots', 'availableSlots');
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/cancel/{appointment}', 'cancel');
    });

Route::controller(BusinessController::class)->prefix('businesses')->group(function () {
    Route::get('/', 'index');
    Route::get('/{business}', 'show');
});

Route::controller(CategoryController::class)->prefix('categories')->group(function () {
    Route::get('/', 'index');
    Route::get('/{category}', 'show');
});

Route::controller(ProductController::class)->prefix('products')->group(function () {
    Route::get('/', 'search');
    Route::get('/{product}', 'show');
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

Route::controller(PetRoutineController::class)
    ->middleware('auth:sanctum')
    ->prefix('pet-routines')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{pet_routine}', 'show');
        Route::post('/{pet_routine}', 'update');
        Route::delete('/{pet_routine}', 'destroy');
    });

Route::controller(RoutineTemplateController::class)
    ->middleware('auth:sanctum')
    ->prefix('routine-templates')
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/{routine_template}', 'show');
    });

Route::controller(OrderController::class)->prefix('orders')->middleware('auth:sanctum')
    ->group(function () {
        Route::post('/', 'store');
        Route::get('/', 'index');
        Route::get('/{order}', 'show');
    });

Route::controller(PaymentController::class)->prefix('payments')
    ->group(function () {
        Route::post('/pay', 'pay')->middleware('auth:sanctum');
        Route::post('/callback/{gateway}', 'callback')->name('payments.callback');
    });

Route::controller(NotificationController::class)
    ->prefix('notifications')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/', 'index');
        Route::post('/{id}/read', 'markAsRead');
    });

