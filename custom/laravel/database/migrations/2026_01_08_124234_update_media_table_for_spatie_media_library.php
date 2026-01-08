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
        // Drop the existing media table and recreate with Spatie Media Library structure
        Schema::dropIfExists('media');
        
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            $table->morphs('model');
            $table->uuid()->nullable()->unique();
            $table->string('collection_name');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk');
            $table->string('conversions_disk')->nullable();
            $table->unsignedBigInteger('size');
            $table->json('manipulations');
            $table->json('custom_properties');
            $table->json('generated_conversions');
            $table->json('responsive_images');
            $table->unsignedInteger('order_column')->nullable()->index();

            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the original media table structure
        Schema::dropIfExists('media');
        
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
        });
    }
};
