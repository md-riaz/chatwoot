<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create access_tokens table for polymorphic token management
        // This is separate from personal_access_tokens (Sanctum) and action_mailbox tables
        Schema::create('access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('owner'); // owner_type, owner_id
            $table->string('token', 64)->unique();
            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_tokens');
    }
};