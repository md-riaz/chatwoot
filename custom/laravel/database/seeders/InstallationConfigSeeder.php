<?php

namespace Database\Seeders;

use App\Services\ConfigLoaderService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class InstallationConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Loading installation configuration from YAML files...');

        $configLoader = new ConfigLoaderService([
            'reconcile_only_new' => false, // Allow updates during seeding
            'load_features' => true,
        ]);

        $results = $configLoader->process();

        $this->command->info('Configuration loading completed:');
        $this->command->line("  Configs loaded: {$results['configs_loaded']}");
        $this->command->line("  Configs updated: {$results['configs_updated']}");
        $this->command->line("  Features loaded: {$results['features_loaded']}");

        if (!empty($results['errors'])) {
            $this->command->warn('Errors encountered:');
            foreach ($results['errors'] as $error) {
                $this->command->error('  - ' . $error);
                Log::error('InstallationConfigSeeder error', ['error' => $error]);
            }
        }

        // Display enabled default features
        $enabledFeatures = $configLoader->getEnabledDefaultFeatures();
        $this->command->info('Default features enabled for new accounts:');
        foreach ($enabledFeatures as $feature) {
            $this->command->line("  - {$feature['display_name']} ({$feature['name']})");
        }

        $this->command->info('Installation configuration seeded successfully!');
    }
}