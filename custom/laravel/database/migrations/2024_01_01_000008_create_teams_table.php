<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Teams Table Migration
 * 
 * Team management - depends on accounts
 * Must be created before team_members and conversations (which reference teams)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('allow_auto_assign')->default(true);
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index('account_id');
            $table->unique(['name', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};