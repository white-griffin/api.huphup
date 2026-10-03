<?php

use App\Http\Controllers\Admin\ChatService\ChatController;

Route::middleware('auth:admin')->group(function () {
    Route::get(
        '/chat/token',
        ChatController::class
    )->name('admin.chat.token');
});
