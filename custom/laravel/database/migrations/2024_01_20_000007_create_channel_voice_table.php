<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_voice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->string('phone_number');
            $table->string('provider')->default('twilio'); // twilio, vonage, etc.
            $table->json('provider_config')->nullable();
            $table->boolean('greeting_enabled')->default(false);
            $table->text('greeting_message')->nullable();
            $table->timestamps();

            $table->index('phone_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_voice');
    }
};