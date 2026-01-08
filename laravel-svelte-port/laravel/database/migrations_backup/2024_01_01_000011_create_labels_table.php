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
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('color')->default('#1f93ff');
            $table->boolean('show_on_sidebar')->default(true);
            $table->timestamps();

            $table->unique(['account_id', 'title']);
        });

        // Pivot table for labeling conversations and contacts
        Schema::create('labelings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('label_id')->constrained()->cascadeOnDelete();
            $table->morphs('labelable'); // conversation, contact, etc.
            $table->timestamps();

            $table->unique(['label_id', 'labelable_id', 'labelable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labelings');
        Schema::dropIfExists('labels');
    }
};
