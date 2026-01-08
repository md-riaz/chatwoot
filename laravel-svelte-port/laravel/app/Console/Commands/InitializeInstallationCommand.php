<?php

namespace App\Console\Commands;

use App\Services\ConfigLoaderService;
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

        // Load configuration from YAML files
        $this->info('Loading configuration from YAML files...');
        $configLoader = new ConfigLoaderService([
            'reconcile_only_new' => false, // Allow updates during initialization
            'load_features' => true,
        ]);

        $results = $configLoader->process();

        // Display results
        $this->info('Configuration loading completed:');
        $this->line("  Configs loaded: {$results['configs_loaded']}");
        $this->line("  Configs updated: {$results['configs_updated']}");
        $this->line("  Features loaded: {$results['features_loaded']}");

        if (!empty($results['errors'])) {
            $this->warn('Errors encountered:');
            foreach ($results['errors'] as $error) {
                $this->error('  - ' . $error);
            }
        }

        // Enable onboarding if requested
        if ($this->option('enable-onboarding')) {
            Redis::set($onboardingKey, true);
            $this->info('Onboarding enabled for first superadmin creation.');
            $this->line('Visit /installation/onboarding to create the first superadmin account.');
        }

        // Display statistics
        $stats = $configLoader->getStats();
        $this->info('Installation statistics:');
        $this->line("  Total configurations: {$stats['total_configs']}");
        $this->line("  Locked configurations: {$stats['locked_configs']}");
        $this->line("  Editable configurations: {$stats['editable_configs']}");

        // Show enabled default features
        $enabledFeatures = $configLoader->getEnabledDefaultFeatures();
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
}