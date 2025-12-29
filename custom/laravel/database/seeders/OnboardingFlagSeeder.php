<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OnboardingFlagSeeder extends Seeder
{
    /**
     * Set the Redis onboarding flag for first-time superadmin creation.
     */
    public function run(): void
    {
        // Set the onboarding flag in Redis (Rails-style)
        app('redis')->set('chatwoot_installation_onboarding', true);
    }
}
