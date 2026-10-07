<?php

use App\Http\Controllers\User\Api\V1\User\AuthController;

Route::controller(AuthController::class)->group(function (){
    Route::post('/login','login');
    Route::post('/check_code','checkCode');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/logout','logOut');
        Route::get('/chat_token','getChatJwtToken');
        Route::post('/device-tokens', 'setFcmToken');
        Route::delete('/device-tokens/{token}', 'destroyFcmToken');
    });
});

