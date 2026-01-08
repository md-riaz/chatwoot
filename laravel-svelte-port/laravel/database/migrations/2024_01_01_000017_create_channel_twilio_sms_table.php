<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Channel Twilio SMS Table Migration
 * 
 * Twilio SMS channel configuration - depends on accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_twilio_sms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number')->nullable();
            $table->string('messaging_service_sid')->nullable();
            $table->string('api_key_sid')->nullable();
            $table->string('account_sid');
            $table->string('auth_token');
            $table->integer('medium')->default(0); // 0: sms, 1: whatsapp
            $table->json('content_templates')->nullable();
            $table->timestamp('content_templates_last_updated')->nullable();
            $table->timestamps();

            $table->index('account_id');
            $table->index('phone_number');
            $table->unique(['account_sid', 'phone_number'], 'twilio_account_phone_unique');
            $table->unique('messaging_service_sid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_twilio_sms');
    }
};