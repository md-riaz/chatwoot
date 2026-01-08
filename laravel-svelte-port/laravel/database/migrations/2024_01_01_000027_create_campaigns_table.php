<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campaigns Table Migration
 * 
 * Marketing campaigns - depends on accounts, inboxes, users
 * Must be created BEFORE conversations (conversations reference campaigns)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->integer('display_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('message');
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('enabled')->default(true);
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inbox_id')->constrained()->cascadeOnDelete();
            $table->json('trigger_rules')->nullable();
            $table->integer('campaign_type')->default(0); // 0: ongoing, 1: one_off
            $table->integer('campaign_status')->default(0); // 0: active, 1: paused, 2: completed
            $table->json('audience')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->boolean('trigger_only_during_business_hours')->default(false);
            $table->json('template_params')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamps();

            $table->index('account_id');
            $table->index('inbox_id');
            $table->index('campaign_status');
            $table->index('campaign_type');
            $table->index('scheduled_at');
            $table->unique(['account_id', 'display_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};