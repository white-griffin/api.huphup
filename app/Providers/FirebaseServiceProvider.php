<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;
use RuntimeException;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Messaging::class, function () {
            $credentials = config('services.firebase.credentials');

            if (! $credentials || ! is_file($credentials) || ! is_readable($credentials)) {
                throw new RuntimeException(
                    'Firebase credentials file is missing or unreadable.'
                );
            }

            return (new Factory)
                ->withServiceAccount($credentials)
                ->createMessaging();
        });

        $this->app->singleton(Messaging::class, function () {
            return (new Factory)
                ->withServiceAccount(
                    config('services.firebase.credentials')
                )
                ->createMessaging();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
