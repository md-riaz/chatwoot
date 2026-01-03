<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update existing integrations_hooks table to match our Hook model requirements
        Schema::table('integrations_hooks', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('integrations_hooks', 'app_id')) {
                $table->string('app_id')->nullable()->after('inbox_id');
            }
            
            // Ensure access_token is encrypted (text type for encryption)
            if (Schema::hasColumn('integrations_hooks', 'access_token')) {
                $table->text('access_token')->nullable()->change();
            }
            
            // Ensure settings is json (not jsonb for compatibility)
            if (Schema::hasColumn('integrations_hooks', 'settings')) {
                $table->json('settings')->nullable()->change();
            }
            
            // Add hook_type as string if it's currently integer
            if (Schema::hasColumn('integrations_hooks', 'hook_type')) {
                // We'll keep the existing integer type and map it in the model
                // 0 = account, 1 = inbox
            }
            
            // Add status as string if it's currently integer  
            if (Schema::hasColumn('integrations_hooks', 'status')) {
                // We'll keep the existing integer type and map it in the model
                // 0 = disabled, 1 = enabled
            }
        });
    }

    public function down(): void
    {
        // We don't want to drop columns that might be used by existing integrations
        // This migration only adds/modifies columns, doesn't remove them
    }
};