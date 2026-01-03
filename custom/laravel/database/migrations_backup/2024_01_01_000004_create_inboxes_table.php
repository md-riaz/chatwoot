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
        Schema::create('inboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('channel_type'); // Polymorphic type: web, email, api, etc.
            $table->unsignedBigInteger('channel_id')->nullable(); // Polymorphic ID
            $table->boolean('enable_auto_assignment')->default(true);
            $table->boolean('greeting_enabled')->default(false);
            $table->text('greeting_message')->nullable();
            $table->boolean('enable_email_collect')->default(true);
            $table->boolean('csat_survey_enabled')->default(false);
            $table->boolean('allow_messages_after_resolved')->default(true);
            $table->json('working_hours')->nullable();
            $table->string('timezone')->default('UTC');
            $table->boolean('working_hours_enabled')->default(false);
            $table->text('out_of_office_message')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'channel_type', 'channel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inboxes');
    }
};
