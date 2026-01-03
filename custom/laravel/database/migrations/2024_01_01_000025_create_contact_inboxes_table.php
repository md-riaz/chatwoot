<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contact Inboxes Table Migration
 * 
 * Contact-Inbox relationships - depends on contacts, inboxes
 * Junction table for contact-inbox mapping
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_inboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inbox_id')->constrained()->cascadeOnDelete();
            $table->text('source_id'); // External source identifier
            $table->boolean('hmac_verified')->default(false);
            $table->string('pubsub_token')->unique()->nullable();
            $table->timestamps();

            $table->index('contact_id');
            $table->index('inbox_id');
            $table->index('source_id');
            $table->unique(['inbox_id', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_inboxes');
    }
};