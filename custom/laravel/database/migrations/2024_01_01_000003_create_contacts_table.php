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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('location')->nullable();
            $table->string('country_code')->nullable();
            $table->string('identifier')->nullable(); // External identifier
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->text('avatar_url')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->json('additional_attributes')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->boolean('blocked')->default(false);
            $table->integer('contact_type')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'email']);
            $table->index(['account_id', 'phone_number']);
            $table->index(['account_id', 'identifier']);
            $table->index('last_activity_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
