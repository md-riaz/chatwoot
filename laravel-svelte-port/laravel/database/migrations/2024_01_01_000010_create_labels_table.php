<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Labels Table Migration
 * 
 * Labeling system - depends on accounts
 * Must be created before conversations (which can have labels)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('color')->default('#1f93ff');
            $table->boolean('show_on_sidebar')->default(false);
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index('account_id');
            $table->unique(['title', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labels');
    }
};