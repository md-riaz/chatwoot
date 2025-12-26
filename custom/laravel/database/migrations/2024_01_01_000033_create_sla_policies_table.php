<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('first_response_time_threshold')->nullable()->comment('In seconds');
            $table->integer('next_response_time_threshold')->nullable()->comment('In seconds');
            $table->integer('resolution_time_threshold')->nullable()->comment('In seconds');
            $table->boolean('only_during_business_hours')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['account_id', 'active']);
        });

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

    public function down(): void
    {
        Schema::dropIfExists('applied_slas');
        Schema::dropIfExists('sla_policies');
    }
};
