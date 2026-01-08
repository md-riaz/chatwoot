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
        // Message attachments
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->integer('file_type')->default(0); // 0=image, 1=audio, 2=video, 3=file, 4=location, 5=fallback
            $table->string('external_url')->nullable();
            $table->json('coordinates_lat')->nullable();
            $table->json('coordinates_long')->nullable();
            $table->string('fallback_title')->nullable();
            $table->string('extension')->nullable();
            $table->timestamps();

            $table->index('message_id');
        });

        // Conversation mentions
        Schema::create('mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mentioned_at')->nullable(); // Message ID where mentioned
            $table->timestamps();

            $table->index(['conversation_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentions');
        Schema::dropIfExists('attachments');
    }
};
