<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class OnboardingFlagSeeder extends Seeder
{
    /**
     * Set the onboarding flag for first-time superadmin creation.
     */
    public function run(): void
    {
        if (! class_exists(\Redis::class) && ! class_exists(\Predis\Client::class)) {
            Cache::forever('chatwoot_installation_onboarding', true);
            return;
        }

        try {
            app('redis')->set('chatwoot_installation_onboarding', true);
        } catch (\Throwable) {
            Cache::forever('chatwoot_installation_onboarding', true);
        }
    }
}
