<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channel_telegram', function (Blueprint $table) {
            $table->string('webhook_secret')->nullable()->after('bot_token');
        });
    }

    public function down(): void
    {
        Schema::table('channel_telegram', function (Blueprint $table) {
            $table->dropColumn('webhook_secret');
        });
    }
};
