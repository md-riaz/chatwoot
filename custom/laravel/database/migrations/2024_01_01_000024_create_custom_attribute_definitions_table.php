<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_attribute_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('attribute_key');
            $table->string('attribute_display_name');
            $table->text('attribute_description')->nullable();
            $table->integer('attribute_model')->default(0); // 0: conversation, 1: contact
            $table->integer('attribute_display_type')->default(0); // text, number, currency, etc.
            $table->jsonb('attribute_values')->nullable();
            $table->text('default_value')->nullable(); // Stored as string, cast based on display_type
            $table->string('regex_pattern')->nullable();
            $table->string('regex_cue')->nullable();
            $table->timestamps();

            $table->unique(['attribute_key', 'attribute_model', 'account_id'], 'attribute_key_model_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_attribute_definitions');
    }
};
