<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('taggings_count')->default(0);
        });

        Schema::create('taggings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('taggable_type')->nullable();
            $table->unsignedBigInteger('taggable_id')->nullable();
            $table->string('tagger_type')->nullable();
            $table->unsignedBigInteger('tagger_id')->nullable();
            $table->string('context', 128)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('context');
            $table->unique(
                ['tag_id', 'taggable_id', 'taggable_type', 'context', 'tagger_id', 'tagger_type'],
                'taggings_idx'
            );
            $table->index(['taggable_id', 'taggable_type', 'context'], 'taggings_taggable_context');
            $table->index(['taggable_id', 'taggable_type', 'tagger_id', 'context'], 'taggings_taggable_tagger');
            $table->index('taggable_id');
            $table->index('taggable_type');
            $table->index(['tagger_id', 'tagger_type']);
            $table->index('tagger_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taggings');
        Schema::dropIfExists('tags');
    }
};
