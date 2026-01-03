<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Channel TikTok Table Migration
 * 
 * TikTok channel configuration - depends on accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_tiktok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('business_id')->unique();
            $table->string('access_token');
            $table->timestamp('expires_at');
            $table->string('refresh_token');
            $table->timestamp('refresh_token_expires_at');
            $table->timestamps();

            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_tiktok');
    }
};