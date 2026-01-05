<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Automation Rules Table Migration
 * 
 * Workflow automation - depends on accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('event_name'); // conversation_created, message_created, etc.
            $table->json('conditions')->nullable();
            $table->json('actions')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('account_id');
            $table->index('event_name');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};