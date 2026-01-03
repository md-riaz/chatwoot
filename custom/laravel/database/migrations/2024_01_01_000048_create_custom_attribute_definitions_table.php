<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Custom Attribute Definitions Table Migration
 * 
 * Custom field definitions - depends on accounts
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_attribute_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('attribute_display_name');
            $table->string('attribute_key');
            $table->integer('attribute_display_type')->default(0); // 0: text, 1: number, 2: link, 3: date, 4: list, 5: checkbox
            $table->integer('default_value')->nullable();
            $table->integer('attribute_model')->default(0); // 0: contact_attribute, 1: conversation_attribute
            $table->text('attribute_description')->nullable();
            $table->json('attribute_values')->default('[]');
            $table->string('regex_pattern')->nullable();
            $table->string('regex_cue')->nullable();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index('account_id');
            $table->unique(['attribute_key', 'attribute_model', 'account_id'], 'custom_attributes_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_attribute_definitions');
    }
};