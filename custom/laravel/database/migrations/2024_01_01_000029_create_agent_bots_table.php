<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent Bots Table Migration
 * 
 * Bot management - depends on accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_bots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('outgoing_url')->nullable();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->integer('bot_type')->default(0); // 0: webhook, 1: csml
            $table->json('bot_config')->default('{}');
            $table->timestamps();

            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_bots');
    }
};