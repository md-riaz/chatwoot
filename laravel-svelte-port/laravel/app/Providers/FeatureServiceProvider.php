<?php

namespace App\Providers;

use App\Services\Seeding\AccountSeederService;
use Illuminate\Support\ServiceProvider;

class FeatureServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register seeding service
        $this->app->bind(AccountSeederService::class, function ($app) {
            return new AccountSeederService(
                $app->make('request')->route('account') ?? $app->make(\App\Models\Account::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Nothing needed here - keep it simple
    }
}