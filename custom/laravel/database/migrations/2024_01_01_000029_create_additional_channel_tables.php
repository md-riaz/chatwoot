<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // WhatsApp Channel
        Schema::create('channel_whatsapp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number')->unique();
            $table->string('provider')->default('default');
            $table->jsonb('provider_config')->nullable();
            $table->jsonb('message_templates')->nullable();
            $table->timestamp('message_templates_last_updated')->nullable();
            $table->timestamps();
        });

        // Telegram Channel
        Schema::create('channel_telegram', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('bot_token')->unique();
            $table->string('bot_name')->nullable();
            $table->timestamps();
        });

        // SMS Channel (Bandwidth)
        Schema::create('channel_sms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number')->unique();
            $table->string('provider')->default('bandwidth');
            $table->jsonb('provider_config')->nullable();
            $table->timestamps();
        });

        // Twilio SMS Channel
        Schema::create('channel_twilio_sms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number');
            $table->string('messaging_service_sid')->nullable();
            $table->string('account_sid');
            $table->string('auth_token');
            $table->string('medium')->default('sms'); // sms, whatsapp
            $table->timestamps();

            $table->index('phone_number');
        });

        // Line Channel
        Schema::create('channel_line', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('line_channel_id')->unique();
            $table->string('line_channel_secret');
            $table->string('line_channel_token');
            $table->timestamps();
        });

        // Facebook Page Channel
        Schema::create('channel_facebook_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('page_id')->unique();
            $table->text('user_access_token');
            $table->text('page_access_token');
            $table->string('instagram_id')->nullable();
            $table->timestamps();
        });

        // Twitter Profile Channel
        Schema::create('channel_twitter_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('profile_id')->unique();
            $table->text('twitter_access_token');
            $table->text('twitter_access_token_secret');
            $table->boolean('tweets_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_twitter_profiles');
        Schema::dropIfExists('channel_facebook_pages');
        Schema::dropIfExists('channel_line');
        Schema::dropIfExists('channel_twilio_sms');
        Schema::dropIfExists('channel_sms');
        Schema::dropIfExists('channel_telegram');
        Schema::dropIfExists('channel_whatsapp');
    }
};
