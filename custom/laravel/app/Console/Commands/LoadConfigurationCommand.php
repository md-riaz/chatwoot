<?php

namespace App\Console\Commands;

use App\Services\ConfigLoaderService;
use Illuminate\Console\Command;

class LoadConfigurationCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'config:load 
                            {--reconcile-only-new : Only load new configurations, skip existing ones}
                            {--no-env-migration : Skip environment variable migration}
                            {--no-features : Skip feature flag loading}
                            {--validate : Validate configuration file before loading}';

    /**
     * The console command description.
     */
    protected $description = 'Load configuration from YAML files into database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Loading configuration from YAML files...');

        $options = [
            'reconcile_only_new' => $this->option('reconcile-only-new'),
            'migrate_env_vars' => !$this->option('no-env-migration'),
            'load_features' => !$this->option('no-features'),
        ];

        $loader = new ConfigLoaderService($options);

        // Validate first if requested
        if ($this->option('validate')) {
            $this->info('Validating configuration file...');
            $errors = $loader->validate();
            
            if (!empty($errors)) {
                $this->error('Configuration validation failed:');
                foreach ($errors as $error) {
                    $this->error('  - ' . $error);
                }
                return 1;
            }
            
            $this->info('Configuration file is valid.');
        }

        // Process configuration loading
        $results = $loader->process();

        // Display results
        $this->info('Configuration loading completed:');
        $this->line("  Configs loaded: {$results['configs_loaded']}");
        $this->line("  Configs updated: {$results['configs_updated']}");
        $this->line("  Configs skipped: {$results['configs_skipped']}");
        $this->line("  Features loaded: {$results['features_loaded']}");

        if (!empty($results['errors'])) {
            $this->warn('Errors encountered:');
            foreach ($results['errors'] as $error) {
                $this->error('  - ' . $error);
            }
        }

        // Display statistics
        $stats = $loader->getStats();
        $this->info('Configuration statistics:');
        $this->line("  Total configurations: {$stats['total_configs']}");
        $this->line("  Locked configurations: {$stats['locked_configs']}");
        $this->line("  Editable configurations: {$stats['editable_configs']}");
        
        if (!empty($stats['configs_by_type'])) {
            $this->line('  Configurations by type:');
            foreach ($stats['configs_by_type'] as $type => $count) {
                $this->line("    {$type}: {$count}");
            }
        }

        return empty($results['errors']) ? 0 : 1;
    }
}