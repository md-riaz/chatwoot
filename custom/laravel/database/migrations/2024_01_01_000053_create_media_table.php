<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->morphs('mediable'); // mediable_type and mediable_id
            $table->integer('file_type')->default(0);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('extension')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('disk')->default('local');
            $table->string('external_url')->nullable();
            $table->string('thumb_path')->nullable();
            $table->float('coordinates_lat')->nullable();
            $table->float('coordinates_long')->nullable();
            $table->string('fallback_title')->nullable();
            $table->jsonb('meta')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->timestamps();

            $table->index('file_type');
            // Note: morphs() already creates index on mediable_type and mediable_id
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};