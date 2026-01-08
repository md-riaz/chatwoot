<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Channel API Table Migration
 * 
 * API channel configuration - depends on accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_api', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('webhook_url')->nullable();
            $table->string('identifier')->nullable();
            $table->string('hmac_token')->nullable();
            $table->boolean('hmac_mandatory')->default(false);
            $table->json('additional_attributes')->nullable();
            $table->timestamps();

            $table->unique('hmac_token');
            $table->unique('identifier');
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_api');
    }
};