<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Conversations Table Migration
 * 
 * Core conversation management - depends on accounts, inboxes, contacts, teams, users, campaigns, sla_policies, agent_bots, contact_inboxes
 * ALL dependencies now exist before this migration
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inbox_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_inbox_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('display_id');
            $table->integer('status')->default(0); // 0: open, 1: resolved, 2: pending, 3: snoozed
            $table->integer('priority')->nullable(); // 0: none, 1: low, 2: medium, 3: high, 4: urgent
            $table->string('identifier')->nullable();
            $table->uuid('uuid')->unique();
            $table->timestamp('contact_last_seen_at')->nullable();
            $table->timestamp('agent_last_seen_at')->nullable();
            $table->timestamp('assignee_last_seen_at')->nullable();
            $table->timestamp('first_reply_created_at')->nullable();
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamp('snoozed_until')->nullable();
            $table->json('additional_attributes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->text('cached_label_list')->nullable();
            $table->foreignId('sla_policy_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('waiting_since')->nullable();
            $table->foreignId('assignee_agent_bot_id')->nullable()->constrained('agent_bots')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Performance indexes
            $table->index('account_id');
            $table->index('inbox_id');
            $table->index('contact_id');
            $table->index('assignee_id');
            $table->index('team_id');
            $table->index('campaign_id');
            $table->index('status');
            $table->index('priority');
            $table->index('last_activity_at');
            $table->index('first_reply_created_at');
            $table->index(['account_id', 'display_id']);
            $table->index(['account_id', 'inbox_id', 'status', 'assignee_id']);
            $table->index(['status', 'account_id']);
            $table->index(['status', 'priority']);
            $table->index(['assignee_id', 'account_id']);
            $table->index('identifier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};