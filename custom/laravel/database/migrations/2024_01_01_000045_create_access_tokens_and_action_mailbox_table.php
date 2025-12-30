<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('token');
            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
            $table->unique('token');
        });

        Schema::create('action_mailbox_inbound_emails', function (Blueprint $table) {
            $table->id();
            $table->integer('status')->default(0);
            $table->string('message_id');
            $table->string('message_checksum');
            $table->timestamps();

            $table->unique(['message_id', 'message_checksum'], 'action_mailbox_inbound_emails_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_mailbox_inbound_emails');
        Schema::dropIfExists('access_tokens');
    }
};
