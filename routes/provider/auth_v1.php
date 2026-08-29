<?php

use App\Http\Controllers\Provider\Api\V1\AuthController;

Route::controller(AuthController::class)->group(function (){
    Route::post('/login','login');
    Route::post('/check_code','verify2fa');
    Route::get('/logout','logOut')->middleware('auth:provider');
    Route::get('/toggle2fa','toggle2fa')->middleware('auth:provider');
    Route::post('/toggle2fa','verifyToggle2fa')->middleware('auth:provider');
});
