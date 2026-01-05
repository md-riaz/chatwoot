<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Webhooks Table Migration
 * 
 * Webhook management - depends on accounts, inboxes
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inbox_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('url');
            $table->integer('webhook_type')->default(0); // 0: account, 1: inbox
            $table->json('subscriptions')->nullable(); // Array of event types
            $table->timestamps();

            $table->index('account_id');
            $table->index('inbox_id');
            $table->unique(['account_id', 'url']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};