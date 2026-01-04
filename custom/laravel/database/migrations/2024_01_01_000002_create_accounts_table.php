<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Accounts Table Migration
 * 
 * Multi-tenancy core table - depends on users (for foreign keys in other tables)
 * Must be created early as most other tables reference accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain', 100)->nullable();
            $table->string('support_email', 100)->nullable();
            $table->integer('locale')->default(0);
            $table->bigInteger('feature_flags')->default(0);
            $table->integer('auto_resolve_duration')->nullable();
            $table->integer('status')->default(0);
            $table->json('limits')->default('{}');
            $table->json('custom_attributes')->default('{}');
            $table->json('internal_attributes')->default('{}');
            $table->json('settings')->default('{}');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('domain');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};