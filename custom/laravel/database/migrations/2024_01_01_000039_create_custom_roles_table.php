<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->json('permissions')->default('[]');
            $table->timestamps();

            $table->unique(['name', 'account_id']);
        });

        // Add custom_role_id to account_users table
        Schema::table('account_users', function (Blueprint $table) {
            $table->foreignId('custom_role_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('account_users', function (Blueprint $table) {
            $table->dropForeign(['custom_role_id']);
            $table->dropColumn('custom_role_id');
        });

        Schema::dropIfExists('custom_roles');
    }
};
