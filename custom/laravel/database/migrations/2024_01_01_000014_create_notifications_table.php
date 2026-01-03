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
        // Create notifications table if it doesn't exist (Laravel notifications system)
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('account_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('notification_type'); // conversation_creation, conversation_assignment, etc.
                $table->morphs('notifiable'); // Polymorphic: conversation, message, etc.
                $table->json('primary_actor')->nullable(); // Who triggered the notification
                $table->json('secondary_actor')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamp('snoozed_until')->nullable();
                $table->timestamp('last_activity_at')->default(now());
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['account_id', 'user_id', 'read_at']);
                $table->index(['user_id', 'read_at']);
            });
        } else {
            // Table exists, check if we need to add our custom columns
            Schema::table('notifications', function (Blueprint $table) {
                // Add our custom columns if they don't exist
                if (!Schema::hasColumn('notifications', 'account_id')) {
                    $table->foreignId('account_id')->nullable()->constrained()->cascadeOnDelete();
                }
                if (!Schema::hasColumn('notifications', 'notification_type')) {
                    $table->string('notification_type')->nullable();
                }
                if (!Schema::hasColumn('notifications', 'primary_actor')) {
                    $table->json('primary_actor')->nullable();
                }
                if (!Schema::hasColumn('notifications', 'secondary_actor')) {
                    $table->json('secondary_actor')->nullable();
                }
                if (!Schema::hasColumn('notifications', 'snoozed_until')) {
                    $table->timestamp('snoozed_until')->nullable();
                }
                if (!Schema::hasColumn('notifications', 'last_activity_at')) {
                    $table->timestamp('last_activity_at')->nullable()->default(now());
                }
                if (!Schema::hasColumn('notifications', 'meta')) {
                    $table->json('meta')->nullable();
                }
                
                // Add indexes if they don't exist
                try {
                    $table->index(['account_id', 'user_id', 'read_at']);
                } catch (\Exception $e) {
                    // Index might already exist
                }
                
                try {
                    $table->index(['user_id', 'read_at']);
                } catch (\Exception $e) {
                    // Index might already exist
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop if this looks like our custom notifications table
        if (Schema::hasTable('notifications') && 
            Schema::hasColumn('notifications', 'account_id') &&
            Schema::hasColumn('notifications', 'notification_type')) {
            Schema::dropIfExists('notifications');
        }
    }
};
