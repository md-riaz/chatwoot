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

        // Pivot table for labeling conversations and contacts
        Schema::create('labelings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('label_id')->constrained()->cascadeOnDelete();
            $table->string('labelable_type');
            $table->unsignedBigInteger('labelable_id');
            $table->timestamps();

            // Unique constraint to prevent duplicate label assignments
            $table->unique(['label_id', 'labelable_id', 'labelable_type'], 'unique_label_labelable');
        });

        // Add indexes separately to avoid conflicts
        Schema::table('labelings', function (Blueprint $table) {
            $table->index('label_id', 'labelings_label_id_index');
            $table->index(['labelable_type', 'labelable_id'], 'labelings_polymorphic_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labelings');
        Schema::dropIfExists('labels');
    }
};