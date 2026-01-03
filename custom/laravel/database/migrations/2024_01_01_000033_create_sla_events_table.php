<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SLA Events Table Migration
 * 
 * SLA event tracking - depends on accounts, applied_slas
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applied_sla_id')->constrained()->cascadeOnDelete();
            $table->string('event_type'); // first_response, next_response, resolution
            $table->timestamp('expected_at');
            $table->timestamp('actual_at')->nullable();
            $table->json('meta')->default('{}');
            $table->timestamps();

            $table->index('account_id');
            $table->index('applied_sla_id');
            $table->index('event_type');
            $table->index('expected_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_events');
    }
};