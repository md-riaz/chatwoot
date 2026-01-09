<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('account_users', function (Blueprint $table) {
            // Only add permissions column if it doesn't exist
            if (!Schema::hasColumn('account_users', 'permissions')) {
                $table->json('permissions')->nullable()->after('auto_offline');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_users', function (Blueprint $table) {
            if (Schema::hasColumn('account_users', 'permissions')) {
                $table->dropColumn('permissions');
            }
        });
    }
};
