<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Channel WhatsApp Table Migration
 * 
 * WhatsApp channel configuration - depends on accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_whatsapp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number')->unique();
            $table->string('provider')->default('default');
            $table->json('provider_config')->nullable();
            $table->json('message_templates')->nullable();
            $table->timestamp('message_templates_last_updated')->nullable();
            $table->timestamps();

            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_whatsapp');
    }
};