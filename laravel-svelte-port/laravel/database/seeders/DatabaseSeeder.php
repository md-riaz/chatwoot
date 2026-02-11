<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Seed roles & permissions, installation config, then set onboarding flag for first-time superadmin creation
        $this->call(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->call(InstallationConfigSeeder::class);
        $this->call(OnboardingFlagSeeder::class);
        $this->call(DemoReportsSeeder::class);
    }
}
