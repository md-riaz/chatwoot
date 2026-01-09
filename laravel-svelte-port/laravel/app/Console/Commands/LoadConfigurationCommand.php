<?php

namespace App\Console\Commands;

use App\Enums\Feature;
use App\Models\InstallationConfig;
use Illuminate\Console\Command;

class LoadConfigurationCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'config:load 
                            {--features : Load feature flag defaults}';

    /**
     * The console command description.
     */
    protected $description = 'Load basic configuration defaults into database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Loading configuration defaults...');

        $featuresLoaded = 0;

        // Load feature defaults if requested
        if ($this->option('features')) {
            $featuresLoaded = $this->loadFeatureDefaults();
        }

        $this->info('Configuration loading completed:');
        $this->line("  Features loaded: {$featuresLoaded}");

        // Display statistics
        $totalConfigs = InstallationConfig::count();
        $this->info('Configuration statistics:');
        $this->line("  Total configurations: {$totalConfigs}");

        return 0;
    }

    /**
     * Load feature defaults into database.
     */
    private function loadFeatureDefaults(): int
    {
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

        return count($enabledFeatures);
    }
}