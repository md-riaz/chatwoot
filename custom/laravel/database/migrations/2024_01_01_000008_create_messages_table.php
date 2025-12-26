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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inbox_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable(); // Polymorphic sender
            $table->string('sender_type')->nullable(); // User or Contact
            $table->integer('message_type')->default(0); // 0=incoming, 1=outgoing, 2=activity, 3=template
            $table->text('content')->nullable();
            $table->json('content_attributes')->nullable(); // For rich content
            $table->integer('content_type')->default(0); // 0=text, 1=input_text, 2=input_email, etc.
            $table->integer('status')->default(0); // 0=sent, 1=delivered, 2=read, 3=failed
            $table->boolean('private')->default(false); // Private note
            $table->string('external_source_id')->nullable(); // External message ID
            $table->json('external_source_ids')->nullable();
            $table->text('source_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['conversation_id', 'created_at']);
            $table->index(['account_id', 'inbox_id']);
            $table->index(['sender_id', 'sender_type']);
            $table->index('external_source_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
