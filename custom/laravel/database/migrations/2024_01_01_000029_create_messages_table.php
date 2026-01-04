<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Messages Table Migration
 * 
 * Message management - depends on conversations, users, accounts, inboxes
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->text('content')->nullable();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inbox_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->integer('message_type'); // 0: incoming, 1: outgoing, 2: activity, 3: template
            $table->integer('content_type')->default(0); // 0: text, 1: input_email, 2: input_textarea, etc.
            $table->integer('status')->default(0); // 0: sent, 1: delivered, 2: read, 3: failed
            $table->boolean('private')->default(false);
            $table->text('source_id')->nullable(); // External message ID
            $table->string('sender_type')->nullable(); // Polymorphic sender type
            $table->unsignedBigInteger('sender_id')->nullable(); // Polymorphic sender ID
            $table->json('content_attributes')->default('{}');
            $table->json('external_source_ids')->default('{}');
            $table->json('additional_attributes')->default('{}');
            $table->text('processed_message_content')->nullable();
            $table->integer('sentiment')->nullable(); // -1: negative, 0: neutral, 1: positive
            $table->json('translations')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Performance indexes
            $table->index('account_id');
            $table->index('inbox_id');
            $table->index('conversation_id');
            $table->index('source_id');
            $table->index(['sender_type', 'sender_id']);
            $table->index(['account_id', 'inbox_id']);
            $table->index(['message_type', 'account_id', 'created_at']);
            $table->index('created_at');
            
            // Full-text search index for content
            $table->index('content', 'messages_content_fulltext');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};