<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Custom Roles Table Migration
 * 
 * Role-based access control - depends on accounts
 * Must be created before account_users (account_users reference custom_roles)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->json('permissions')->default('[]');
            $table->timestamps();

            $table->unique(['name', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_roles');
    }
};