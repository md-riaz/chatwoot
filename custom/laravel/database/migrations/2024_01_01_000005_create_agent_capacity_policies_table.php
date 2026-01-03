<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agent Capacity Policies Table Migration
 * 
 * Agent workload management - depends on accounts
 * Must be created before account_users (account_users reference agent_capacity_policies)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_capacity_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->json('exclusion_rules')->default('{}');
            $table->timestamps();

            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_capacity_policies');
    }
};