<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Users Table Migration
 * 
 * Core authentication table - depends on Laravel framework tables
 * Enhanced users table with all Chatwoot-specific fields
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // OAuth/SSO fields
            $table->string('provider')->default('email');
            $table->string('uid')->default('');
            
            // Profile fields
            $table->string('display_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('type')->nullable(); // User type (agent, admin, etc.)
            $table->integer('availability')->default(0); // 0: offline, 1: online, 2: busy
            $table->text('message_signature')->nullable();
            
            // System fields
            $table->string('pubsub_token')->unique()->nullable();
            $table->json('tokens')->nullable(); // OAuth tokens
            $table->json('ui_settings')->nullable();
            $table->json('custom_attributes')->nullable();
            
            // Laravel auth fields
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Performance indexes
            $table->index(['uid', 'provider']);
            $table->index('email');
            $table->index('pubsub_token');
            $table->index('availability');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};