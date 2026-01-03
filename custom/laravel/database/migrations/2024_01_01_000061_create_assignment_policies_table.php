<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('assignment_order')->default(0); // 0: round_robin, 1: balanced
            $table->integer('conversation_priority')->default(0); // 0: earliest_created, 1: longest_waiting
            $table->integer('fair_distribution_limit')->default(100);
            $table->integer('fair_distribution_window')->default(3600); // seconds
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['account_id', 'name']);
            $table->index('enabled');
        });

        Schema::create('inbox_assignment_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbox_id')->constrained()->onDelete('cascade');
            $table->foreignId('assignment_policy_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique('inbox_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbox_assignment_policies');
        Schema::dropIfExists('assignment_policies');
    }
};