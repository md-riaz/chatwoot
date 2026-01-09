<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Labelings Table Migration
 * 
 * Polymorphic pivot table for labels - can attach labels to any model
 * (conversations, contacts, etc.) - similar to Rails taggings table
 * Depends on labels table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labelings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('label_id')->constrained()->cascadeOnDelete();
            $table->morphs('labelable'); // Creates labelable_type and labelable_id
            $table->timestamps();

            // Ensure unique label-labelable pairs
            $table->unique(['label_id', 'labelable_id', 'labelable_type'], 'unique_label_labelable');
            
            // Performance indexes
            $table->index('label_id');
            $table->index(['labelable_type', 'labelable_id']);
            $table->index(['label_id', 'labelable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labelings');
    }
};