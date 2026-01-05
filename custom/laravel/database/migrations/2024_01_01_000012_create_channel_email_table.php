<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Channel Email Table Migration
 * 
 * Email channel configuration - depends on accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_email', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('forward_to_email');
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
            $table->json('provider_config')->nullable();
            $table->string('provider')->nullable();
            $table->boolean('verified_for_sending')->default(false);
            $table->timestamps();

            $table->unique('email');
            $table->unique('forward_to_email');
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_email');
    }
};