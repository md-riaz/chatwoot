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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inbox_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_inbox_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assignee_agent_bot_id')->nullable()->constrained('agent_bots')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sla_policy_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('display_id')->nullable(); // Per-account display ID
            $table->integer('status')->default(0); // 0=open, 1=resolved, 2=pending, 3=snoozed
            $table->integer('priority')->default(0); // 0=none, 1=low, 2=medium, 3=high, 4=urgent
            $table->text('additional_attributes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->text('cached_label_list')->nullable();
            $table->string('identifier')->nullable();
            $table->timestamp('first_reply_created_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('contact_last_seen_at')->nullable();
            $table->timestamp('agent_last_seen_at')->nullable();
            $table->timestamp('assignee_last_seen_at')->nullable();
            $table->timestamp('waiting_since')->nullable();
            $table->timestamp('snoozed_until')->nullable();
            $table->string('uuid')->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'status']);
            $table->index(['account_id', 'inbox_id']);
            $table->index(['account_id', 'assignee_id']);
            $table->index(['account_id', 'team_id']);
            $table->index(['account_id', 'display_id']);
            $table->index('last_activity_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
