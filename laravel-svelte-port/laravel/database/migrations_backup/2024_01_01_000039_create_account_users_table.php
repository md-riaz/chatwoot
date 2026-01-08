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
        Schema::create('account_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('role')->default(0); // 0=agent, 1=administrator (Rails parity)
            $table->boolean('active_at')->default(true);
            $table->integer('availability')->default(1); // 1=online, 0=offline
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'user_id']);
            $table->index(['account_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_users');
    }
};
