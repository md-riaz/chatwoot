<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Canned Responses Table Migration
 * 
 * Pre-defined response templates - depends on accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canned_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('short_code');
            $table->text('content');
            $table->timestamps();

            $table->index('account_id');
            $table->index('short_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canned_responses');
    }
};