<?php

namespace App\Console\Commands;

use App\Enums\Feature;
use App\Models\InstallationConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class InitializeInstallationCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'installation:initialize 
                            {--force : Force initialization even if already completed}
                            {--enable-onboarding : Enable onboarding for first superadmin creation}';

    /**
     * The console command description.
     */
    protected $description = 'Initialize installation configuration and enable onboarding';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Initializing Chatwoot installation...');

        // Check if already initialized
        $onboardingKey = 'chatwoot_installation_onboarding';
        $alreadyInitialized = !Redis::get($onboardingKey) && !$this->option('force');

        if ($alreadyInitialized && !$this->option('force')) {
            $this->warn('Installation appears to be already completed.');
            $this->line('Use --force to reinitialize or --enable-onboarding to enable first-time setup.');
            return 1;
        }

        // Load feature defaults
        $this->info('Loading feature defaults...');
        $featuresLoaded = $this->loadFeatureDefaults();

        $this->info('Configuration loading completed:');
        $this->line("  Features loaded: {$featuresLoaded}");

        // Enable onboarding if requested
        if ($this->option('enable-onboarding')) {
            Redis::set($onboardingKey, true);
            $this->info('Onboarding enabled for first superadmin creation.');
            $this->line('Visit /installation/onboarding to create the first superadmin account.');
        }

        // Display statistics
        $totalConfigs = InstallationConfig::count();
        $this->info('Installation statistics:');
        $this->line("  Total configurations: {$totalConfigs}");

        // Show enabled default features
        $enabledFeatures = Feature::getEnabledByDefault();
        $this->info('Default features enabled for new accounts:');
        foreach ($enabledFeatures as $feature) {
            $this->line("  - {$feature['display_name']} ({$feature['name']})");
        }

        $this->info('Installation initialization completed successfully!');
        
        if ($this->option('enable-onboarding')) {
            $this->warn('IMPORTANT: Complete the onboarding process to create your first superadmin account.');
        }

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