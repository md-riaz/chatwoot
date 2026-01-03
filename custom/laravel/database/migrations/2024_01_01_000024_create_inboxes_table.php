<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inboxes Table Migration
 * 
 * Inbox management - depends on accounts, channels (polymorphic)
 * Must be created after all channel tables
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('channel_type'); // Polymorphic type: web, email, api, etc.
            $table->unsignedBigInteger('channel_id'); // Polymorphic ID
            $table->string('email_address')->nullable();
            $table->boolean('enable_auto_assignment')->default(true);
            $table->boolean('greeting_enabled')->default(false);
            $table->text('greeting_message')->nullable();
            $table->boolean('enable_email_collect')->default(true);
            $table->boolean('csat_survey_enabled')->default(false);
            $table->boolean('allow_messages_after_resolved')->default(true);
            $table->boolean('working_hours_enabled')->default(false);
            $table->string('timezone')->default('UTC');
            $table->text('out_of_office_message')->nullable();
            $table->json('auto_assignment_config')->default('{}');
            $table->boolean('lock_to_single_conversation')->default(false);
            $table->foreignId('portal_id')->nullable()->constrained()->nullOnDelete();
            $table->json('csat_config')->nullable();
            $table->timestamps();

            $table->index('account_id');
            $table->index(['channel_type', 'channel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inboxes');
    }
};