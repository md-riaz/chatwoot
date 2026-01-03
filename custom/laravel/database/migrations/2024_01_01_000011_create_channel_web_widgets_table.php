<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Channel Web Widgets Table Migration
 * 
 * Web widget channel configuration - depends on accounts
 * Must be created before inboxes (inboxes reference channels polymorphically)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_web_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('website_url');
            $table->string('website_token')->unique();
            $table->string('widget_color')->default('#1f93ff');
            $table->string('welcome_title')->nullable();
            $table->text('welcome_tagline')->nullable();
            $table->boolean('feature_flags')->default(true);
            $table->json('pre_chat_form_options')->nullable();
            $table->boolean('pre_chat_form_enabled')->default(false);
            $table->integer('reply_time')->default(0);
            $table->string('hmac_token')->nullable()->unique();
            $table->boolean('hmac_mandatory')->default(false);
            $table->boolean('continuity_via_email')->default(true);
            $table->text('allowed_domains')->nullable();
            $table->timestamps();

            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_web_widgets');
    }
};