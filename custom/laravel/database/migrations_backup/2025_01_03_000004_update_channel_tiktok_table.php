<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channel_tiktok', function (Blueprint $table) {
            // Update access_token to be encrypted (text type)
            if (Schema::hasColumn('channel_tiktok', 'access_token')) {
                $table->text('access_token')->change();
            }
            
            // Update refresh_token to be encrypted (text type)
            if (Schema::hasColumn('channel_tiktok', 'refresh_token')) {
                $table->text('refresh_token')->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('channel_tiktok', function (Blueprint $table) {
            // Revert to string type
            if (Schema::hasColumn('channel_tiktok', 'access_token')) {
                $table->string('access_token')->change();
            }
            
            if (Schema::hasColumn('channel_tiktok', 'refresh_token')) {
                $table->string('refresh_token')->change();
            }
        });
    }
};