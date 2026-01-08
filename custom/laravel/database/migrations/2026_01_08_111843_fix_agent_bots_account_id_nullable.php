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
        Schema::table('agent_bots', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['account_id']);
            
            // Make account_id nullable
            $table->foreignId('account_id')->nullable()->change();
            
            // Add the foreign key constraint back with nullOnDelete
            $table->foreign('account_id')->references('id')->on('accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_bots', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['account_id']);
            
            // Make account_id not nullable (this might fail if there are null values)
            $table->foreignId('account_id')->nullable(false)->change();
            
            // Add the foreign key constraint back with cascadeOnDelete
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
        });
    }
};
