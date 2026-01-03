<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Applied SLAs Table Migration
 * 
 * SLA application tracking - depends on accounts, sla_policies, conversations
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applied_slas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sla_policy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->integer('sla_status')->default(0); // 0: active, 1: hit, 2: missed
            $table->timestamps();

            $table->index('account_id');
            $table->index('sla_policy_id');
            $table->index('conversation_id');
            $table->unique(['account_id', 'sla_policy_id', 'conversation_id'], 'applied_slas_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applied_slas');
    }
};