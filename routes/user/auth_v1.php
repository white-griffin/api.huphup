<?php

use App\Http\Controllers\User\Api\V1\AuthController;

Route::controller(AuthController::class)->group(function (){
    Route::post('/login','login');
    Route::post('/check_code','checkCode');
    Route::get('/logout','logOut')->middleware('auth:sanctum');
});

