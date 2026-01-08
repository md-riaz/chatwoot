<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent Bot Inboxes Table Migration
 * 
 * Bot-Inbox relationships - depends on agent_bots, inboxes, accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_bot_inboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbox_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_bot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->integer('status')->default(0); // 0: active, 1: inactive
            $table->timestamps();

            $table->index('inbox_id');
            $table->index('agent_bot_id');
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_bot_inboxes');
    }
};