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
            $table->boolean('enabled')->default(true);
            $table->string('assignment_order')->default('round_robin');
            $table->string('conversation_priority')->default('earliest_created');
            $table->integer('fair_distribution_limit')->default(100);
            $table->integer('fair_distribution_window')->default(3600); // 1 hour in seconds
            $table->timestamps();

            $table->unique(['account_id', 'name']);
            $table->index('enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_policies');
    }
};