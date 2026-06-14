<?php

use App\Http\Middleware\ResolveBusiness;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
        then: function () {

            /* User Api Version 1 */
            Route::middleware('api')
                ->prefix('api/v1/user')
                ->group(base_path('routes/user/api_v1.php'));
            Route::middleware('api')
                ->prefix('api/v1/user/auth')
                ->group(base_path('routes/user/auth_v1.php'));

            /* Provider Api Version 1 */
            Route::middleware([
                'api',
                'auth:provider',
                'resolve.business'
            ])
                ->prefix('api/v1/provider')
                ->scopeBindings()
                ->group(base_path('routes/provider/api_v1.php'));

            Route::middleware('api')
                ->prefix('api/v1/provider/auth')
                ->group(base_path('routes/provider/auth_v1.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'resolve.business' => ResolveBusiness::class,
        ]);
    })->withBroadcasting(
        channels: __DIR__ . '/../routes/channels.php',
        attributes: ['middleware' => ['api', 'auth:sanctum']],
    )
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
