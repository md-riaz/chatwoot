<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contacts Table Migration
 * 
 * Customer/contact management - depends on accounts, companies
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name')->default('');
            $table->string('middle_name')->default('');
            $table->string('last_name')->default('');
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('location')->default('');
            $table->string('country_code')->default('');
            $table->string('identifier')->nullable(); // External identifier
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->text('avatar_url')->nullable();
            $table->json('custom_attributes')->default('{}');
            $table->json('additional_attributes')->default('{}');
            $table->timestamp('last_activity_at')->nullable();
            $table->boolean('blocked')->default(false);
            $table->integer('contact_type')->default(0);
            $table->timestamps();

            // Indexes for performance
            $table->index(['account_id', 'email']);
            $table->index(['account_id', 'phone_number']);
            $table->index(['account_id', 'identifier']);
            $table->index(['account_id', 'contact_type']);
            $table->index('last_activity_at');
            $table->index('blocked');
            
            // Unique constraints
            $table->unique(['email', 'account_id'], 'contacts_email_account_unique');
            $table->unique(['identifier', 'account_id'], 'contacts_identifier_account_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};