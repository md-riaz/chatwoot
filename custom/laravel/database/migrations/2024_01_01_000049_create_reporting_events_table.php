<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reporting Events Table Migration
 * 
 * Analytics and reporting events - depends on accounts, inboxes, users, conversations
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporting_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('value')->nullable();
            $table->float('value_in_business_hours')->nullable();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('inbox_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('event_start_time')->nullable();
            $table->timestamp('event_end_time')->nullable();
            $table->timestamps();

            $table->index('account_id');
            $table->index('inbox_id');
            $table->index('user_id');
            $table->index('conversation_id');
            $table->index('name');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting_events');
    }
};