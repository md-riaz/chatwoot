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
        // Web Widget Channel
        Schema::create('channel_web_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('website_url');
            $table->string('website_token')->unique();
            $table->string('widget_color')->default('#1f93ff');
            $table->string('welcome_title')->nullable();
            $table->text('welcome_tagline')->nullable();
            $table->boolean('feature_flags')->default(true);
            $table->json('pre_chat_form_options')->nullable();
            $table->boolean('pre_chat_form_enabled')->default(false);
            $table->timestamps();
        });

        // Email Channel
        Schema::create('channel_emails', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('forward_to_email');
            $table->string('imap_host')->nullable();
            $table->integer('imap_port')->nullable();
            $table->string('imap_login')->nullable();
            $table->string('imap_password')->nullable();
            $table->boolean('imap_enabled')->default(false);
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->nullable();
            $table->string('smtp_login')->nullable();
            $table->string('smtp_password')->nullable();
            $table->boolean('smtp_enabled')->default(false);
            $table->timestamps();
        });

        // API Channel
        Schema::create('channel_apis', function (Blueprint $table) {
            $table->id();
            $table->string('webhook_url')->nullable();
            $table->string('hmac_token')->nullable();
            $table->boolean('hmac_mandatory')->default(false);
            $table->json('additional_attributes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_web_widgets');
        Schema::dropIfExists('channel_emails');
        Schema::dropIfExists('channel_apis');
    }
};
