<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attachments Table Migration
 * 
 * File attachments - depends on messages, accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->integer('file_type')->default(0); // 0: image, 1: audio, 2: video, 3: file, 4: location, 5: fallback
            $table->string('external_url')->nullable();
            $table->float('coordinates_lat')->default(0.0);
            $table->float('coordinates_long')->default(0.0);
            $table->string('fallback_title')->nullable();
            $table->string('extension')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('message_id');
            $table->index('account_id');
            $table->index('file_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};