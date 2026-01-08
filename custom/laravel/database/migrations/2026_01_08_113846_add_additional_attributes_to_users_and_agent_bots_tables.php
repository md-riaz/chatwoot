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
        Schema::table('users', function (Blueprint $table) {
            $table->json('additional_attributes')->nullable()->after('custom_attributes');
        });

        Schema::table('agent_bots', function (Blueprint $table) {
            $table->json('additional_attributes')->nullable()->after('bot_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('additional_attributes');
        });

        Schema::table('agent_bots', function (Blueprint $table) {
            $table->dropColumn('additional_attributes');
        });
    }
};
