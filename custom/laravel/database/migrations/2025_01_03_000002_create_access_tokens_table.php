<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if access_tokens table already exists (from Sanctum or other packages)
        if (!Schema::hasTable('access_tokens')) {
            // Create access_tokens table for polymorphic token management
            // This is separate from personal_access_tokens (Sanctum) and action_mailbox tables
            Schema::create('access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('owner'); // owner_type, owner_id
                $table->string('token', 64)->unique();
                $table->timestamps();

                $table->index(['owner_type', 'owner_id']);
            });
        } else {
            // Table exists, check if we need to add missing columns or indexes
            Schema::table('access_tokens', function (Blueprint $table) {
                // Check if owner columns exist (morphs creates owner_type and owner_id)
                if (!Schema::hasColumn('access_tokens', 'owner_type')) {
                    $table->string('owner_type')->nullable();
                }
                if (!Schema::hasColumn('access_tokens', 'owner_id')) {
                    $table->unsignedBigInteger('owner_id')->nullable();
                }
                
                // Add index only if it doesn't exist
                // PostgreSQL will throw error if index already exists, so we use raw SQL with IF NOT EXISTS
                if (DB::getDriverName() === 'pgsql') {
                    try {
                        DB::statement('CREATE INDEX IF NOT EXISTS access_tokens_owner_type_owner_id_index ON access_tokens (owner_type, owner_id)');
                    } catch (\Exception $e) {
                        // Index already exists, ignore
                    }
                } else {
                    // For MySQL, check if index exists before creating
                    $indexExists = DB::select("SHOW INDEX FROM access_tokens WHERE Key_name = 'access_tokens_owner_type_owner_id_index'");
                    if (empty($indexExists)) {
                        $table->index(['owner_type', 'owner_id']);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        // Only drop if we created it, not if it was from Sanctum
        // Check if table has our specific columns to determine if we should drop it
        if (Schema::hasTable('access_tokens') && 
            Schema::hasColumn('access_tokens', 'owner_type') && 
            Schema::hasColumn('access_tokens', 'owner_id')) {
            
            // If this is our table (has polymorphic owner columns), we can drop it
            // But be careful - Sanctum also uses access_tokens but with different structure
            $columns = Schema::getColumnListing('access_tokens');
            
            // Check if this looks like our table vs Sanctum's personal_access_tokens
            if (in_array('owner_type', $columns) && in_array('owner_id', $columns)) {
                Schema::dropIfExists('access_tokens');
            } else {
                // This is likely Sanctum's table, just remove our additions
                Schema::table('access_tokens', function (Blueprint $table) {
                    if (Schema::hasColumn('access_tokens', 'owner_type')) {
                        $table->dropColumn('owner_type');
                    }
                    if (Schema::hasColumn('access_tokens', 'owner_id')) {
                        $table->dropColumn('owner_id');
                    }
                    
                    // Drop our index if it exists
                    try {
                        $table->dropIndex('access_tokens_owner_type_owner_id_index');
                    } catch (\Exception $e) {
                        // Index might not exist, ignore
                    }
                });
            }
        }
    }
};