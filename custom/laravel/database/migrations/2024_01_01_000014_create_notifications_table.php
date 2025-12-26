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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('notification_type'); // conversation_creation, conversation_assignment, etc.
            $table->morphs('notifiable'); // Polymorphic: conversation, message, etc.
            $table->json('primary_actor')->nullable(); // Who triggered the notification
            $table->json('secondary_actor')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'user_id', 'read_at']);
            $table->index(['user_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
