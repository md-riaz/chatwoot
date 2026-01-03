<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_capacity_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('exclusion_rules')->default('{}');
            $table->timestamps();
        });

        Schema::create('inbox_capacity_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_capacity_policy_id')->constrained()->onDelete('cascade');
            $table->foreignId('inbox_id')->constrained()->onDelete('cascade');
            $table->integer('conversation_limit');
            $table->timestamps();

            $table->unique(['agent_capacity_policy_id', 'inbox_id']);
        });

        // Add agent_capacity_policy_id to account_users table
        Schema::table('account_users', function (Blueprint $table) {
            $table->foreignId('agent_capacity_policy_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('account_users', function (Blueprint $table) {
            $table->dropForeign(['agent_capacity_policy_id']);
            $table->dropColumn('agent_capacity_policy_id');
        });

        Schema::dropIfExists('inbox_capacity_limits');
        Schema::dropIfExists('agent_capacity_policies');
    }
};
