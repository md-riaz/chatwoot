<?php

namespace Database\Seeders;

use App\Enums\Feature;
use App\Models\InstallationConfig;
use Illuminate\Database\Seeder;

class InstallationConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Loading installation configuration...');

        // Load feature defaults
        $enabledFeatures = Feature::getEnabledByDefault();
        
        InstallationConfig::updateOrCreate(
            ['name' => 'ACCOUNT_LEVEL_FEATURE_DEFAULTS'],
            [
                'display_title' => 'Account Level Feature Defaults',
                'description' => 'Default features enabled for new accounts',
                'type' => 'array',
                'locked' => true,
                'serialized_value' => $enabledFeatures,
            ]
        );

        $this->command->info('Configuration loading completed:');
        $this->command->line("  Features loaded: " . count($enabledFeatures));

        // Display enabled default features
        $this->command->info('Default features enabled for new accounts:');
        foreach ($enabledFeatures as $feature) {
            $this->command->line("  - {$feature['display_name']} ({$feature['name']})");
        }

        $this->command->info('Installation configuration seeded successfully!');
    }
}