<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notifications Table Migration
 * 
 * User notifications - depends on accounts, users
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('notification_type'); // 0: conversation_creation, 1: conversation_assignment, etc.
            $table->string('primary_actor_type'); // Polymorphic type
            $table->unsignedBigInteger('primary_actor_id'); // Polymorphic ID
            $table->string('secondary_actor_type')->nullable(); // Polymorphic type
            $table->unsignedBigInteger('secondary_actor_id')->nullable(); // Polymorphic ID
            $table->timestamp('read_at')->nullable();
            $table->timestamp('snoozed_until')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('account_id');
            $table->index('user_id');
            $table->index(['primary_actor_type', 'primary_actor_id']);
            $table->index(['secondary_actor_type', 'secondary_actor_id']);
            $table->index('read_at');
            $table->index('notification_type');
            $table->index(['user_id', 'account_id', 'notification_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};