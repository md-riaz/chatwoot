<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CSAT Survey Responses Table Migration
 * 
 * Customer satisfaction surveys - depends on accounts, conversations, messages, contacts, users
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('csat_survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('rating'); // 1-5 rating
            $table->text('feedback_message')->nullable();
            $table->timestamps();

            $table->index('account_id');
            $table->index('conversation_id');
            $table->index('contact_id');
            $table->index('assigned_agent_id');
            $table->unique('message_id'); // One response per message
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('csat_survey_responses');
    }
};