<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_bots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('outgoing_url')->nullable();
            $table->string('avatar_url')->nullable();
            $table->integer('bot_type')->default(0); // 0: webhook
            $table->jsonb('bot_config')->nullable();
            $table->timestamps();

            $table->index('bot_type');
        });

        Schema::create('agent_bot_inboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_bot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inbox_id')->constrained()->cascadeOnDelete();
            $table->integer('status')->default(0);
            $table->timestamps();

            $table->unique(['agent_bot_id', 'inbox_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_bot_inboxes');
        Schema::dropIfExists('agent_bots');
    }
};
