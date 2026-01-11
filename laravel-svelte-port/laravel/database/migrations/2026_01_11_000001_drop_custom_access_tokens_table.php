<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to drop the custom access_tokens table.
 * 
 * We're now using Laravel Sanctum's personal_access_tokens table instead
 * of the custom polymorphic access_tokens table.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('access_tokens');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');
            $table->string('token', 64)->unique();
            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
        });
    }
};
