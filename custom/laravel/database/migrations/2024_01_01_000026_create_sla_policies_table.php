<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SLA Policies Table Migration
 * 
 * Service Level Agreement policies - depends on accounts
 * Must be created BEFORE conversations (conversations reference sla_policies)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->integer('first_response_time_threshold')->nullable(); // in minutes
            $table->integer('next_response_time_threshold')->nullable(); // in minutes
            $table->integer('resolution_time_threshold')->nullable(); // in minutes
            $table->boolean('only_during_business_hours')->default(false);
            $table->timestamps();

            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_policies');
    }
};