<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applied_slas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sla_policy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->timestamp('sla_first_response_at')->nullable();
            $table->timestamp('sla_next_response_at')->nullable();
            $table->timestamp('sla_resolution_at')->nullable();
            $table->timestamps();

            $table->unique(['conversation_id', 'sla_policy_id']);
            $table->index(['account_id', 'sla_policy_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applied_slas');
    }
};