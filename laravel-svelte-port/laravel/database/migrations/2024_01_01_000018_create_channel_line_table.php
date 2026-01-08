<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Channel Line Table Migration
 * 
 * Line channel configuration - depends on accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_line', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('line_channel_id')->unique();
            $table->string('line_channel_secret');
            $table->string('line_channel_token');
            $table->timestamps();

            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_line');
    }
};