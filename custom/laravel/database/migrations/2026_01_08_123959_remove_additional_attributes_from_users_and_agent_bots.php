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
        // Remove additional_attributes columns since we're using Spatie Media Library
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'additional_attributes')) {
                $table->dropColumn('additional_attributes');
            }
        });

        Schema::table('agent_bots', function (Blueprint $table) {
            if (Schema::hasColumn('agent_bots', 'additional_attributes')) {
                $table->dropColumn('additional_attributes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('additional_attributes')->nullable();
        });

        Schema::table('agent_bots', function (Blueprint $table) {
            $table->json('additional_attributes')->nullable();
        });
    }
};
