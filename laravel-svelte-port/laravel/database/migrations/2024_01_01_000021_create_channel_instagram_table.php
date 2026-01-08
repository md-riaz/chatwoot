<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Channel Instagram Table Migration
 * 
 * Instagram channel configuration - depends on accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_instagram', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('instagram_id')->unique();
            $table->text('access_token');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_instagram');
    }
};