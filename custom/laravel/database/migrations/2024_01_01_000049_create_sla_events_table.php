<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applied_sla_id')->constrained('applied_slas')->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sla_policy_id')->constrained('sla_policies')->cascadeOnDelete();
            $table->foreignId('inbox_id')->constrained()->cascadeOnDelete();
            $table->integer('event_type')->nullable();
            $table->jsonb('meta')->default(new Expression("'{}'::jsonb"));
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_events');
    }
};
