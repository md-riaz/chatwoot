<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Account Users Table Migration
 * 
 * User-Account relationships - depends on users, accounts, custom_roles, agent_capacity_policies
 * Junction table for multi-tenancy
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inviter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('custom_role_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agent_capacity_policy_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('role')->default(0); // 0: agent, 1: admin, 2: super_admin
            $table->integer('availability')->default(0);
            $table->boolean('auto_offline')->default(true);
            $table->timestamp('active_at')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'user_id']);
            $table->index('account_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_users');
    }
};