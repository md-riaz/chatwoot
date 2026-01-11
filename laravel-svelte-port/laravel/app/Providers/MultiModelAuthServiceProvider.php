<?php

namespace App\Providers;

use App\Auth\MultiModelSanctumGuard;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;

/**
 * Multi-Model Authentication Service Provider
 * 
 * Registers the custom MultiModelSanctumGuard to enable authentication
 * of User, AgentBot, and PlatformApp models using Sanctum tokens.
 */
class MultiModelAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Extend the Auth facade to use our custom multi-model guard
        Auth::extend('sanctum', function ($app, $name, array $config) {
            return new MultiModelSanctumGuard(
                $app[\Illuminate\Contracts\Auth\Factory::class],
                config('sanctum.expiration'),
                $config['provider'] ?? null,
                $app->request,
                config('sanctum.hash', true)
            );
        });
    }
}
