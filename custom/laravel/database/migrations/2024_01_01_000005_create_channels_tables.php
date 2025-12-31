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
        Schema::create('channel_email', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('forward_to_email');
            $table->timestamps();
            $table->boolean('imap_enabled')->default(false);
            $table->string('imap_address')->default('');
            $table->integer('imap_port')->default(0);
            $table->string('imap_login')->default('');
            $table->string('imap_password')->default('');
            $table->boolean('imap_enable_ssl')->default(true);
            $table->timestamp('imap_inbox_synced_at')->nullable();
            $table->boolean('smtp_enabled')->default(false);
            $table->string('smtp_address')->default('');
            $table->integer('smtp_port')->default(0);
            $table->string('smtp_login')->default('');
            $table->string('smtp_password')->default('');
            $table->string('smtp_domain')->default('');
            $table->boolean('smtp_enable_starttls_auto')->default(true);
            $table->string('smtp_authentication')->default('login');
            $table->string('smtp_openssl_verify_mode')->default('none');
            $table->boolean('smtp_enable_ssl_tls')->default(false);
            $table->jsonb('provider_config')->default(new \Illuminate\Database\Query\Expression("'{}'::jsonb"));
            $table->string('provider')->nullable();
            $table->boolean('verified_for_sending')->default(false);

            $table->unique('email');
            $table->unique('forward_to_email');
        });

        // API Channel
        Schema::create('channel_api', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('webhook_url')->nullable();
            $table->timestamps();
            $table->string('identifier')->nullable();
            $table->string('hmac_token')->nullable();
            $table->boolean('hmac_mandatory')->default(false);
            $table->jsonb('additional_attributes')->default(new \Illuminate\Database\Query\Expression("'{}'::jsonb"));

            $table->unique('hmac_token');
            $table->unique('identifier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_web_widgets');
        Schema::dropIfExists('channel_email');
        Schema::dropIfExists('channel_api');
    }
};
