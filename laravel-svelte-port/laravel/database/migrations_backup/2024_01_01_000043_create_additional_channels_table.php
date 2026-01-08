<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_instagram', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->string('instagram_id')->unique();
            $table->text('access_token');
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::create('channel_voice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->string('phone_number')->unique();
            $table->string('provider')->default('twilio');
            $table->json('provider_config');
            $table->json('additional_attributes')->default('{}');
            $table->timestamps();
        });

        Schema::create('account_saml_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->string('sso_url')->nullable();
            $table->text('certificate')->nullable();
            $table->string('sp_entity_id')->nullable();
            $table->string('idp_entity_id')->nullable();
            $table->json('role_mappings')->default('{}');
            $table->boolean('enabled')->default(false);
            $table->string('issuer')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_saml_settings');
        Schema::dropIfExists('channel_voice');
        Schema::dropIfExists('channel_instagram');
    }
};
